<?php
/*
Plugin Name: IWP Migration
Description: A custom plugin to add a button with specific settings.
Version: 1.0
Author: InstaWP
*/

// Admin settings
include( plugin_dir_path( __FILE__ ) . 'admin_settings.php' );

// Adding button to the top bar
function iwp_migration_button( $wp_admin_bar ) {

	$top_bar_text = get_option( 'top_bar_text' );

	// add a button to the top admin bar with color, text and background
	$args = array(
		'id'    => 'iwp_migration_btn',
		'title' => esc_attr( get_option( 'top_bar_text', 'Migrate' ) ),
		'href'  => admin_url( 'admin.php?page=iwp_migrate_content' ),
		'meta'  => array(
			'class' => 'menupop iwp_migration_class',

		)
	);

	$wp_admin_bar->add_node( $args );
}

add_action( 'admin_bar_menu', 'iwp_migration_button', 999 );

function iwp_migration_add_submenu_page() {
	add_submenu_page(
		'iwp_migration_settings',          // parent_slug: It should be the same slug used for your main settings page.
		'IWP Migrate Content',             // page_title
		'Migrate Content',                 // menu_title
		'manage_options',                  // capability: Only users with this capability can access the page.
		'iwp_migrate_content',             // menu_slug
		'iwp_migration_display_content'    // callback function
	);

	remove_menu_page( 'iwp_migration' );
}

add_action( 'admin_menu', 'iwp_migration_add_submenu_page' );

function iwp_migration_display_content() {
	?>
    <div class="iwp_migration_container iwp-migration-screen-1">

        <div class="iwp_migration_title">
            <img src="<?php echo esc_attr( get_option( 'logo_url' ) ); ?>" style="max-width: 150px;" alt="Logo"/>
            <h1><?php echo esc_attr( get_option( 'title_text' ) ); ?></h1>
        </div>

        <!--<?php echo esc_attr( get_option( 'background_color' ) ); ?>;-->
        <div style="background-color: #F9FAFB; padding: 20px ; align-items: center; " class="iwp_migration_content">
            <div style="margin-top: 10px; margin-bottom: 25px" id="iwp_migration_content">
				<?php echo wp_kses_post( get_option( 'content' ) ); ?>
            </div>

            <button style="background-color:<?php echo esc_attr( get_option( 'brand_color' ) ); ?>; color:<?php echo esc_attr( get_option( 'button_text_color' ) ); ?>;" class="iwp_btn_primary iwp-btn-main">
				<?php echo esc_attr( get_option( 'cta_button_text' ) ); ?>
            </button>
            <div class="iwp_migration_footer">
				<?php echo wp_kses_post( get_option( 'footer_text' ) ); ?>
            </div>
        </div>

    </div>
	<?php
}

// Thank you page.

function iwp_migration_add_submenu_page_thankyou() {
	add_submenu_page(
		'iwp_migration_settings',          // parent_slug: It should be the same slug used for your main settings page.
		'IWP Migrate Thank you',             // page_title
		'Migrate Thank you',                 // menu_title
		'manage_options',                  // capability: Only users with this capability can access the page.
		'iwp_migrate_content',             // menu_slug
		'iwp_migration_display_content_thankyou'    // callback function
	);
}

add_action( 'admin_menu', 'iwp_migration_add_submenu_page_thankyou' );

function iwp_migration_display_content_thankyou() {
	?>
    <div class="iwp_migration_container iwp-migration-screen-2">
        <div class="iwp_migration_title">
            <img src="<?php echo esc_attr( get_option( 'logo_url' ) ); ?>" style="max-width: 150px;" alt="Logo"/>
            <h1><?php echo esc_attr( get_option( 'title_text' ) ); ?></h1>
        </div>
        <!--<?php echo esc_attr( get_option( 'background_color' ) ); ?>;-->
        <div style="width: 100%;background-color: #F9FAFB;text-align: center;padding: 20px 0;" class="iwp_migration_content">

            <div style="margin-top: 10px; margin-bottom: 25px" id="iwp_migration_content">
                <img src="">
                <div class="iwp-response-message">
					<?php echo wp_kses_post( get_option( 'thankyou_text' ) ); ?>
                </div>
            </div>

            <button style="background-color:<?php echo esc_attr( get_option( 'brand_color' ) ); ?>; color:<?php echo esc_attr( get_option( 'button_text_color' ) ); ?>;" class="iwp_btn_primary iwp-button-close">
                Close
            </button>

        </div>
    </div>
	<?php
}


// Add styles for the button
function iwp_migration_styles() {
	$brand_color       = get_option( 'brand_color' );
	$button_text_color = get_option( 'button_text_color' );

	echo "<style type=\"text/css\">

        button {
            border: none;
        }
        
        .iwp_migration_class {
            background-color: $brand_color !important; 
			color: $button_text_color; 
			
        }

		.iwp_migration_class a:hover {
			/* color: $button_text_color !important;  */
		}

		.iwp_migration_container {
            font-family: Inter;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 20px; /* 142.857% */
            margin-top: 30px !important;
			display: flex;
			margin: 0 auto;
			max-width: 685px;
			flex-direction: column;
			align-items: center;
			border-radius: 16px;
			box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.05), 0px 4px 6px -2px rgba(0, 0, 0, 0.05), 0px 10px 15px -3px rgba(0, 0, 0, 0.10);
		}

        .iwp_btn_primary {
            display: flex;
            padding: 13px 19px;
            justify-content: center;
            border-radius: 6px;
            margin: 0 auto;
            /* shadow/sm */
            box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.05);
            color: #FFF;
            font-family: Inter;
            font-size: 18px;
            font-style: normal;
            font-weight: 500;
            line-height: 20px; /* 111.111% */
            cursor:pointer;
        }

        .iwp_migration_title { 
            display: flex;
            padding: 24px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 12px;
            align-self: stretch;
            background-color: white;
            border-radius: 8px 8px 0px 0px;
        }

        .iwp_migration_content {
            border-radius: 0px 0px 8px 8px;
            background: #F9FAFB;

            /* /shadow/base */
            box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.06), 0px 1px 3px 0px rgba(0, 0, 0, 0.10);
        }

        .iwp_migration_title h1 {
            font-family: Inter;
            font-size: 18px;
            font-style: normal;
            font-weight: 600;
            line-height: normal;
        }

        .iwp_migration_footer {
            font-family: Inter;
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        
        .iwp-migration-screen-2 {
              display: none;
        }

    </style>";
}

add_action( 'wp_head', 'iwp_migration_styles' );
add_action( 'admin_head', 'iwp_migration_styles' );

/**
 * Load JS
 */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'iwp-migration', plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', array( 'jquery' ) );
	wp_localize_script( 'iwp-migration', 'iwp_migration', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), ) );
} );


/**
 * Handle ajax call
 */
add_action( 'wp_ajax_iwp_migration_initiate', function () {

	$iwp_api_key    = get_option( 'iwp_api_key' );
	$iwp_api_domain = 'https://stage.instawp.io/';
	$body_args      = array(
		'url'            => site_url(),
		'email'          => get_option( 'iwp_support_email' ),
		'customer_email' => get_option( 'admin_email' ),
		'subject'        => get_option( 'iwp_email_subject' ),
		'body'           => get_option( 'iwp_email_body' ),
	);
	$headers        = array(
		'Accept'        => 'application/json',
		'Content-Type'  => 'application/json',
		'Authorization' => 'Bearer ' . $iwp_api_key,
	);
	$args           = array(
		'headers'     => $headers,
		'body'        => json_encode( $body_args ),
		'method'      => 'POST',
		'data_format' => 'body'
	);
	$response       = wp_remote_post( $iwp_api_domain . 'api/v2/migrate-request', $args );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( [ 'message' => $response->get_error_message() ] );
	}

	$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! isset( $response_body['status'] ) || $response_body['status'] !== true ) {
		wp_send_json_error( $response_body );
	}

	wp_send_json_success( $response_body );
} );


/**
 * Remove this plugin from plugins list
 */
add_filter( 'all_plugins', function ( $all_plugins ) {
	$plugin_to_remove = 'iwp-migration/iwp-migration.php';

	if ( array_key_exists( $plugin_to_remove, $all_plugins ) ) {
		unset( $all_plugins[ $plugin_to_remove ] );
	}

	return $all_plugins;
} );

