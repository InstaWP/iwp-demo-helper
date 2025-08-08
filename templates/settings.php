<?php
// Handle reset settings action
if (isset($_POST['iwp_reset_settings']) && $_POST['iwp_reset_settings'] == '1') {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['_wpnonce'], 'iwp_migration_settings_group-options')) {
        wp_die('Security check failed.');
    }
    
    // Reset all settings to default
    IWP_Migration::reset_all_settings();
    
    // Redirect with success message
    wp_redirect(admin_url('admin.php?page=iwp_demo_helper&tab=advanced&reset=true'));
    exit;
}

// Export is now handled via AJAX
?>

<div class="wrap">
    <h2>InstaWP Demo Helper Settings</h2>
    
    <?php
    // Show reset success message
    if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Settings Reset Successfully!</strong></p>';
        echo '<p>All plugin settings have been reset to their default values. The page will reload to reflect the changes.</p>';
        echo '</div>';
        
        // Add JavaScript to reload after showing message
        echo '<script>
        setTimeout(function() {
            window.location.href = "' . admin_url('admin.php?page=iwp_demo_helper&tab=' . (isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general')) . '";
        }, 3000);
        </script>';
    }
    
    // Show settings updated message  
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    
    $tabs = array(
        'general'  => 'General Settings',
        'branding' => 'Content & Branding',
        'buttons'  => 'Button Configuration',
        'email'    => 'Email & Notifications',
        'advanced' => 'Advanced'
    );
    
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    ?>
    
    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_title): ?>
            <a href="?page=iwp_demo_helper&tab=<?php echo esc_attr($tab_key); ?>" 
               class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_title); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <form method="post" action="options.php">
        <input type="hidden" name="iwp_current_tab" value="<?php echo esc_attr($current_tab); ?>" />
        <?php
        settings_fields('iwp_migration_settings_group');
        
        // Display current tab's settings
        do_settings_sections('iwp_migration_' . $current_tab);
        
        // Add hidden fields for all other tabs to preserve their values
        $all_fields = IWP_Migration::get_setting_fields();
        foreach ($all_fields as $field_id => $field) {
            $field_tab = isset($field['tab']) ? $field['tab'] : 'general';
            if ($field_tab !== $current_tab) {
                $field_value = get_option($field_id, $field['default'] ?? '');
                printf('<input type="hidden" name="%s" value="%s" />', 
                    esc_attr($field_id), 
                    esc_attr($field_value)
                );
            }
        }
        
        submit_button();
        ?>
    </form>
    
    <script>
    // Redirect back to current tab after form submission
    jQuery(document).ready(function($) {
        $('form').on('submit', function() {
            var currentTab = $('input[name="iwp_current_tab"]').val();
            var action = $(this).attr('action');
            var separator = action.indexOf('?') === -1 ? '?' : '&';
            window.setTimeout(function() {
                window.location.href = window.location.pathname + '?page=iwp_demo_helper&tab=' + currentTab + '&settings-updated=true';
            }, 100);
        });
    });
    </script>
</div>
