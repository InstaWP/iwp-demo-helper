(function ($, window, document, plugin_object) {

    $(document).on('click', '.iwp-button-close', function () {
        let el_screen_1 = $('.iwp-migration-screen-1'),
            el_screen_2 = $('.iwp-migration-screen-2');

        el_screen_2.fadeOut();
        el_screen_1.fadeIn();
    });


    $(document).on('click', '.iwp-btn-main', function () {

        let el_iwp_button = $(this),
            el_screen_1 = $('.iwp-migration-screen-1'),
            el_screen_2 = $('.iwp-migration-screen-2'),
            el_response_message = $('.iwp-response-message');

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'iwp_migration_initiate'
            },
            success: function (response) {

                el_screen_1.fadeOut();
                el_screen_2.fadeIn();

                if (!response.status) {
                    el_response_message.html(response.data.message);
                }
            }
        });
    });

})(jQuery, window, document, iwp_migration);

