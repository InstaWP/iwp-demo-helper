<?php

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
            <div class="migration-desc">
				<?php echo wp_kses_post( $content ); ?>
            </div>

            <p class="iwp-response-message"></p>


			<?php if ( get_option( 'iwp_show_domain_field' ) === 'yes' ) : ?>
                <label class="iwp-form-fields">
                    <input type="text" id="iwp-domain-name" placeholder="<?php echo esc_attr( get_option( 'iwp_domain_field_label', __( 'Enter your domain name', 'iwp-migration' ) ) ); ?>">
                </label>
			<?php endif; ?>

            <button style="background-color:<?php echo esc_attr( $cta_btn_bg_color ); ?>; color:<?php echo esc_attr( $cta_btn_text_color ); ?>;" class="iwp-btn iwp-btn-migrate">
				<?php echo esc_attr( $cta_btn_text ); ?>
            </button>

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
