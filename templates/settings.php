<?php

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
