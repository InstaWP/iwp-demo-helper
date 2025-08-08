(function ($, window, document, plugin_object) {

    jQuery(document).ready(function ($) {
        $('.iwp-color-picker').wpColorPicker();
        
        // Store current tab in localStorage for persistence
        if (window.location.href.indexOf('page=iwp_demo_helper') > -1) {
            const urlParams = new URLSearchParams(window.location.search);
            const currentTab = urlParams.get('tab') || 'general';
            localStorage.setItem('iwp_migration_current_tab', currentTab);
        }
    });

    $(document).on('change', 'input.iwp-checkbox-with-field', function () {
        var linkedFieldName = $(this).data('linked-field');
        var linkedField = $('input[name="' + linkedFieldName + '"]');
        
        if ($(this).is(':checked')) {
            linkedField.show();
        } else {
            linkedField.hide();
        }
    });

    $(document).on('change', 'input.iwp-checkbox-with-multiple-fields', function () {
        var linkedFieldsContainer = $(this).closest('.iwp-checkbox-with-multiple-fields-container').find('.iwp-multiple-linked-fields');
        
        if ($(this).is(':checked')) {
            linkedFieldsContainer.show();
        } else {
            linkedFieldsContainer.hide();
        }
    });

    $(document).on('click', '.iwp-migrate-close', function () {
        let el_screen_content = $('.migration-content'),
            el_screen_content_thankyou = $('.migration-content-thankyou'),
            el_response_message = $('.iwp-response-message');

        el_response_message.removeClass('notice notice-error').html('');
        el_screen_content.removeClass('hidden');
        el_screen_content_thankyou.addClass('hidden');
    });


    $(document).on('click', '.iwp-btn-migrate', function () {

        let el_screen_content = $('.migration-content'),
            el_screen_content_thankyou = $('.migration-content-thankyou'),
            el_input_field = $('input#iwp-domain-name'),
            el_response_message = $('.iwp-response-message');

        el_response_message.removeClass('notice notice-error').html('');
        $(this).addClass('disabled');

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'iwp_migration_initiate',
                'domain_name': el_input_field.val(),
            },
            success: function (response) {

                if (!response.success) {
                    // Enhanced error display with more detailed messages
                    var errorMessage = response.data.message || 'An unknown error occurred';
                    var errorCode = response.data.code;
                    
                    // Add error code to display if available
                    if (errorCode) {
                        errorMessage += ' (Error Code: ' + errorCode + ')';
                    }
                    
                    el_response_message.addClass('notice notice-error').html(errorMessage);
                    $(this).removeClass('disabled');
                    return;
                }

                if (response.success) {
                    // Handle the new "Open Link on Button Click" action (overrides all others)
                    if (response.data.open_link_action && response.data.redirect_url) {
                        if (response.data.open_new_tab) {
                            window.open(response.data.redirect_url, '_blank');
                        } else {
                            window.location.href = response.data.redirect_url;
                        }
                        return;
                    }

                    var actions = response.data.actions || {};

                    // Handle redirect if Show Domain Choice & Redirect is enabled
                    if (actions.show_domain_redirect && response.data.redirection_url && response.data.redirection_url !== '') {
                        window.location.href = response.data.redirection_url;
                        return;
                    }

                    // Check if there's an API warning message (like 404) to show before proceeding
                    if (response.data.api_warning && response.data.api_status_code === 404) {
                        el_response_message.addClass('notice notice-warning').html(
                            '<strong>Notice:</strong> ' + response.data.message
                        );
                        
                        // Show the message for 3 seconds before proceeding to thank you screen
                        setTimeout(function() {
                            el_response_message.removeClass('notice notice-warning').html('');
                            el_screen_content.addClass('hidden');
                            el_screen_content_thankyou.removeClass('hidden');
                        }, 3000);
                    } else {
                        // If no warning, show thank you screen immediately
                        el_screen_content.addClass('hidden');
                        el_screen_content_thankyou.removeClass('hidden');
                    }
                }
            },
            error: function (xhr, status, error) {
                // Handle AJAX errors (network issues, etc.)
                el_response_message.addClass('notice notice-error').html(
                    'Connection error: Unable to reach server. Please check your internet connection and try again.'
                );
                $(this).removeClass('disabled');
            }
        });
    });

    // Code for appending src_demo_url
    function append_src_demo_url() {
        if ($('body.admin_page_iwp_demo_landing .migration-content .migration-desc a').length > 0 && plugin_object.enable_src_demo_url === 'yes') {
            var demoUrl = plugin_object.demo_site_url;
            if (demoUrl) { // Ensure demoUrl is not empty
                $('.migration-desc a').each(function () {
                    var link = $(this);
                    var currentHref = link.attr('href');
                    if (currentHref && currentHref !== '#' && !currentHref.startsWith('javascript:')) { // Basic check to avoid modifying empty or JS links
                        var paramSeparator = currentHref.indexOf('?') === -1 ? '?' : '&';
                        var newHref = currentHref + paramSeparator + 'src_demo_url=' + encodeURIComponent(demoUrl);
                        link.attr('href', newHref);
                    }
                });
            }
        }
    }

    setTimeout(function () {
        append_src_demo_url();
    }, 500);
})(jQuery, window, document, iwp_migration);

