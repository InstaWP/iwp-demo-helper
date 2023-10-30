<?php
/*
Plugin Name: IWP Migration
Description: A custom plugin to add a button with specific settings.
Version: 1.0
Author: InstaWP
*/

// Admin settings
include(plugin_dir_path(__FILE__) . 'admin_settings.php');

// Adding button to the top bar
function iwp_migration_button($wp_admin_bar) {
	
	$top_bar_text = get_option('top_bar_text');

	// add a button to the top admin bar with color, text and background
	$args = array(
		'id' => 'iwp_migration_btn',
		'title' => esc_attr(get_option('top_bar_text', 'Migrate')),
		'href'  => admin_url('admin.php?page=iwp_migrate_content'),
		'meta' => array(
			'class' => 'menupop iwp_migration_class',
			
		)
	);

	$wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'iwp_migration_button', 999);

function iwp_migration_add_submenu_page() {
    add_submenu_page(
        'iwp_migration_settings',          // parent_slug: It should be the same slug used for your main settings page.
        'IWP Migrate Content',             // page_title
        'Migrate Content',                 // menu_title
        'manage_options',                  // capability: Only users with this capability can access the page.
        'iwp_migrate_content',             // menu_slug
        'iwp_migration_display_content'    // callback function
    );
}
add_action('admin_menu', 'iwp_migration_add_submenu_page');

function iwp_migration_display_content() {
    ?>
    <div class="iwp_migration_container">
		<div class="iwp_migration_title"> 
		    <img src="<?php echo esc_attr(get_option('logo_url')); ?>" style="max-width: 150px;" alt="Logo" />
            <h1><?php echo esc_attr(get_option('title_text')); ?></h1>
        </div>
        <!--<?php echo esc_attr(get_option('background_color')); ?>;-->
        <div style="background-color: #F9FAFB; padding: 20px ; align-items: center; " class="iwp_migration_content">
            
            <div style="margin-top: 10px; margin-bottom: 25px" id="iwp_migration_content"> 
                <?php echo wp_kses_post(get_option('content')); ?> 
            </div>
            
            <button style="background-color:<?php echo esc_attr(get_option('brand_color')); ?>; color:<?php echo esc_attr(get_option('button_text_color')); ?>;" class="iwp_btn_primary">
                <?php echo esc_attr(get_option('cta_button_text')); ?>
            </button>
			<div class="iwp_migration_footer">
                <?php echo wp_kses_post(get_option('footer_text')); ?>
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
add_action('admin_menu', 'iwp_migration_add_submenu_page_thankyou');

function iwp_migration_display_content_thankyou() {
    ?>
    <div class="iwp_migration_container">
		<div class="iwp_migration_title"> 
		    <img src="<?php echo esc_attr(get_option('logo_url')); ?>" style="max-width: 150px;" alt="Logo" />
            <h1><?php echo esc_attr(get_option('title_text')); ?></h1>
        </div>
        <!--<?php echo esc_attr(get_option('background_color')); ?>;-->
        <div style="background-color: #F9FAFB; padding: 20px ; align-items: center; " class="iwp_migration_content">
            
            <div style="margin-top: 10px; margin-bottom: 25px" id="iwp_migration_content"> 
                <img src="">
                <?php echo wp_kses_post(get_option('thankyou_text')); ?> 
            </div>
            
            <button style="background-color:<?php echo esc_attr(get_option('brand_color')); ?>; color:<?php echo esc_attr(get_option('button_text_color')); ?>;" class="iwp_btn_primary">
                Close
            </button>
			
        </div>
    </div>
    <?php
}


// Add styles for the button
function iwp_migration_styles() {
	$brand_color = get_option('brand_color');
	$button_text_color = get_option('button_text_color');

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

    </style>";
}
add_action('wp_head', 'iwp_migration_styles');
add_action('admin_head', 'iwp_migration_styles');
