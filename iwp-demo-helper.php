<?php
/*
Plugin Name: InstaWP Demo Helper
Description: A custom plugin to add a button with specific settings.
Version: 1.0.5
Author: InstaWP Inc
*/

defined( 'IWP_MIG_PLUGIN_VERSION' ) || define( 'IWP_MIG_PLUGIN_VERSION', '1.0.5' );

class IWP_Migration {

	protected static $_instance = null;
	public static $_plugin_slug = 'iwp-migration/iwp-migration.php';
	public static $_plugin_slug_git = 'iwp-migration-main/iwp-migration.php';
	public static $_settings_section = 'iwp_migration_main_section';
	public static $_settings_group = 'iwp_migration_settings_group';


	public function __construct() {

		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) == 'iwp_migrate_content' ) {
			add_filter( 'admin_footer_text', '__return_false' );
			add_filter( 'update_footer', '__return_false', 99 );
		}

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_migrate_button' ), 999 );
		add_action( 'admin_menu', array( $this, 'add_migrate_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_iwp_migration_initiate', array( $this, 'iwp_migration_initiate' ) );

		add_filter( 'all_plugins', array( $this, 'remove_plugin_from_list' ) );
		add_action( 'admin_bar_menu', array( $this, 'render_css_for_admin_bar_btn' ) );

		$this->check_update();
	}

	/**
	 * Checks for any available updates for the plugin.
	 *
	 * This function is intended to verify and handle any necessary updates
	 * for the plugin.
	 * 
	 * @since 1.0.5
	 * @return void
	 */
	function check_update() {
		if ( class_exists( 'InstaWP\Connect\Helpers\AutoUpdatePluginFromGitHub' ) ) {
			$updater = new InstaWP\Connect\Helpers\AutoUpdatePluginFromGitHub(
				IWP_MIG_PLUGIN_VERSION, // Current version
				'https://github.com/InstaWP/iwp-migration', // URL to GitHub repo
				plugin_basename( __FILE__ ) // Plugin slug
			);
		} else {
			error_log( 'Update check class not found.' );
		}
	}

	function render_css_for_admin_bar_btn() {

		$css_rules = array(
			array(
				'selectors' => array(
					'#wpadminbar .iwp_migration_class > a',
					'#wpadminbar .iwp_migration_class > a:hover',
					'#wpadminbar .iwp_migration_class > a:focus',
					'#wpadminbar .iwp_migration_class > a:visited',
				),
				'rules'     => array(
					'background-color' => IWP_Migration::get_option( 'cta_btn_bg_color', '#6b2fad' ) . ' !important',
					'color'            => IWP_Migration::get_option( 'cta_btn_text_color', '#fff' ) . ' !important',
				),
			)
		);

		ob_start();

		foreach ( $css_rules as $css_rule ) {
			$selectors = isset( $css_rule['selectors'] ) ? $css_rule['selectors'] : array();
			$rules     = isset( $css_rule['rules'] ) ? $css_rule['rules'] : array();
			$rules     = array_map( function ( $rule_value, $rule_name ) {
				return sprintf( '%s: %s;', $rule_name, $rule_value );
			}, $rules, array_keys( $rules ) );

			printf( '%s { %s }', implode( ',', $selectors ), implode( ' ', $rules ) );
		}

		if ( ! empty( $custom_css = IWP_Migration::get_option( 'iwp_custom_css' ) ) ) {
			printf( '%s', $custom_css );
		}

		printf( '<style>%s</style>', ob_get_clean() );
	}


	function iwp_migration_initiate() {
		$domain_name       = isset( $_POST['domain_name'] ) ? sanitize_text_field( $_POST['domain_name'] ) : '';
		$iwp_email_subject = get_option( 'iwp_email_subject' );
		$iwp_email_subject = empty( $iwp_email_subject ) ? 'Sample email subject' : $iwp_email_subject;
		$iwp_email_body    = get_option( 'iwp_email_body' );
		$iwp_email_body    = empty( $iwp_email_body ) ? 'Sample email body' : $iwp_email_body;
		$iwp_api_key       = get_option( 'iwp_api_key' );
		$iwp_api_domain    = defined('INSTAWP_API_DOMAIN' ) ? INSTAWP_API_DOMAIN : 'https://app.instawp.io';
		$body_args         = array(
			'url'            => site_url(),
			'email'          => get_option( 'iwp_support_email' ),
			'customer_email' => get_option( 'admin_email' ),
			'subject'        => $iwp_email_subject,
			'body'           => $iwp_email_body,
		);

		if ( get_option( 'iwp_disable_email' ) == 'yes' ) {
			$body_args['email'] = '';
		}

		$headers  = array(
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $iwp_api_key,
		);
		$args     = array(
			'headers'     => $headers,
			'body'        => json_encode( $body_args ),
			'method'      => 'POST',
			'data_format' => 'body'
		);
		$response = wp_remote_post( $iwp_api_domain . '/api/v2/migrate-request', $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		error_log( 'Response from api/v2/migrate-request:' . json_encode( $response_body ) );

		if ( ! isset( $response_body['status'] ) || $response_body['status'] !== true ) {
			wp_send_json_error( $response_body );
		}

		if ( ! empty( $redirection_url = get_option( 'iwp_redirection_url' ) ) ) {

			if ( ! empty( $domain_name ) ) {
				$redirection_url .= '?domain=' . $domain_name;
			}

			$response_body['redirection_url'] = $redirection_url;
		}

		if ( ! empty( $webhook_url = get_option( 'iwp_webhook_url' ) ) ) {
			$webhook_args     = array(
				'body'        => json_encode( $body_args ),
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'method'      => 'POST',
				'data_format' => 'body',
			);
			$webhook_response = wp_remote_post( $webhook_url, $webhook_args );

			if ( is_wp_error( $webhook_response ) ) {
				$response_body['webhook_status']  = false;
				$response_body['webhook_message'] = $webhook_response->get_error_message();
			} else {
				$response_body['webhook_status'] = true;
			}
		}

		wp_send_json_success( $response_body );
	}


	function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'iwp-migration', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_script( 'iwp-migration', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), time() );
		wp_localize_script( 'iwp-migration', 'iwp_migration',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'demo_site_url'         => site_url(),
				'enable_src_demo_url'   => IWP_Migration::get_option( 'iwp_enable_src_demo_url', '' ),
			)
		);

		if ( wp_enqueue_code_editor( array( 'type' => 'text/css' ) ) ) {
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_enqueue_style( 'wp-codemirror' );
		}
	}


	function render_migrate_page() {
		include 'templates/migration.php';
	}


	function remove_plugin_from_list( $all_plugins ) {

		if ( get_option( 'iwp_hide_migration_plugin' ) === 'yes' ) {
			if ( array_key_exists( self::$_plugin_slug, $all_plugins ) ) {
				unset( $all_plugins[ self::$_plugin_slug ] );
			}

			if ( array_key_exists( self::$_plugin_slug_git, $all_plugins ) ) {
				unset( $all_plugins[ self::$_plugin_slug_git ] );
			}
		}

		return $all_plugins;
	}


	function add_migrate_page() {
		add_submenu_page(
			'iwp_migration_settings',
			'IWP Migrate Content',
			'Migrate Content',
			'manage_options',
			'iwp_migrate_content',
			array( $this, 'render_migrate_page' )
		);

		add_menu_page(
			'IWP Migration Settings',
			'IWP Migration',
			'manage_options',
			'iwp_migration',
			array( $this, 'render_migrate_settings_page' ),
			'dashicons-migrate',
			200
		);

		if ( get_option( 'iwp_hide_migration_plugin' ) === 'yes' ) {
			remove_menu_page( 'iwp_migration' );
		}
	}


	function add_migrate_button( $wp_admin_bar ) {
		$button_location = IWP_Migration::get_option( 'top_button_location', 'left' );

		$args = array(
			'id'    => 'iwp_migration_btn',
			'title' => esc_attr( get_option( 'top_bar_text', 'Migrate' ) ),
			'href'  => admin_url( 'admin.php?page=iwp_migrate_content' ),
			'meta'  => array(
				'class' => 'menupop iwp_migration_class',
			)
		);

		if ( $button_location === 'right' ) {
			$args['parent'] = 'top-secondary';
			$args['meta']['class'] .= ' iwp_migration_class_right';
		}

		$wp_admin_bar->add_node( $args );
	}


	public static function get_setting_fields() {
		return array(
			'iwp_api_key'               => array(
				'title'   => 'API Key',
				'type'    => 'text',
				'default' => '',
			),
			'iwp_support_email'         => array(
				'title'   => 'Support Email',
				'type'    => 'text',
				'default' => '',
			),
			'iwp_disable_email'         => array(
//				'title'   => 'Support Email',
				'label'   => 'Do not send support email on publish',
				'type'    => 'checkbox',
				'default' => '',
			),
			'logo_url'                  => array(
				'title'   => 'Logo URL',
				'type'    => 'text',
				'default' => 'https://instawpcom.b-cdn.net/wp-content/uploads/2023/07/header-logo.svg',
			),
			'content'                   => array(
				'title'   => 'Main Content',
				'type'    => 'wp_editor',
				'default' => 'This a demo site, which you can migrate to your own hosting account in just one click. Click on the Migrate button and we will migrate it!',
			),
			'title_text'                => array(
				'title'   => 'Title Text',
				'type'    => 'text',
				'default' => 'Migration Demo',
			),
			'footer_text'               => array(
				'title'   => 'Footer Text',
				'type'    => 'wp_editor',
				'default' => 'Powered by InstaWP',
			),
			'brand_color'               => array(
				'title'   => 'Brand Color',
				'type'    => 'color_picker',
				'default' => '#005e54',
			),
			'cta_btn_text'              => array(
				'title'   => 'CTA Button - Text',
				'type'    => 'text',
				'default' => 'Begin Migration',
			),
			'cta_btn_text_color'        => array(
				'title'   => 'CTA Button - Color',
				'type'    => 'color_picker',
				'default' => '#fff',
			),
			'cta_btn_bg_color'          => array(
				'title'   => 'CTA Button - BG Color',
				'type'    => 'color_picker',
				'default' => '#005e54',
			),
			'close_btn_text'            => array(
				'title'   => 'Close Button - Text',
				'type'    => 'text',
				'default' => 'Close',
			),
			'close_btn_text_color'      => array(
				'title'   => 'Close Button - Color',
				'type'    => 'color_picker',
				'default' => '#fff',
			),
			'close_btn_bg_color'        => array(
				'title'   => 'Close Button - BG Color',
				'type'    => 'color_picker',
				'default' => '#005e54',
			),
			'top_bar_text'              => array(
				'title'   => 'Top Button',
				'type'    => 'text',
				'default' => 'Migrate',
			),
			'top_button_location' => array(
				'title'   => 'Top Button Location',
				'type'    => 'radio',
				'options' => array(
					'left'  => 'Left',
					'right' => 'Right',
				),
				'default' => 'left',
			),
			'thankyou_text'             => array(
				'title'   => 'Thank you Text',
				'type'    => 'wp_editor',
				'default' => 'Thank you, we have sent your request to our support team. We will be in touch soon',
			),
			'iwp_email_subject'         => array(
				'title'   => 'Email Subject',
				'type'    => 'text',
				'default' => 'New migration request from {{customer_email}}',
			),
			'iwp_email_body'            => array(
				'title'   => 'Email Body',
				'type'    => 'textarea',
				'default' => 'Hello, You have a new migration request from {{customer_email}} for site : {{site_url}}',
			),
			'iwp_custom_css'            => array(
				'title'       => 'Custom CSS',
				'type'        => 'css_editor',
				'placeholder' => '/* Enter your custom CSS here */',
			),
			'iwp_hide_cta_button' => array(
				'title' => 'Hide CTA Button',
				'label' => 'Hide CTA Button on migration page',
				'type' => 'checkbox',
				'default' => '',
			),
			'iwp_enable_src_demo_url' => array(
				'title' => 'Enable src_demo_url',
				'label' => 'Append src_demo_url parameter to links in Main Content',
				'type' => 'checkbox',
				'default' => '',
			),
			'iwp_hide_migration_plugin' => array(
				'title'   => 'Hide Migration Plugin',
				'label'   => 'Hide Plugin',
				'type'    => 'checkbox',
				'default' => '',
			),
			'iwp_redirection_url'       => array(
				'title'       => 'Redirection URL',
				'type'        => 'url',
				'placeholder' => 'https://my-redirection-url.com',
				'default'     => '',
			),
			'iwp_webhook_url'           => array(
				'title'       => 'Webhook URL',
				'type'        => 'url',
				'placeholder' => 'https://my-webhook-url.com',
				'default'     => '',
			),
			'iwp_show_domain_field'     => array(
				'title'   => 'Show Domain Field',
				'label'   => 'Display domain input field',
				'type'    => 'checkbox',
				'default' => '',
			),
			'iwp_domain_field_label'    => array(
				'title'   => 'Domain Field Placeholder',
				'type'    => 'text',
				'default' => 'Enter your domain name',
			),
		);
	}


	function render_migrate_settings_page() {
		include 'templates/settings.php';
	}


	function register_settings() {
		foreach ( self::get_setting_fields() as $field_id => $field ) {
			register_setting( self::$_settings_group, $field_id );
		}

		add_settings_section( self::$_settings_section, 'Main Settings', null, 'iwp_migration' );

		foreach ( self::get_setting_fields() as $field_id => $field ) {
			add_settings_field(
				$field_id,
				isset( $field['title'] ) ? $field['title'] : '',
				array( $this, 'render_setting_field' ),
				'iwp_migration',
				self::$_settings_section,
				array_merge( array( 'id' => $field_id ), $field )
			);
		}
	}


	function render_setting_field( $field ) {

		$field_id    = $field['id'] ?? '';
		$field_label = $field['label'] ?? '';
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field_type  = isset( $field['type'] ) ? $field['type'] : 'text';
		$field_value = get_option( $field_id, ( $field['default'] ?? '' ) );
		$is_disabled = '';

		if ( $field_id == 'iwp_support_email' ) {
			if ( get_option( 'iwp_disable_email' ) == 'yes' ) {
				$is_disabled = 'disabled';
			}
		}

		if ( $field_type === 'text' ) {
			printf( '<input %s type="text" style="width: 380px;" name="%s" value="%s" placeholder="%s" />', $is_disabled, $field_id, $field_value, $placeholder );
		}

		if ( $field_type === 'url' ) {
			printf( '<input type="url" style="width: 380px;" name="%s" value="%s" placeholder="%s" />', $field_id, $field_value, $placeholder );
		}

		if ( $field_type === 'checkbox' ) {
			printf( '<label style="cursor: pointer;"><input type="checkbox" %s name="%s" value="yes" /> %s</label>',
				( ( $field_value === 'yes' ) ? 'checked' : '' ), $field_id, $field_label
			);
		}

		if ( $field_type === 'textarea' ) {
			printf( '<textarea rows="10" placeholder="%s" cols="80" name="%s">%s</textarea>', $placeholder, $field_id, $field_value );
		}

		if ( $field_type === 'css_editor' ) {
			$random_id = uniqid( 'iwp-' );

			printf( '<textarea id="%s" rows="10" cols="80" name="%s">%s</textarea>', $random_id, $field_id, $field_value );
			?>
            <script>
                (function ($) {
                    $(document).ready(function () {
                        let editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
                        editorSettings.codemirror = _.extend({},
                            editorSettings.codemirror, {
                                mode: 'css',
                                indentUnit: 4,
                                tabSize: 4,
                                placeholder: '<?= $placeholder; ?>'
                            }
                        );
                        wp.codeEditor.initialize($('#<?= $random_id ?>'), editorSettings);
                    });
                })(jQuery);
            </script>
			<?php
		}

		if ( $field_type === 'color_picker' ) {
			printf( '<input type="text" class="iwp-color-picker" name="%s" value="%s" />', $field_id, $field_value );
		}

		if ( $field_type === 'wp_editor' ) {
			wp_editor( $field_value, $field_id,
				array(
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => $field_id,
					'textarea_rows' => 10,
					'teeny'         => false
				)
			);
		}

		if ( $field_type === 'radio' ) {
			$options = isset( $field['options'] ) ? $field['options'] : array();
			if ( ! empty( $options ) ) {
				echo '<fieldset>';
				foreach ( $options as $value => $label ) {
					printf( '<label style="margin-right: 10px;"><input type="radio" name="%s" value="%s" %s /> %s</label>',
						$field_id,
						esc_attr( $value ),
						checked( $field_value, $value, false ),
						esc_html( $label )
					);
				}
				echo '</fieldset>';
			}
		}
	}


	public static function get_option( $option_name, $default = '' ) {
		$option_value = get_option( $option_name );

		if ( empty( $option_value ) ) {

			$all_options = self::get_setting_fields();
			$this_option = $all_options[ $option_name ] ?? [];

			if ( ! empty( $this_option ) && is_array( $this_option ) && empty( $default ) ) {
				$default = $this_option['default'] ?? '';
			}

			return $default;
		}

		return $option_value;
	}


	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

IWP_Migration::instance();
