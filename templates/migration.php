<?php

$is_plugin_disabled   = get_option( 'iwp_disable_plugin' ) === 'yes';
$hide_cta_button      = IWP_Migration::get_option( 'iwp_hide_cta_button', '' );
$logo_url             = IWP_Migration::get_option( 'logo_url' );
$title_text           = IWP_Migration::get_option( 'title_text' );
$content              = IWP_Migration::get_option( 'content' );
$brand_color          = IWP_Migration::get_option( 'brand_color' );
$cta_btn_text_color   = IWP_Migration::get_option( 'cta_btn_text_color' );
$cta_btn_bg_color     = IWP_Migration::get_option( 'cta_btn_bg_color' );
$cta_btn_text         = IWP_Migration::get_option( 'cta_btn_text' );
$footer_text          = IWP_Migration::get_option( 'footer_text' );
$close_btn_text       = IWP_Migration::get_option( 'close_btn_text' );
$close_btn_text_color = IWP_Migration::get_option( 'close_btn_text_color' );
$close_btn_bg_color   = IWP_Migration::get_option( 'close_btn_bg_color' );

?>
<div class="iwp-migration-container">

    <div class="migration-title">
		<?php if ( ! empty( $logo_url ) ) : ?>
            <img src="<?php echo esc_attr( $logo_url ); ?>" style="max-width: 150px;" alt="Logo"/>
		<?php endif; ?>

        <h1><?php echo esc_attr( $title_text ); ?></h1>
    </div>

    <div class="migration-content">
        <div>
			<?php if ( $is_plugin_disabled ) : ?>
                <div class="migration-desc">
                    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <strong>Migration Disabled</strong><br>
                        The migration functionality is currently disabled. Please contact your system administrator for assistance.
                    </div>
                </div>
			<?php else : ?>
                <div class="migration-desc">
					<?php echo wp_kses_post( $content ); ?>
                </div>

                <p class="iwp-response-message"></p>

				<?php if ( get_option( 'iwp_show_domain_redirect' ) === 'yes' ) : ?>
                    <label class="iwp-form-fields">
                        <input type="text" id="iwp-domain-name" placeholder="<?php echo esc_attr( get_option( 'iwp_domain_field_label', __( 'Enter your domain name', 'iwp-migration' ) ) ); ?>">
                    </label>
				<?php endif; ?>

				<?php if ( $hide_cta_button !== 'yes' ) : ?>
                    <button style="background-color:<?php echo esc_attr( $cta_btn_bg_color ); ?>; color:<?php echo esc_attr( $cta_btn_text_color ); ?>;" class="iwp-btn iwp-btn-migrate">
						<?php echo esc_attr( $cta_btn_text ); ?>
                    </button>
				<?php endif; ?>
			<?php endif; ?>

            <div class="iwp_migration_footer">
				<?php echo wp_kses_post( $footer_text ); ?>
            </div>
        </div>
    </div>

    <div class="migration-content hidden migration-content-thankyou">
        <div class="">
            <div class="iwp-thankyou-text">
				<?php echo wp_kses_post( get_option( 'thankyou_text' ) ); ?>
            </div>

            <button style="background-color:<?php echo esc_attr( $close_btn_bg_color ); ?>; color:<?php echo esc_attr( $close_btn_text_color ); ?>;" class="iwp-btn iwp-migrate-close"><?= $close_btn_text; ?></button>
        </div>
    </div>

</div>
