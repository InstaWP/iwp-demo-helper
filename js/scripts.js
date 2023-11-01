(function ($, window, document, plugin_object) {

    jQuery(document).ready(function ($) {
        $('.iwp-color-picker').wpColorPicker();
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
            el_response_message = $('.iwp-response-message');

        el_response_message.removeClass('notice notice-error').html('');

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'iwp_migration_initiate'
            },
            success: function (response) {

                console.log(response);

                if (!response.success) {
                    el_response_message.addClass('notice notice-error').html(response.data.message);
                }

                if (response.success) {
                    el_screen_content.addClass('hidden');
                    el_screen_content_thankyou.removeClass('hidden');
                }
            }
        });
    });

})(jQuery, window, document, iwp_migration);

