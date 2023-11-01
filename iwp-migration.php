<?php
/*
Plugin Name: IWP Migration
Description: A custom plugin to add a button with specific settings.
Version: 1.0
Author: InstaWP
*/

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
	}


	function iwp_migration_initiate() {
		$iwp_email_subject = get_option( 'iwp_email_subject' );
		$iwp_email_subject = empty( $iwp_email_subject ) ? 'Sample email subject' : $iwp_email_subject;
		$iwp_email_body    = get_option( 'iwp_email_body' );
		$iwp_email_body    = empty( $iwp_email_body ) ? 'Sample email body' : $iwp_email_body;
		$iwp_api_key       = get_option( 'iwp_api_key' );
		$iwp_api_domain    = 'https://stage.instawp.io/';
		$body_args         = array(
			'url'            => site_url(),
			'email'          => get_option( 'iwp_support_email' ),
			'customer_email' => get_option( 'admin_email' ),
			'subject'        => $iwp_email_subject,
			'body'           => $iwp_email_body,
		);
		$headers           = array(
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $iwp_api_key,
		);
		$args              = array(
			'headers'     => $headers,
			'body'        => json_encode( $body_args ),
			'method'      => 'POST',
			'data_format' => 'body'
		);
		$response          = wp_remote_post( $iwp_api_domain . 'api/v2/migrate-request', $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $response_body['status'] ) || $response_body['status'] !== true ) {
			wp_send_json_error( $response_body );
		}

		wp_send_json_success( $response_body );
	}


	function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'iwp-migration', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_script( 'iwp-migration', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ) );
		wp_localize_script( 'iwp-migration', 'iwp_migration',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
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
		$wp_admin_bar->add_node(
			array(
				'id'    => 'iwp_migration_btn',
				'title' => esc_attr( get_option( 'top_bar_text', 'Migrate' ) ),
				'href'  => admin_url( 'admin.php?page=iwp_migrate_content' ),
				'meta'  => array(
					'class' => 'menupop iwp_migration_class',
				)
			)
		);
	}


	public static function get_setting_fields() {
		return array(
			'iwp_api_key'               => array(
				'title' => 'API Key',
				'type'  => 'text',
			),
			'iwp_support_email'         => array(
				'title' => 'Support Email',
				'type'  => 'text',
			),
			'logo_url'                  => array(
				'title' => 'Logo URL',
				'type'  => 'text',
			),
			'content'                   => array(
				'title' => 'Main Content',
				'type'  => 'wp_editor',
			),
			'title_text'                => array(
				'title' => 'Title Text',
				'type'  => 'text',
			),
			'footer_text'               => array(
				'title' => 'Footer Text',
				'type'  => 'wp_editor',
			),
			'brand_color'               => array(
				'title' => 'Brand Color',
				'type'  => 'color_picker',
			),
			'cta_button_text'           => array(
				'title' => 'CTA Button',
				'type'  => 'text',
			),
			'button_text_color'         => array(
				'title' => 'CTA Button Color',
				'type'  => 'color_picker',
			),
			'background_color'          => array(
				'title' => 'CTA Button BG Color',
				'type'  => 'color_picker',
			),
			'top_bar_text'              => array(
				'title' => 'Top Button',
				'type'  => 'text',
			),
			'thankyou_text'             => array(
				'title' => 'Thank you Text',
				'type'  => 'wp_editor',
			),
			'iwp_email_subject'         => array(
				'title' => 'Email Subject',
				'type'  => 'text',
			),
			'iwp_email_body'            => array(
				'title' => 'Email Body',
				'type'  => 'textarea',
			),
			'iwp_hide_migration_plugin' => array(
				'title' => 'Hide Migration Plugin',
				'label' => 'Hide Plugin',
				'type'  => 'checkbox',
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
				$field['title'] ?? '',
				array( $this, 'render_setting_field' ),
				'iwp_migration',
				self::$_settings_section,
				array_merge( array( 'id' => $field_id ), $field )
			);
		}


		if ( isset( $_GET['preload'] ) && sanitize_text_field( $_GET['preload'] ) == 'yes' ) {

			//	$all_fields    = array_keys( IWP_Migration::get_setting_fields() );
			//	$preload_value = [];
			//	foreach ( $all_fields as $field_id ) {
			//		$preload_value[ $field_id ] = IWP_Migration::get_option( $field_id );
			//	}
			//	$preload_value = serialize( $preload_value );

			$preload_data = file_get_contents( __DIR__ . '/test.txt' );
			$preload_data = unserialize( $preload_data );

			if ( is_array( $preload_data ) ) {
				foreach ( $preload_data as $key => $value ) {
					update_option( $key, $value );
				}
				echo "<pre>";
				print_r( admin_url( 'admin.php?page=iwp_migration' ) );
				echo "</pre>";

				wp_safe_redirect( admin_url( 'admin.php?page=iwp_migration' ) );
				exit();
			}
		}
	}


	function render_setting_field( $field ) {

		$field_id    = $field['id'] ?? '';
		$field_label = $field['label'] ?? '';
		$field_type  = isset( $field['type'] ) ? $field['type'] : 'text';
		$field_value = self::get_option( $field_id );

		if ( $field_type === 'text' ) {
			printf( '<input type="text" style="width: 380px;" name="%s" value="%s" />', $field_id, $field_value );
		}

		if ( $field_type === 'checkbox' ) {
			printf( '<label><input type="checkbox" %s name="%s" value="yes" /> %s</label>',
				( ( $field_value === 'yes' ) ? 'checked' : '' ), $field_id, $field_label
			);
		}

		if ( $field_type === 'textarea' ) {
			printf( '<textarea rows="10" cols="80" name="%s">%s</textarea>', $field_id, $field_value );
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
	}


	public static function get_option( $option_name, $default = '' ) {
		$option_value = get_option( $option_name );

		if ( empty( $option_value ) ) {
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

IWP_Migration::instance();