<?php
/*
Plugin Name: InstaWP Demo Helper
Description: Enables one-click migration requests from demo WordPress sites. Adds a customizable migration button to the admin bar and provides a branded migration interface that connects to InstaWP's API for seamless site transfers. Perfect for hosting providers offering temporary demos with migration capabilities.
Version: 1.0.7
Author: InstaWP Inc
*/

defined( 'IWP_MIG_PLUGIN_VERSION' ) || define( 'IWP_MIG_PLUGIN_VERSION', '1.0.7' );

class IWP_Migration {

	protected static $_instance = null;
	public static $_plugin_slug = 'iwp-demo-helper/iwp-demo-helper.php';
	public static $_plugin_slug_git = 'iwp-demo-helper-main/iwp-demo-helper.php';
	public static $_settings_section = 'iwp_migration_main_section';
	public static $_settings_group = 'iwp_migration_settings_group';


	public function __construct() {

		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) == 'iwp_demo_landing' ) {
			add_filter( 'admin_footer_text', '__return_false' );
			add_filter( 'update_footer', '__return_false', 99 );
		}

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_api' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_migrate_button' ), 999 );
		add_action( 'admin_menu', array( $this, 'add_migrate_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_iwp_migration_initiate', array( $this, 'iwp_migration_initiate' ) );
		add_action( 'wp_ajax_iwp_import_settings', array( $this, 'handle_import_settings_ajax' ) );
		add_action( 'wp_ajax_iwp_export_settings', array( $this, 'handle_export_settings_ajax' ) );

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
				'https://github.com/InstaWP/iwp-demo-helper', // URL to GitHub repo
				plugin_basename( __FILE__ ) // Plugin slug
			);
		} else {
			error_log( 'Update check class not found.' );
		}
	}

	/**
	 * Replace placeholders in URLs with actual values
	 * 
	 * @param string $url URL with placeholders
	 * @return string URL with placeholders replaced
	 */
	function replace_placeholders( $url ) {
		$replacements = array(
			'{{site_url}}'      => site_url(),
			'{{customer_email}}' => get_option( 'admin_email' ),
			'{{site_id}}'       => get_option( 'iwp_site_id', '' ),
			'{{site_hash}}'     => get_option( 'iwp_site_hash', '' ),
		);
		
		return str_replace( array_keys( $replacements ), array_values( $replacements ), $url );
	}

	/**
	 * Log API requests and responses for debugging
	 */
	public static function log_api_activity( $type, $url, $request_data = null, $response_data = null, $response_code = null ) {
		// Only log if debugging is enabled in both WordPress and plugin settings
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! get_option( 'iwp_debug_logging', '' ) ) {
			return;
		}

		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'type' => $type, // 'request' or 'response'
			'url' => $url,
		);

		if ( $type === 'request' && $request_data ) {
			$log_entry['request_headers'] = isset( $request_data['headers'] ) ? $request_data['headers'] : array();
			$log_entry['request_method'] = isset( $request_data['method'] ) ? $request_data['method'] : 'POST';
			
			// Sanitize sensitive data in request body
			$body = isset( $request_data['body'] ) ? $request_data['body'] : '';
			if ( is_string( $body ) ) {
				$decoded_body = json_decode( $body, true );
				if ( is_array( $decoded_body ) ) {
					// Mask sensitive data
					if ( isset( $decoded_body['api_key'] ) ) {
						$decoded_body['api_key'] = self::mask_api_key( $decoded_body['api_key'] );
					}
					$body = wp_json_encode( $decoded_body );
				}
			}
			$log_entry['request_body'] = $body;
		}

		if ( $type === 'response' && $response_data ) {
			$log_entry['response_code'] = $response_code;
			$log_entry['response_body'] = is_array( $response_data ) ? wp_json_encode( $response_data ) : $response_data;
		}

		// Format log message
		$log_message = sprintf(
			'[IWP Demo Helper] %s - %s: %s',
			strtoupper( $type ),
			$url,
			wp_json_encode( $log_entry )
		);

		error_log( $log_message );
	}

	/**
	 * Register REST API endpoints
	 */
	function register_rest_api() {
		register_rest_route( 'iwp-migration/v1', '/disable', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'api_disable_plugin' ),
			'permission_callback' => '__return_true', // Unauthenticated access
		) );
	}

	/**
	 * API endpoint to disable the plugin
	 */
	function api_disable_plugin( $request ) {
		// Update the disable plugin setting to 'yes'
		$updated = update_option( 'iwp_disable_plugin', 'yes' );
		
		if ( $updated ) {
			return rest_ensure_response( array(
				'success' => true,
				'message' => 'Migration plugin has been disabled successfully.',
				'timestamp' => current_time( 'mysql' )
			) );
		} else {
			return new WP_Error( 'update_failed', 'Failed to disable the plugin.', array( 'status' => 500 ) );
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
		// Check if plugin is disabled
		if ( get_option( 'iwp_disable_plugin' ) === 'yes' ) {
			wp_send_json_error( array( 'message' => 'Migration functionality is currently disabled.' ) );
		}

		$domain_name       = isset( $_POST['domain_name'] ) ? sanitize_text_field( $_POST['domain_name'] ) : '';
		
		// Check for the new "Open Link on Button Click" action first (it overrides all others)
		$open_link_action = get_option( 'iwp_open_link_action' ) === 'yes';
		
		if ( $open_link_action ) {
			$open_link_url = get_option( 'iwp_open_link_url' );
			if ( ! empty( $open_link_url ) ) {
				$processed_url = $this->replace_placeholders( $open_link_url );
				$open_in_new_tab = get_option( 'iwp_open_link_new_tab' ) === 'yes';
				wp_send_json_success( array(
					'open_link_action' => true,
					'redirect_url' => $processed_url,
					'open_new_tab' => $open_in_new_tab,
					'message' => 'Redirecting to external link...'
				));
			} else {
				wp_send_json_error( array( 'message' => 'Open link URL is not configured.' ) );
			}
		}
		
		// Check post-migration actions before making API calls
		$convert_sandbox = get_option( 'iwp_convert_sandbox' ) === 'yes';
		$show_domain_redirect = get_option( 'iwp_show_domain_redirect' ) === 'yes';
		$create_ticket = get_option( 'iwp_create_ticket' ) === 'yes';
		
		// Only proceed if at least one action is enabled
		if ( ! $convert_sandbox && ! $show_domain_redirect && ! $create_ticket ) {
			wp_send_json_error( [ 'message' => 'No migration actions are enabled. Please enable at least one option in the settings.' ] );
		}
		
		$iwp_email_subject = get_option( 'iwp_email_subject' );
		$iwp_email_subject = empty( $iwp_email_subject ) ? 'Sample email subject' : $iwp_email_subject;
		$iwp_email_body    = get_option( 'iwp_email_body' );
		$iwp_email_body    = empty( $iwp_email_body ) ? 'Sample email body' : $iwp_email_body;
		$iwp_api_key       = get_option( 'iwp_api_key' );
		$iwp_api_domain    = defined('INSTAWP_API_DOMAIN' ) ? INSTAWP_API_DOMAIN : 'https://app.instawp.io';
		
		// Only make API call if convert_sandbox or create_ticket is enabled
		$response_body = array( 'status' => true );
		
		if ( $convert_sandbox || $create_ticket ) {
			$body_args = array(
				'url'            => site_url(),
				'email'          => $create_ticket ? get_option( 'iwp_support_email' ) : '',
				'customer_email' => get_option( 'admin_email' ),
				'subject'        => $iwp_email_subject,
				'body'           => $iwp_email_body,
			);

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
			
			$api_url = $iwp_api_domain . '/api/v2/migrate-request';
			
			// Log the request
			self::log_api_activity( 'request', $api_url, $args );
			
			$response = wp_remote_post( $api_url, $args );

			if ( is_wp_error( $response ) ) {
				// Log the error
				self::log_api_activity( 'response', $api_url, null, array( 'error' => $response->get_error_message() ), 'error' );
				wp_send_json_error( [ 'message' => 'Network error: ' . $response->get_error_message() ] );
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			// Log the response
			self::log_api_activity( 'response', $api_url, null, $response_body, $response_code );

			// Handle specific HTTP status codes
			if ( $response_code === 401 ) {
				wp_send_json_error( [ 
					'message' => 'Invalid API key. Please check your InstaWP API key in plugin settings.',
					'code' => 401
				] );
			} elseif ( $response_code === 404 ) {
				// For 404, log the error but allow migration to continue with other actions
				error_log( 'InstaWP API returned 404 - site may not exist or already migrated. Continuing with other actions.' );
				$response_body = array( 
					'status' => true, 
					'message' => 'FYI: Site may not exist or a migrate request already exists.',
					'api_warning' => true,
					'api_status_code' => 404
				);
			} elseif ( $response_code === 403 ) {
				wp_send_json_error( [ 
					'message' => 'Access denied. Please verify your API permissions and try again.',
					'code' => 403
				] );
			} elseif ( $response_code === 429 ) {
				wp_send_json_error( [ 
					'message' => 'Too many requests. Please wait a moment and try again.',
					'code' => 429
				] );
			} elseif ( $response_code >= 500 ) {
				wp_send_json_error( [ 
					'message' => 'InstaWP API is temporarily unavailable. Please try again later.',
					'code' => $response_code
				] );
			} elseif ( $response_code !== 200 ) {
				// Handle other non-200 status codes
				$api_message = isset( $response_body['message'] ) ? $response_body['message'] : 'Unknown API error';
				wp_send_json_error( [ 
					'message' => 'API Error (Status ' . $response_code . '): ' . $api_message,
					'code' => $response_code
				] );
			} elseif ( ! isset( $response_body['status'] ) || $response_body['status'] !== true ) {
				// Handle API-level errors in successful HTTP responses
				$api_message = isset( $response_body['message'] ) ? $response_body['message'] : 'Migration request failed';
				wp_send_json_error( [ 
					'message' => $api_message,
					'code' => $response_code,
					'api_response' => $response_body
				] );
			}
		}
		
		// Add conversion flag to response if enabled
		if ( $convert_sandbox ) {
			$response_body['convert_sandbox'] = true;
		}
		
		// Handle webhook if any action is enabled
		if ( ( $convert_sandbox || $show_domain_redirect ) && ! empty( $webhook_url = get_option( 'iwp_webhook_url' ) ) ) {
			// Prepare webhook body args (use the same as API call if it was made, otherwise create minimal payload)
			$webhook_body_args = isset( $body_args ) ? $body_args : array(
				'url'            => site_url(),
				'email'          => $create_ticket ? get_option( 'iwp_support_email' ) : '',
				'customer_email' => get_option( 'admin_email' ),
				'subject'        => $iwp_email_subject,
				'body'           => $iwp_email_body,
			);
			
			$webhook_args = array(
				'body'        => json_encode( $webhook_body_args ),
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'method'      => 'POST',
				'data_format' => 'body',
			);
			
			// Log the webhook request
			self::log_api_activity( 'request', $webhook_url, $webhook_args );
			
			$webhook_response = wp_remote_post( $webhook_url, $webhook_args );

			if ( is_wp_error( $webhook_response ) ) {
				// Log webhook error
				self::log_api_activity( 'response', $webhook_url, null, array( 'error' => $webhook_response->get_error_message() ), 'error' );
				$response_body['webhook_status']  = false;
				$response_body['webhook_message'] = $webhook_response->get_error_message();
			} else {
				// Log webhook response
				$webhook_response_code = wp_remote_retrieve_response_code( $webhook_response );
				$webhook_response_body = json_decode( wp_remote_retrieve_body( $webhook_response ), true );
				self::log_api_activity( 'response', $webhook_url, null, $webhook_response_body, $webhook_response_code );
				$response_body['webhook_status'] = true;
			}
		}

		// Handle redirection if enabled
		if ( $show_domain_redirect ) {
			if ( ! empty( $redirection_url = get_option( 'iwp_redirection_url' ) ) ) {
				if ( ! empty( $domain_name ) ) {
					$redirection_url .= '?domain=' . $domain_name;
				}
				$response_body['redirection_url'] = $redirection_url;
			}
		}

		$response_body['actions'] = array(
			'convert_sandbox' => $convert_sandbox,
			'show_domain_redirect' => $show_domain_redirect,
			'create_ticket' => $create_ticket
		);
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
			'iwp_demo_helper_settings',
			'IWP Migrate Content',
			'Migrate Content',
			'manage_options',
			'iwp_demo_landing',
			array( $this, 'render_migrate_page' )
		);

		add_menu_page(
			'InstaWP Demo Helper Settings',
			'IWP Demo Helper',
			'manage_options',
			'iwp_demo_helper',
			array( $this, 'render_migrate_settings_page' ),
			'dashicons-migrate',
			200
		);

		// Add redirect page for backward compatibility
		add_submenu_page(
			null, // Hidden from menu
			'',
			'',
			'manage_options',
			'iwp_migration',
			array( $this, 'handle_old_url_redirect' )
		);

		if ( get_option( 'iwp_hide_migration_plugin' ) === 'yes' ) {
			remove_menu_page( 'iwp_demo_helper' );
		}
	}

	/**
	 * Handle redirect from old URL to new URL for backward compatibility
	 * 
	 * @since 1.0.7
	 * @return void
	 */
	function handle_old_url_redirect() {
		// Try PHP redirect first
		if ( ! headers_sent() ) {
			wp_redirect( admin_url( 'admin.php?page=iwp_demo_helper' ) );
			exit;
		}
		
		// Fallback to JavaScript redirect if headers already sent
		echo '<script type="text/javascript">window.location.href = "' . admin_url( 'admin.php?page=iwp_demo_helper' ) . '";</script>';
		echo '<noscript><meta http-equiv="refresh" content="0;url=' . admin_url( 'admin.php?page=iwp_demo_helper' ) . '" /></noscript>';
		echo '<p>Redirecting to the new page... <a href="' . admin_url( 'admin.php?page=iwp_demo_helper' ) . '">Click here if you are not redirected automatically</a>.</p>';
		exit;
	}


	function add_migrate_button( $wp_admin_bar ) {
		// Don't show button if plugin is disabled
		if ( get_option( 'iwp_disable_plugin' ) === 'yes' ) {
			return;
		}

		$button_location = IWP_Migration::get_option( 'top_button_location', 'left' );

		$args = array(
			'id'    => 'iwp_migration_btn',
			'title' => esc_attr( get_option( 'top_bar_text', 'Migrate' ) ),
			'href'  => admin_url( 'admin.php?page=iwp_demo_landing' ),
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


	/**
	 * Get default values for all settings
	 * Centralized location for all default values - DRY principle
	 */
	public static function mask_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			return '';
		}
		
		$length = strlen( $api_key );
		if ( $length <= 4 ) {
			// For very short keys, just show asterisks
			return str_repeat( '*', $length );
		}
		
		// Show only last 4 characters with asterisks for the rest
		$visible_end = substr( $api_key, -4 );
		$masked_start = str_repeat( '*', $length - 4 );
		
		return $masked_start . $visible_end;
	}

	public function sanitize_api_key_field( $value ) {
		// If the value is empty, allow clearing the API key
		if ( empty( $value ) ) {
			return '';
		}
		
		// Get the current API key from database
		$current_api_key = get_option( 'iwp_api_key', '' );
		
		// If the submitted value is the masked version, don't update (keep existing)
		if ( !empty( $current_api_key ) && $value === self::mask_api_key( $current_api_key ) ) {
			return $current_api_key;
		}
		
		// If the submitted value contains only asterisks and last 4 characters, don't update
		if ( !empty( $current_api_key ) && preg_match( '/^\*+[a-zA-Z0-9]{4}$/', $value ) ) {
			return $current_api_key;
		}
		
		// Otherwise, this is a new API key, sanitize and save it
		return sanitize_text_field( $value );
	}

	public static function get_default_values() {
		return array(
			// General Settings Tab
			'iwp_api_key' => '',
			'iwp_convert_sandbox' => 'yes',
			'iwp_create_ticket' => '', // Changed from 'yes' to '' (no)
			'iwp_support_email' => '',
			'iwp_open_link_action' => '', // Already '' (no)
			'iwp_open_link_url' => '',
			'iwp_open_link_new_tab' => '',
			'iwp_show_domain_redirect' => '', // Already '' (no)
			'iwp_domain_field_label' => '',
			'iwp_redirection_url' => '',
			'iwp_webhook_url' => '',
			
			// Content & Branding Tab
			'logo_url' => 'https://instawp.com/wp-content/uploads/2023/07/header-logo.svg',
			'title_text' => 'Demo Helper', // Changed from 'Migration Demo'
			'content' => 'This a demo site, which you can migrate to your own hosting account in just one click. Click on the Migrate button and we will migrate it!',
			'footer_text' => 'Powered by InstaWP',
			'thankyou_text' => 'Thank you, we have sent your request to our support team. We will be in touch soon',
			'brand_color' => '#005e54',
			'iwp_enable_src_demo_url' => '',
			
			// Button Configuration Tab
			'top_bar_text' => 'Go Live', // Changed from 'Migrate'
			'top_button_location' => 'left',
			'iwp_hide_cta_button' => '',
			'cta_btn_text' => 'Go Live', // Changed from 'Begin Migration'
			'cta_btn_text_color' => '#fff',
			'cta_btn_bg_color' => '#005e54',
			'close_btn_text' => 'Close',
			'close_btn_text_color' => '#fff',
			'close_btn_bg_color' => '#005e54',
			
			// Email & Notifications Tab
			'iwp_email_subject' => 'New migration request from {{customer_email}}',
			'iwp_email_body' => 'Hello, You have a new migration request from {{customer_email}} for site : {{site_url}}',
			
			// Advanced Tab
			'iwp_custom_css' => '',
			'iwp_disable_plugin' => '', // Already '' (no)
			'iwp_hide_migration_plugin' => '', // Already '' (no)
			'iwp_debug_logging' => '', // Debug logging disabled by default
		);
	}

	public static function get_setting_fields() {
		$defaults = self::get_default_values();
		return array(
			// General Settings Tab
			'iwp_api_key'               => array(
				'title'   => 'API Key',
				'type'    => 'api_key',
				'default' => $defaults['iwp_api_key'],
				'tab'     => 'general',
				'help'    => 'Your InstaWP API key for creating migration requests. Get this from your <a target="_blank" href="https://app.instawp.io/user/api-tokens">InstaWP API Tokens</a>.',
				'field_placeholder' => 'Leave it blank if you don\'t want to create migration requests'
			),
			'iwp_convert_sandbox' => array(
				'title'   => 'Convert Sandbox to Regular Site',
				'label'   => 'Convert sandbox to regular site after migration',
				'type'    => 'checkbox',
				'default' => $defaults['iwp_convert_sandbox'],
				'tab'     => 'general',
				'help'    => 'Automatically convert the sandbox to a regular site after migration request.',
			),
			'iwp_create_ticket' => array(
				'title'   => 'Create Support Ticket',
				'label'   => 'Create a support ticket to',
				'type'    => 'checkbox_with_field',
				'default' => $defaults['iwp_create_ticket'],
				'tab'     => 'general',
				'help'    => 'Send an email notification to the support team when migration is requested. Configure the Email Settings <a href="?page=iwp_demo_helper&tab=email">here</a>.',
				'linked_field' => 'iwp_support_email',
				'field_placeholder' => 'support@example.com'
			),
			'iwp_open_link_action' => array(
				'title'   => 'Open Link on Button Click',
				'label'   => 'Override all actions and open link directly when button is clicked',
				'type'    => 'checkbox_with_multiple_fields',
				'default' => $defaults['iwp_open_link_action'],
				'tab'     => 'general',
				'help'    => 'This action overrides all other actions. Supports placeholders: {{site_url}}, {{customer_email}}, {{site_id}}, {{site_hash}}',
				'linked_fields' => array(
					array(
						'name' => 'iwp_open_link_url',
						'type' => 'url',
						'label' => 'Redirect URL:',
						'placeholder' => 'https://example.com?site={{site_url}}&email={{customer_email}}&id={{site_id}}',
						'style' => 'width: 450px;'
					),
					array(
						'name' => 'iwp_open_link_new_tab',
						'type' => 'checkbox',
						'label' => 'Open in new tab',
						'placeholder' => '',
						'style' => 'margin-top: 5px;'
					)
				)
			),

			'iwp_show_domain_redirect' => array(
				'title'   => 'Show Domain Choice & Redirect',
				'label'   => 'Show domain input field and redirect after submission',
				'type'    => 'checkbox_with_multiple_fields',
				'default' => $defaults['iwp_show_domain_redirect'],
				'tab'     => 'general',
				'help'    => 'Display domain input field and redirect to specified URL after migration request.',
				'linked_fields' => array(
					array(
						'name' => 'iwp_domain_field_label',
						'type' => 'text',
						'label' => 'Domain Field Placeholder:',
						'placeholder' => 'Enter your domain name',
						'style' => 'width: 250px;'
					),
					array(
						'name' => 'iwp_redirection_url',
						'type' => 'url',
						'label' => 'Redirection URL:',
						'placeholder' => 'https://my-redirection-url.com',
						'style' => 'width: 350px;'
					)
				)
			),
			'iwp_webhook_url'           => array(
				'title'       => 'Webhook URL',
				'type'        => 'url',
				'placeholder' => 'https://my-webhook-url.com',
				'default'     => $defaults['iwp_webhook_url'],
				'tab'         => 'general',
				'help'        => 'Optional webhook URL to receive migration request data for integration with external systems.',
			),
			
			// Content & Branding Tab
			'logo_url'                  => array(
				'title'   => 'Logo URL',
				'type'    => 'text',
				'default' => $defaults['logo_url'],
				'tab'     => 'branding',
				'help'    => 'URL of the logo image to display at the top of the migration page. Max width: 150px.',
			),
			'title_text'                => array(
				'title'   => 'Title Text',
				'type'    => 'text',
				'default' => $defaults['title_text'],
				'tab'     => 'branding',
			),
			'content'                   => array(
				'title'   => 'Main Content',
				'type'    => 'wp_editor',
				'default' => $defaults['content'],
				'tab'     => 'branding',
				'help'    => 'Main content displayed on the migration page. Supports HTML and can include links.',
			),
			'footer_text'               => array(
				'title'   => 'Footer Text',
				'type'    => 'wp_editor',
				'default' => $defaults['footer_text'],
				'tab'     => 'branding',
			),
			'thankyou_text'             => array(
				'title'   => 'Thank you Text',
				'type'    => 'wp_editor',
				'default' => $defaults['thankyou_text'],
				'tab'     => 'branding',
				'help'    => 'Message shown to users after they submit a migration request (if no redirection URL is set).',
			),
			'brand_color'               => array(
				'title'   => 'Brand Color',
				'type'    => 'color_picker',
				'default' => $defaults['brand_color'],
				'tab'     => 'branding',
				'help'    => 'Primary brand color used throughout the migration interface.',
			),
			'iwp_enable_src_demo_url' => array(
				'title' => 'Append Source Demo URL',
				'label' => 'Append src_demo_url parameter to links in Main Content',
				'type' => 'checkbox',
				'default' => $defaults['iwp_enable_src_demo_url'],
				'tab'     => 'branding',
				'help'    => 'Automatically adds the current demo site URL as a parameter to all links in the main content for tracking purposes.',
			),
			
			// Button Configuration Tab
			'top_bar_text'              => array(
				'title'   => 'Top Button Text',
				'type'    => 'text',
				'default' => $defaults['top_bar_text'],
				'tab'     => 'buttons',
			),
			'top_button_location' => array(
				'title'   => 'Top Button Location',
				'type'    => 'radio',
				'options' => array(
					'left'  => 'Left',
					'right' => 'Right',
				),
				'default' => $defaults['top_button_location'],
				'tab'     => 'buttons',
				'help'    => 'Choose whether the migration button appears on the left or right side of the WordPress admin bar.',
			),
			'iwp_hide_cta_button' => array(
				'title' => 'Hide CTA Button',
				'label' => 'Hide CTA Button on migration page',
				'type' => 'checkbox',
				'default' => $defaults['iwp_hide_cta_button'],
				'tab'     => 'buttons',
				'help'    => 'Check this to hide the main migration button on the migration page (useful if using only the admin bar button).',
			),
			'cta_btn_text'              => array(
				'title'   => 'CTA Button - Text',
				'type'    => 'text',
				'default' => $defaults['cta_btn_text'],
				'tab'     => 'buttons',
			),
			'cta_btn_text_color'        => array(
				'title'   => 'CTA Button - Color',
				'type'    => 'color_picker',
				'default' => $defaults['cta_btn_text_color'],
				'tab'     => 'buttons',
			),
			'cta_btn_bg_color'          => array(
				'title'   => 'CTA Button - BG Color',
				'type'    => 'color_picker',
				'default' => $defaults['cta_btn_bg_color'],
				'tab'     => 'buttons',
			),
			'close_btn_text'            => array(
				'title'   => 'Close Button - Text',
				'type'    => 'text',
				'default' => $defaults['close_btn_text'],
				'tab'     => 'buttons',
			),
			'close_btn_text_color'      => array(
				'title'   => 'Close Button - Color',
				'type'    => 'color_picker',
				'default' => $defaults['close_btn_text_color'],
				'tab'     => 'buttons',
			),
			'close_btn_bg_color'        => array(
				'title'   => 'Close Button - BG Color',
				'type'    => 'color_picker',
				'default' => $defaults['close_btn_bg_color'],
				'tab'     => 'buttons',
			),
			
			// Email & Notifications Tab
			'iwp_email_subject'         => array(
				'title'   => 'Email Subject',
				'type'    => 'text',
				'default' => $defaults['iwp_email_subject'],
				'tab'     => 'email',
				'help'    => 'Subject line for email notifications. Use {{customer_email}} and {{site_url}} as placeholders.',
			),
			'iwp_email_body'            => array(
				'title'   => 'Email Body',
				'type'    => 'textarea',
				'default' => $defaults['iwp_email_body'],
				'tab'     => 'email',
				'help'    => 'Email template body. Available placeholders: {{customer_email}}, {{site_url}}',
			),
			
			// Advanced Tab
			'iwp_custom_css'            => array(
				'title'       => 'Custom CSS',
				'type'        => 'css_editor',
				'default'     => $defaults['iwp_custom_css'],
				'placeholder' => '/* Enter your custom CSS here */',
				'tab'         => 'advanced',
				'help'        => 'Custom CSS styles to override the default landing page appearance.',
			),
			'iwp_disable_plugin' => array(
				'title'   => 'Disable Plugin',
				'label'   => 'Disable the plugin\'s functionality completely',
				'type'    => 'checkbox',
				'default' => $defaults['iwp_disable_plugin'],
				'tab'     => 'advanced',
				'help'    => 'When enabled, completely disables the plugin\'s functionality. Can be controlled via Rest API: POST ' . site_url() . '/wp-json/iwp-demo-helper/v1/disable (no authentication required).',
			),
			'iwp_hide_migration_plugin' => array(
				'title'   => 'Hide Migration Plugin',
				'label'   => 'Hide Plugin',
				'type'    => 'checkbox',
				'default' => $defaults['iwp_hide_migration_plugin'],
				'tab'     => 'advanced',
				'help'    => 'Hides this plugin from the WordPress plugins list and from the left sidemenu. Access settings via direct URL: /wp-admin/admin.php?page=iwp_demo_helper',
			),
			'iwp_debug_logging' => array(
				'title'   => 'Debug Logging',
				'label'   => 'Enable API request/response logging',
				'type'    => 'checkbox',
				'default' => $defaults['iwp_debug_logging'],
				'tab'     => 'advanced',
				'help'    => 'Log API requests and responses to WordPress debug log. Only works when WP_DEBUG is enabled in wp-config.php. Logs include request headers, body, and response data for troubleshooting.',
			),
			'iwp_reset_settings' => array(
				'title'   => 'Reset All Settings',
				'type'    => 'reset_button',
				'tab'     => 'advanced',
				'help'    => 'Reset all plugin settings to their default values. This action cannot be undone.',
			),
			'iwp_export_settings' => array(
				'title'   => 'Export Settings',
				'type'    => 'export_button',
				'tab'     => 'advanced',
				'help'    => 'Export all plugin settings to a JSON file for backup or migration purposes.',
			),
			'iwp_import_settings' => array(
				'title'   => 'Import Settings',
				'type'    => 'import_button',
				'tab'     => 'advanced',
				'help'    => 'Import plugin settings from a JSON file. This will overwrite current settings.',
			),
		);
	}


	function render_migrate_settings_page() {
		include 'templates/settings.php';
	}


	function register_settings() {
		// Register all settings
		foreach ( self::get_setting_fields() as $field_id => $field ) {
			// Special handling for API key field to prevent overwriting with masked values
			if ( $field['type'] === 'api_key' ) {
				register_setting( self::$_settings_group, $field_id, array(
					'sanitize_callback' => array( $this, 'sanitize_api_key_field' )
				) );
			} else {
				register_setting( self::$_settings_group, $field_id );
			}
			
			// Also register linked fields for checkbox_with_field type
			if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) ) {
				register_setting( self::$_settings_group, $field['linked_field'] );
			}
			
			// Also register linked fields for checkbox_with_multiple_fields type
			if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
				foreach ( $field['linked_fields'] as $linked_field ) {
					if ( isset( $linked_field['name'] ) ) {
						register_setting( self::$_settings_group, $linked_field['name'] );
					}
				}
			}
		}

		// Define tabs
		$tabs = array(
			'general'  => 'General Settings',
			'branding' => 'Content & Branding',
			'buttons'  => 'Button Configuration',
			'email'    => 'Email & Notifications',
			'advanced' => 'Advanced'
		);

		// Create sections for each tab
		foreach ( $tabs as $tab_key => $tab_title ) {
			add_settings_section( 
				'iwp_migration_' . $tab_key . '_section', 
				$tab_title, 
				null, 
				'iwp_migration_' . $tab_key 
			);
		}

		// Add fields to their respective tabs
		foreach ( self::get_setting_fields() as $field_id => $field ) {
			$tab = isset( $field['tab'] ) ? $field['tab'] : 'general';
			add_settings_field(
				$field_id,
				isset( $field['title'] ) ? $field['title'] : '',
				array( $this, 'render_setting_field' ),
				'iwp_migration_' . $tab,
				'iwp_migration_' . $tab . '_section',
				array_merge( array( 'id' => $field_id ), $field )
			);
		}
	}


	function render_setting_field( $field ) {

		$field_id    = $field['id'] ?? '';
		$field_label = $field['label'] ?? '';
		$field_help  = $field['help'] ?? '';
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : ( isset( $field['field_placeholder'] ) ? $field['field_placeholder'] : '' );
		$field_type  = isset( $field['type'] ) ? $field['type'] : 'text';
		$field_value = get_option( $field_id, ( $field['default'] ?? '' ) );
		$is_disabled = '';


		if ( $field_type === 'text' ) {
			printf( '<input %s type="text" style="width: 380px;" name="%s" value="%s" placeholder="%s" />', $is_disabled, $field_id, $field_value, $placeholder );
		}

		if ( $field_type === 'api_key' ) {
			// Secure API key field - only shows last 4 characters, never the full key
			if ( !empty( $field_value ) ) {
				// Show masked version with only last 4 characters visible
				$masked_value = self::mask_api_key( $field_value );
				$status_text = '✓ API Key Configured (showing last 4 characters)';
				
				printf( '<div style="margin-bottom: 10px;">' );
				printf( '<input type="text" readonly style="width: 300px; background-color: #f7f7f7; color: #666;" value="%s" title="API key is masked for security - only last 4 characters shown" />', 
					esc_attr( $masked_value ) );
				printf( '<button type="button" onclick="iwpClearApiKey(this)" style="margin-left: 8px;" class="button button-secondary">Clear</button>' );
				printf( '</div>' );
				printf( '<div style="color: green; font-size: 12px;">%s</div>', $status_text );
				printf( '<div style="color: #666; font-size: 11px; margin-top: 5px;">To update the API key, clear the current key and enter a new one.</div>' );
				
				// Hidden field to preserve the actual API key value during form submission
				printf( '<input type="hidden" name="%s" value="%s" />', $field_id, esc_attr( $field_value ) );
				
				// Add JavaScript for clear functionality
				printf( '<script>
				function iwpClearApiKey(btn) {
					if (confirm("Are you sure you want to clear the API key? You will need to enter a new one to maintain API functionality.")) {
						// Hide the readonly field and status
						btn.parentNode.style.display = "none";
						btn.parentNode.parentNode.querySelector("div[style*=color]").style.display = "none";
						btn.parentNode.parentNode.querySelector("div[style*=margin-top]").style.display = "none";
						
						// Remove the hidden field
						var hiddenField = btn.parentNode.parentNode.querySelector("input[type=hidden]");
						if (hiddenField) hiddenField.remove();
						
						// Show new input field
						var newInputDiv = document.createElement("div");
						newInputDiv.innerHTML = \'<input type="password" style="width: 380px;" name="%s" value="" placeholder="%s" title="Enter your new API key" /><div style="color: #666; font-size: 12px; margin-top: 5px;">API key will be securely stored and masked once saved.</div>\';
						btn.parentNode.parentNode.appendChild(newInputDiv);
					}
				}
				</script>', $field_id, esc_attr( $placeholder ) );
			} else {
				// Show password input for initial setup
				printf( '<input %s type="password" style="width: 380px;" name="%s" value="" placeholder="%s" title="Enter your API key" />', 
					$is_disabled, $field_id, $placeholder );
				printf( '<div style="color: #666; font-size: 12px; margin-top: 5px;">API key will be securely stored and masked once saved. Only the last 4 characters will be visible.</div>' );
			}
		}

		if ( $field_type === 'hidden' ) {
			// Hidden fields don't render anything visible
			return;
		}

		if ( $field_type === 'url' ) {
			printf( '<input type="url" style="width: 380px;" name="%s" value="%s" placeholder="%s" />', $field_id, $field_value, $placeholder );
		}

		if ( $field_type === 'checkbox' ) {
			printf( '<label style="cursor: pointer;"><input type="checkbox" %s name="%s" value="yes" /> %s</label>',
				( ( $field_value === 'yes' ) ? 'checked' : '' ), $field_id, $field_label
			);
		}

		if ( $field_type === 'checkbox_with_field' ) {
			$linked_field = $field['linked_field'] ?? '';
			$linked_field_value = get_option( $linked_field, '' );
			$is_checked = ( $field_value === 'yes' ) ? 'checked' : '';
			$linked_field_style = ( $field_value === 'yes' ) ? '' : 'display: none;';
			
			// Determine the input type based on the field name or a specific setting
			$input_type = 'text'; // default
			if ( strpos( $linked_field, 'email' ) !== false ) {
				$input_type = 'email';
			} elseif ( strpos( $linked_field, 'url' ) !== false ) {
				$input_type = 'url';
			}
			
			printf( '<div class="iwp-checkbox-with-field-container">' );
			printf( '<label style="cursor: pointer; margin-bottom: 10px; display: block;"><input type="checkbox" %s name="%s" value="yes" class="iwp-checkbox-with-field" data-linked-field="%s" /> %s</label>',
				$is_checked, $field_id, $linked_field, $field_label
			);
			if ( ! empty( $linked_field ) ) {
				printf( '<input type="%s" style="width: 300px; margin-left: 20px; %s" name="%s" value="%s" placeholder="%s" class="iwp-linked-field" />',
					$input_type, $linked_field_style, $linked_field, $linked_field_value, $field['field_placeholder'] ?? ''
				);
			}
			printf( '</div>' );
		}

		if ( $field_type === 'checkbox_with_multiple_fields' ) {
			$linked_fields = $field['linked_fields'] ?? array();
			$is_checked = ( $field_value === 'yes' ) ? 'checked' : '';
			$container_style = ( $field_value === 'yes' ) ? '' : 'display: none;';
			
			// Collect field names for JavaScript
			$field_names = array();
			foreach ( $linked_fields as $linked_field ) {
				$field_names[] = $linked_field['name'];
			}
			
			printf( '<div class="iwp-checkbox-with-multiple-fields-container">' );
			printf( '<label style="cursor: pointer; margin-bottom: 10px; display: block;"><input type="checkbox" %s name="%s" value="yes" class="iwp-checkbox-with-multiple-fields" data-linked-fields="%s" /> %s</label>',
				$is_checked, $field_id, esc_attr( implode( ',', $field_names ) ), $field_label
			);
			
			printf( '<div class="iwp-multiple-linked-fields" style="margin-left: 20px; %s">', $container_style );
			
			foreach ( $linked_fields as $linked_field ) {
				$linked_field_name = $linked_field['name'];
				// For checkboxes, default should be empty, not placeholder
				$default_value = ( $linked_field['type'] === 'checkbox' ) ? '' : '';
				$linked_field_value = get_option( $linked_field_name, $default_value );
				$linked_field_type = $linked_field['type'] ?? 'text';
				$linked_field_label = $linked_field['label'] ?? '';
				$linked_field_placeholder = $linked_field['placeholder'] ?? '';
				$linked_field_style = $linked_field['style'] ?? 'width: 300px;';
				
				printf( '<div style="margin-bottom: 8px;">' );
				
				if ( $linked_field_type === 'checkbox' ) {
					$is_checked = ( $linked_field_value === 'yes' ) ? 'checked' : '';
					printf( '<label style="cursor: pointer; %s"><input type="checkbox" %s name="%s" value="yes" class="iwp-multiple-linked-field" /> %s</label>',
						$linked_field_style, $is_checked, $linked_field_name, esc_html( $linked_field_label )
					);
				} else {
					if ( ! empty( $linked_field_label ) ) {
						printf( '<label style="display: inline-block; width: 150px; font-weight: normal;">%s</label>', esc_html( $linked_field_label ) );
					}
					printf( '<input type="%s" style="%s" name="%s" value="%s" placeholder="%s" class="iwp-multiple-linked-field" />',
						$linked_field_type, $linked_field_style, $linked_field_name, esc_attr( $linked_field_value ), esc_attr( $linked_field_placeholder )
					);
				}
				printf( '</div>' );
			}
			
			printf( '</div>' );
			printf( '</div>' );
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

		if ( $field_type === 'select' ) {
			$options = isset( $field['options'] ) ? $field['options'] : array();
			if ( ! empty( $options ) ) {
				printf( '<select name="%s" style="width: 380px;">', $field_id );
				foreach ( $options as $value => $label ) {
					printf( '<option value="%s" %s>%s</option>',
						esc_attr( $value ),
						selected( $field_value, $value, false ),
						esc_html( $label )
					);
				}
				echo '</select>';
			}
		}

		if ( $field_type === 'radio' ) {
			$options = isset( $field['options'] ) ? $field['options'] : array();
			if ( ! empty( $options ) ) {
				echo '<fieldset>';
				foreach ( $options as $value => $label ) {
					printf( '<label style="padding-right: 10px;"><input type="radio" name="%s" value="%s" %s /> %s</label>',
						$field_id,
						esc_attr( $value ),
						checked( $field_value, $value, false ),
						esc_html( $label )
					);
				}
				echo '</fieldset>';
			}
		}
		
		if ( $field_type === 'reset_button' ) {
			// Create reset button with JavaScript to avoid form nesting issues
			$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'advanced';
			$reset_url = admin_url('admin.php?page=iwp_demo_helper&tab=' . $current_tab);
			$nonce = wp_create_nonce('iwp_migration_settings_group-options');
			
			printf( 
				'<button type="button" class="button button-secondary" onclick="iwpResetSettings()" style="margin-top: 10px;">Reset All Settings to Default</button>' .
				'<script>
				function iwpResetSettings() {
					if (confirm("Are you sure you want to reset all settings to default values? This action cannot be undone.")) {
						var form = document.createElement("form");
						form.method = "POST";
						form.action = "%s";
						
						var nonceField = document.createElement("input");
						nonceField.type = "hidden";
						nonceField.name = "_wpnonce";
						nonceField.value = "%s";
						form.appendChild(nonceField);
						
						var refererField = document.createElement("input");
						refererField.type = "hidden";
						refererField.name = "_wp_http_referer";
						refererField.value = "%s";
						form.appendChild(refererField);
						
						var resetField = document.createElement("input");
						resetField.type = "hidden";
						resetField.name = "iwp_reset_settings";
						resetField.value = "1";
						form.appendChild(resetField);
						
						document.body.appendChild(form);
						form.submit();
					}
				}
				</script>',
				esc_url($reset_url),
				esc_attr($nonce),
				esc_attr(wp_unslash($_SERVER['REQUEST_URI']))
			);
		}
		
		if ( $field_type === 'export_button' ) {
			printf( 
				'<button type="button" class="button button-primary" onclick="iwpExportSettings()" style="margin-top: 10px;">Export Settings to JSON</button>' .
				'<div id="iwp_export_status" style="margin-top: 10px;"></div>' .
				'<script>
				function iwpExportSettings() {
					var statusDiv = document.getElementById("iwp_export_status");
					statusDiv.innerHTML = "<p style=\"color: blue;\">Generating export...</p>";
					
					var formData = new FormData();
					formData.append("action", "iwp_export_settings");
					formData.append("nonce", "' . wp_create_nonce('iwp_export_settings') . '");
					
					fetch(ajaxurl, {
						method: "POST",
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							statusDiv.innerHTML = "<p style=\"color: green;\">Export ready! Download will start automatically.</p>";
							
							// Create download link
							var blob = new Blob([data.data.json], { type: "application/json" });
							var url = window.URL.createObjectURL(blob);
							var a = document.createElement("a");
							a.href = url;
							a.download = data.data.filename;
							document.body.appendChild(a);
							a.click();
							window.URL.revokeObjectURL(url);
							document.body.removeChild(a);
							
							setTimeout(() => {
								statusDiv.innerHTML = "";
							}, 3000);
						} else {
							statusDiv.innerHTML = "<p style=\"color: red;\">Error: " + (data.data || "Unknown error") + "</p>";
						}
					})
					.catch(error => {
						statusDiv.innerHTML = "<p style=\"color: red;\">Error: " + error.message + "</p>";
					});
				}
				</script>'
			);
		}
		
		if ( $field_type === 'import_button' ) {
			printf( 
				'<div style="margin-top: 10px;">' .
				'<input type="file" id="iwp_import_file" accept=".json" style="margin-bottom: 10px; display: block;" />' .
				'<button type="button" class="button button-primary" onclick="iwpImportSettings()">Import Settings from JSON</button>' .
				'<div id="iwp_import_status" style="margin-top: 10px;"></div>' .
				'</div>' .
				'<script>
				function iwpImportSettings() {
					var fileInput = document.getElementById("iwp_import_file");
					var statusDiv = document.getElementById("iwp_import_status");
					
					if (!fileInput.files.length) {
						statusDiv.innerHTML = "<p style=\"color: red;\">Please select a JSON file first.</p>";
						return;
					}
					
					var file = fileInput.files[0];
					if (!file.name.toLowerCase().endsWith(".json")) {
						statusDiv.innerHTML = "<p style=\"color: red;\">Please select a valid JSON file.</p>";
						return;
					}
					
					if (!confirm("Are you sure you want to import settings? This will overwrite your current settings.")) {
						return;
					}
					
					var reader = new FileReader();
					reader.onload = function(e) {
						var formData = new FormData();
						formData.append("action", "iwp_import_settings");
						formData.append("nonce", "' . wp_create_nonce('iwp_import_settings') . '");
						formData.append("settings_json", e.target.result);
						
						statusDiv.innerHTML = "<p style=\"color: blue;\">Importing settings...</p>";
						
						fetch(ajaxurl, {
							method: "POST",
							body: formData
						})
						.then(response => response.json())
						.then(data => {
							if (data.success) {
								statusDiv.innerHTML = "<p style=\"color: green;\">Settings imported successfully! Reloading page...</p>";
								setTimeout(() => window.location.reload(), 1500);
							} else {
								statusDiv.innerHTML = "<p style=\"color: red;\">Error: " + (data.data || "Unknown error") + "</p>";
							}
						})
						.catch(error => {
							statusDiv.innerHTML = "<p style=\"color: red;\">Error: " + error.message + "</p>";
						});
					};
					reader.readAsText(file);
				}
				</script>'
			);
		}
		
		// Display help text if available
		if ( ! empty( $field_help ) ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $field_help ) );
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

	/**
	 * Reset all plugin settings to their default values
	 */
	public static function reset_all_settings() {
		// Use centralized default values
		$defaults = self::get_default_values();
		
		// Log the reset action for debugging
		error_log('IWP Migration: reset_all_settings() called - resetting ' . count($defaults) . ' settings');
		
		// Reset all settings to their defaults
		foreach ( $defaults as $option_name => $default_value ) {
			$old_value = get_option( $option_name );
			delete_option( $option_name );
			if ( $default_value !== '' ) {
				update_option( $option_name, $default_value );
				error_log("IWP Migration: Reset $option_name from '$old_value' to '$default_value'");
			} else {
				error_log("IWP Migration: Reset $option_name from '$old_value' to empty");
			}
		}
		
		// Handle linked fields that might not be in defaults array
		$all_fields = self::get_setting_fields();
		foreach ( $all_fields as $field_id => $field ) {
			// Handle linked fields for checkbox_with_field type
			if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) ) {
				$linked_field_name = $field['linked_field'];
				if ( !isset( $defaults[$linked_field_name] ) ) {
					delete_option( $linked_field_name );
				}
			}
			
			// Handle linked fields for checkbox_with_multiple_fields type
			if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
				foreach ( $field['linked_fields'] as $linked_field ) {
					if ( isset( $linked_field['name'] ) ) {
						delete_option( $linked_field['name'] );
					}
				}
			}
		}
	}

	/**
	 * Initialize default settings on plugin activation or first run
	 */
	public static function initialize_default_settings() {
		$defaults = self::get_default_values();
		
		foreach ( $defaults as $option_name => $default_value ) {
			// Only set if option doesn't exist (don't overwrite existing settings)
			if ( get_option( $option_name ) === false ) {
				if ( $default_value !== '' ) {
					update_option( $option_name, $default_value );
				}
			}
		}
	}

	/**
	 * Handle export settings via AJAX
	 */
	public static function handle_export_settings_ajax() {
		// Verify nonce
		if ( !wp_verify_nonce( $_POST['nonce'], 'iwp_export_settings' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}
		
		// Check user permissions
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You do not have sufficient permissions to export settings.' );
		}
		
		// Get all plugin settings
		$all_fields = self::get_setting_fields();
		$settings = array();
		
		foreach ( $all_fields as $field_id => $field ) {
			// Skip buttons and non-setting fields
			if ( in_array( $field['type'], array( 'reset_button', 'export_button', 'import_button' ) ) ) {
				continue;
			}
			
			$value = get_option( $field_id, $field['default'] ?? '' );
			$settings[ $field_id ] = $value;
			
			// Handle linked fields for checkbox_with_field type
			if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) ) {
				$linked_field_name = $field['linked_field'];
				$linked_value = get_option( $linked_field_name, '' );
				$settings[ $linked_field_name ] = $linked_value;
			}
			
			// Handle linked fields for checkbox_with_multiple_fields type
			if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
				foreach ( $field['linked_fields'] as $linked_field ) {
					if ( isset( $linked_field['name'] ) ) {
						$linked_field_name = $linked_field['name'];
						$linked_value = get_option( $linked_field_name, '' );
						$settings[ $linked_field_name ] = $linked_value;
					}
				}
			}
		}
		
		// Add metadata
		$export_data = array(
			'plugin_version' => '1.0.7',
			'export_date' => current_time( 'Y-m-d H:i:s' ),
			'site_url' => site_url(),
			'settings' => $settings
		);
		
		// Generate filename
		$filename = 'iwp-demo-helper-settings-' . date( 'Y-m-d-H-i-s' ) . '.json';
		
		// Return JSON data for client-side download
		wp_send_json_success( array(
			'json' => json_encode( $export_data, JSON_PRETTY_PRINT ),
			'filename' => $filename
		) );
	}

	/**
	 * Import plugin settings from JSON via AJAX
	 */
	public static function handle_import_settings_ajax() {
		// Verify nonce
		if ( !wp_verify_nonce( $_POST['nonce'], 'iwp_import_settings' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}
		
		// Check user permissions
		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You do not have sufficient permissions to import settings.' );
		}
		
		// Get JSON data
		$settings_json = wp_unslash( $_POST['settings_json'] );
		
		if ( empty( $settings_json ) ) {
			wp_send_json_error( 'No settings data provided.' );
		}
		
		// Parse JSON
		$import_data = json_decode( $settings_json, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( 'Invalid JSON format: ' . json_last_error_msg() );
		}
		
		// Validate structure
		if ( !isset( $import_data['settings'] ) || !is_array( $import_data['settings'] ) ) {
			wp_send_json_error( 'Invalid settings format. Expected settings array.' );
		}
		
		$settings = $import_data['settings'];
		$all_fields = self::get_setting_fields();
		$imported_count = 0;
		
		// Import settings
		foreach ( $settings as $option_name => $value ) {
			// Only import settings that exist in our field definitions
			$field_exists = isset( $all_fields[ $option_name ] );
			
			// Or check if it's a linked field
			if ( !$field_exists ) {
				foreach ( $all_fields as $field ) {
					if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) && $field['linked_field'] === $option_name ) {
						$field_exists = true;
						break;
					}
					if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
						foreach ( $field['linked_fields'] as $linked_field ) {
							if ( isset( $linked_field['name'] ) && $linked_field['name'] === $option_name ) {
								$field_exists = true;
								break 2;
							}
						}
					}
				}
			}
			
			if ( $field_exists ) {
				update_option( $option_name, $value );
				$imported_count++;
			}
		}
		
		wp_send_json_success( "Successfully imported {$imported_count} settings." );
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

// WP-CLI Commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * IWP Demo Helper CLI Commands
	 */
	class IWP_Migration_CLI_Command {
		
		/**
		 * Export plugin settings to JSON file
		 *
		 * ## OPTIONS
		 *
		 * [<file>]
		 * : The file path to export settings to. If not provided, outputs to stdout.
		 *
		 * ## EXAMPLES
		 *
		 *     wp iwp-demo-helper export
		 *     wp iwp-demo-helper export /path/to/settings.json
		 *
		 * @when after_wp_load
		 */
		public function export( $args, $assoc_args ) {
			// Get all plugin settings
			$all_fields = IWP_Migration::get_setting_fields();
			$settings = array();
			
			foreach ( $all_fields as $field_id => $field ) {
				// Skip buttons and non-setting fields
				if ( in_array( $field['type'], array( 'reset_button', 'export_button', 'import_button' ) ) ) {
					continue;
				}
				
				$value = get_option( $field_id, $field['default'] ?? '' );
				$settings[ $field_id ] = $value;
				
				// Handle linked fields for checkbox_with_field type
				if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) ) {
					$linked_field_name = $field['linked_field'];
					$linked_value = get_option( $linked_field_name, '' );
					$settings[ $linked_field_name ] = $linked_value;
				}
				
				// Handle linked fields for checkbox_with_multiple_fields type
				if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
					foreach ( $field['linked_fields'] as $linked_field ) {
						if ( isset( $linked_field['name'] ) ) {
							$linked_field_name = $linked_field['name'];
							$linked_value = get_option( $linked_field_name, '' );
							$settings[ $linked_field_name ] = $linked_value;
						}
					}
				}
			}
			
			// Add metadata
			$export_data = array(
				'plugin_version' => '1.0.7',
				'export_date' => current_time( 'Y-m-d H:i:s' ),
				'site_url' => site_url(),
				'settings' => $settings
			);
			
			$json_output = json_encode( $export_data, JSON_PRETTY_PRINT );
			
			if ( isset( $args[0] ) ) {
				// Export to file
				$file_path = $args[0];
				$result = file_put_contents( $file_path, $json_output );
				
				if ( $result === false ) {
					WP_CLI::error( "Failed to write to file: {$file_path}" );
				}
				
				WP_CLI::success( "Settings exported to: {$file_path}" );
			} else {
				// Output to stdout
				WP_CLI::line( $json_output );
			}
		}
		
		/**
		 * Import plugin settings from JSON file
		 *
		 * ## OPTIONS
		 *
		 * <file>
		 * : The JSON file path to import settings from.
		 *
		 * [--dry-run]
		 * : Show what would be imported without actually importing.
		 *
		 * ## EXAMPLES
		 *
		 *     wp iwp-demo-helper import /path/to/settings.json
		 *     wp iwp-demo-helper import /path/to/settings.json --dry-run
		 *
		 * @when after_wp_load
		 */
		public function import( $args, $assoc_args ) {
			if ( ! isset( $args[0] ) ) {
				WP_CLI::error( 'Please provide a JSON file path to import.' );
			}
			
			$file_path = $args[0];
			$dry_run = isset( $assoc_args['dry-run'] );
			
			if ( ! file_exists( $file_path ) ) {
				WP_CLI::error( "File not found: {$file_path}" );
			}
			
			$settings_json = file_get_contents( $file_path );
			
			if ( $settings_json === false ) {
				WP_CLI::error( "Failed to read file: {$file_path}" );
			}
			
			// Parse JSON
			$import_data = json_decode( $settings_json, true );
			
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				WP_CLI::error( 'Invalid JSON format: ' . json_last_error_msg() );
			}
			
			// Validate structure
			if ( ! isset( $import_data['settings'] ) || ! is_array( $import_data['settings'] ) ) {
				WP_CLI::error( 'Invalid settings format. Expected settings array.' );
			}
			
			$settings = $import_data['settings'];
			$all_fields = IWP_Migration::get_setting_fields();
			$import_count = 0;
			
			if ( $dry_run ) {
				WP_CLI::line( 'DRY RUN: The following settings would be imported:' );
				WP_CLI::line( '' );
			}
			
			// Import settings
			foreach ( $settings as $option_name => $value ) {
				// Only import settings that exist in our field definitions
				$field_exists = isset( $all_fields[ $option_name ] );
				
				// Or check if it's a linked field
				if ( ! $field_exists ) {
					foreach ( $all_fields as $field ) {
						if ( $field['type'] === 'checkbox_with_field' && isset( $field['linked_field'] ) && $field['linked_field'] === $option_name ) {
							$field_exists = true;
							break;
						}
						if ( $field['type'] === 'checkbox_with_multiple_fields' && isset( $field['linked_fields'] ) ) {
							foreach ( $field['linked_fields'] as $linked_field ) {
								if ( isset( $linked_field['name'] ) && $linked_field['name'] === $option_name ) {
									$field_exists = true;
									break 2;
								}
							}
						}
					}
				}
				
				if ( $field_exists ) {
					if ( $dry_run ) {
						$current_value = get_option( $option_name, '' );
						WP_CLI::line( "  {$option_name}: '{$current_value}' → '{$value}'" );
					} else {
						update_option( $option_name, $value );
					}
					$import_count++;
				} else if ( $dry_run ) {
					WP_CLI::warning( "  {$option_name}: Field not recognized, would be skipped" );
				}
			}
			
			if ( $dry_run ) {
				WP_CLI::line( '' );
				WP_CLI::success( "DRY RUN: Would import {$import_count} settings." );
				WP_CLI::line( 'Run without --dry-run to actually import the settings.' );
			} else {
				WP_CLI::success( "Successfully imported {$import_count} settings." );
			}
		}
	}
	
	// Register the CLI commands
	WP_CLI::add_command( 'iwp-demo-helper', 'IWP_Migration_CLI_Command' );
}

// Initialize the plugin
IWP_Migration::instance();

// Initialize default settings on first run
IWP_Migration::initialize_default_settings();
