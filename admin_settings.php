<?php

// Admin menu
function iwp_migration_admin_menu() {
	add_menu_page( 'IWP Migration Settings', 'IWP Migration', 'manage_options', 'iwp_migration', 'iwp_migration_settings_page', 'dashicons-migrate', 200 );
}

add_action( 'admin_menu', 'iwp_migration_admin_menu' );

// Display settings page
function iwp_migration_settings_page() {
	?>
    <div class="wrap">
        <h2>IWP Migration Settings</h2>
        <form method="post" action="options.php">
			<?php settings_fields( 'iwp_migration_settings_group' ); ?>
			<?php do_settings_sections( 'iwp_migration' ); ?>
			<?php submit_button(); ?>
        </form>
    </div>
	<?php
}

// Register settings
function iwp_migration_register_settings() {
	register_setting( 'iwp_migration_settings_group', 'iwp_api_key' );
	register_setting( 'iwp_migration_settings_group', 'iwp_support_email' );
	register_setting( 'iwp_migration_settings_group', 'logo_url' );
	register_setting( 'iwp_migration_settings_group', 'content' );
	register_setting( 'iwp_migration_settings_group', 'title_text' );
	register_setting( 'iwp_migration_settings_group', 'footer_text' );
	register_setting( 'iwp_migration_settings_group', 'cta_button_text' );
	register_setting( 'iwp_migration_settings_group', 'brand_color' );
	register_setting( 'iwp_migration_settings_group', 'button_text_color' );
	register_setting( 'iwp_migration_settings_group', 'background_color' );
	register_setting( 'iwp_migration_settings_group', 'top_bar_text' );
	register_setting( 'iwp_migration_settings_group', 'thankyou_text' );
	register_setting( 'iwp_migration_settings_group', 'iwp_email_subject' );
	register_setting( 'iwp_migration_settings_group', 'iwp_email_body' );
	register_setting( 'iwp_migration_settings_group', 'iwp_hide_migration_plugin' );

	add_settings_section( 'iwp_migration_main_section', 'Main Settings', null, 'iwp_migration' );

	add_settings_field( 'iwp_api_key', 'API Key', 'iwp_migration_iwp_api_key_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'iwp_support_email', 'Support Email', 'iwp_migration_iwp_support_email_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'logo-url', 'Logo URL', 'iwp_migration_logo_url_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'content', 'Content', 'iwp_migration_content_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'title', 'Title', 'iwp_migration_title_text_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'footer-text', 'Footer Text', 'iwp_migration_footer_text_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'cta-button-text', 'CTA Button Text', 'iwp_migration_cta_button_text_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'brand-color', 'Brand Color', 'iwp_migration_brand_color_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'button-text-color', 'Button Text Color', 'iwp_migration_button_text_color_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'background-color', 'Background Color', 'iwp_migration_background_color_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'top-bar-text', 'Top Bar Text', 'iwp_migration_top_bar_text_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'thankyou-text', 'Thank You Text', 'iwp_migration_thankyou_text_callback', 'iwp_migration', 'iwp_migration_main_section' );

	add_settings_field( 'iwp_email_subject', 'Email Subject', 'iwp_migration_iwp_email_subject_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'iwp_email_body', 'Email Body', 'iwp_migration_iwp_email_body_callback', 'iwp_migration', 'iwp_migration_main_section' );
	add_settings_field( 'iwp_hide_migration_plugin', 'Hide Migration Plugin', 'iwp_migration_iwp_hide_migration_plugin_callback', 'iwp_migration', 'iwp_migration_main_section' );
}

add_action( 'admin_init', 'iwp_migration_register_settings' );

function iwp_migration_iwp_hide_migration_plugin_callback() {
	$iwp_hide_migration_plugin = get_option( 'iwp_hide_migration_plugin' ) === 'yes' ? 'checked' : '';

	echo '<input id="iwp_hide_migration_plugin" type="checkbox" name="iwp_hide_migration_plugin" ' . $iwp_hide_migration_plugin . ' value="yes" />';
	echo '<label for="iwp_hide_migration_plugin">Hide This Plugin</label>';
}

function iwp_migration_iwp_email_body_callback() {
	echo '<textarea rows="10" cols="80" type="email" placeholder="This site - {{site_url}} of {{customer_email}} migrated." name="iwp_email_body">' . esc_attr( get_option( 'iwp_email_body' ) ) . '</textarea>';
}

function iwp_migration_iwp_email_subject_callback() {
	echo '<input type="text" style="width: 380px;" placeholder="Migration started for {{site_url}}" name="iwp_email_subject" value="' . esc_attr( get_option( 'iwp_email_subject' ) ) . '" />';
}

function iwp_migration_iwp_support_email_callback() {
	echo '<input type="email" style="width: 380px;" placeholder="support@mysite.com" name="iwp_support_email" value="' . esc_attr( get_option( 'iwp_support_email' ) ) . '" />';
}

function iwp_migration_iwp_api_key_callback() {
	echo '<input type="text" style="width: 380px;" placeholder="znrVYIay67kWo56SXNoqe5ScJU9bBBscxOzh15ucN" name="iwp_api_key" value="' . esc_attr( get_option( 'iwp_api_key' ) ) . '" />';
}

function iwp_migration_logo_url_callback() {
	echo '<input type="text" style="width: 380px;" name="logo_url" value="' . esc_attr( get_option( 'logo_url' ) ) . '" />';
}

function iwp_migration_content_callback() {
	$content = get_option( 'content', '' );
	wp_editor(
		$content,                    // Initial content
		'content',                   // Editor's textarea id. Should be unique!
		array(
			'wpautop'       => true,
			'media_buttons' => true,
			'textarea_name' => 'content',
			'textarea_rows' => 10,
			'teeny'         => false
		)
	);
	//echo '<textarea name="content">' . esc_textarea(get_option('content')) . '</textarea>';
}

function iwp_migration_title_text_callback() {
	echo '<input type="text" style="width: 380px;" name="title_text" value="' . esc_attr( get_option( 'title_text' ) ) . '" />';
}


function iwp_migration_footer_text_callback() {
	$footer_text = get_option( 'footer_text', '' );
	wp_editor(
		$footer_text,                    // Initial content
		'footer_text',                   // Editor's textarea id. Should be unique!
		array(
			'wpautop'       => true,
			'media_buttons' => true,
			'textarea_name' => 'footer_text',
			'textarea_rows' => 10,
			'teeny'         => false
		)
	);
	// echo '<input type="text" name="footer_text" value="' . esc_attr(get_option('footer_text')) . '" />';
}

function iwp_migration_thankyou_text_callback() {
	$thankyou_text = get_option( 'thankyou_text', '' );
	wp_editor(
		$thankyou_text,                    // Initial content
		'thankyou_text',                   // Editor's textarea id. Should be unique!
		array(
			'wpautop'       => true,
			'media_buttons' => true,
			'textarea_name' => 'thankyou_text',
			'textarea_rows' => 10,
			'teeny'         => false
		)
	);
	// echo '<input type="text" name="footer_text" value="' . esc_attr(get_option('footer_text')) . '" />';
}

function iwp_migration_cta_button_text_callback() {
	echo '<input type="text" style="width: 380px;" name="cta_button_text" value="' . esc_attr( get_option( 'cta_button_text' ) ) . '" />';
}

function iwp_migration_brand_color_callback() {
	echo '<input type="color" name="brand_color" value="' . esc_attr( get_option( 'brand_color' ) ) . '" />';
}

function iwp_migration_button_text_color_callback() {
	echo '<input type="color" name="button_text_color" value="' . esc_attr( get_option( 'button_text_color' ) ) . '" />';
}

function iwp_migration_background_color_callback() {
	echo '<input type="color" name="background_color" value="' . esc_attr( get_option( 'background_color' ) ) . '" />';
}

function iwp_migration_top_bar_text_callback() {
	echo '<input type="text" style="width: 380px;" name="top_bar_text" value="' . esc_attr( get_option( 'top_bar_text' ) ) . '" />';
}
