<?php

$logo_url          = IWP_Migration::get_option( 'logo_url' );
$title_text        = IWP_Migration::get_option( 'title_text' );
$content           = IWP_Migration::get_option( 'content' );
$brand_color       = IWP_Migration::get_option( 'brand_color' );
$button_text_color = IWP_Migration::get_option( 'button_text_color' );
$cta_button_text   = IWP_Migration::get_option( 'cta_button_text' );
$footer_text       = IWP_Migration::get_option( 'footer_text' );

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

            <button style="background-color:<?php echo esc_attr( $brand_color ); ?>; color:<?php echo esc_attr( $button_text_color ); ?>;" class="iwp-btn iwp-btn-migrate">
				<?php echo esc_attr( $cta_button_text ); ?>
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

            <button style="background-color:<?php echo esc_attr( $brand_color ); ?>; color:<?php echo esc_attr( $button_text_color ); ?>;" class="iwp-btn iwp-migrate-close">Close</button>
        </div>
    </div>

</div>