<?php


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
	}
}

?>

<div class="wrap">
    <h2>IWP Migration Settings</h2>
    <form method="post" action="options.php">
		<?php
		settings_fields( 'iwp_migration_settings_group' );
		do_settings_sections( 'iwp_migration' );
		submit_button();
		?>
    </form>
</div>
