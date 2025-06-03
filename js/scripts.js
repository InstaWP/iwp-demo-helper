(function ($, window, document, plugin_object) {

    jQuery(document).ready(function ($) {
        $('.iwp-color-picker').wpColorPicker();
    });

    $(document).on('change', 'input[name="iwp_disable_email"]', function () {
        $('input[name="iwp_support_email"]').prop('disabled', $(this).is(':checked'));
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
                    el_response_message.addClass('notice notice-error').html(response.data.message);
                    $(this).removeClass('disabled');
                }

                if (response.success) {

                    if (response.data.redirection_url && response.data.redirection_url !== '') {
                        window.location.href = response.data.redirection_url;

                        return;
                    }

                    el_screen_content.addClass('hidden');
                    el_screen_content_thankyou.removeClass('hidden');
                }
            }
        });
    });

    // Code for appending src_demo_url
    function append_src_demo_url() {
        if ($('body.admin_page_iwp_migrate_content .migration-content .migration-desc a').length > 0 && plugin_object.enable_src_demo_url === 'yes') {
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

