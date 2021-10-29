define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, modal) {
        "use strict";
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: '',
            buttons: []
        };

        var popup = modal(options, $('#oney_pedagogique_modal'));
        $(".open-pedagogique_modal").on('click', function () {
            $(".modal-inner-wrap").css("width","60%")
            $("#oney_pedagogique_modal").modal("openModal").on('modalclosed',function () {
                $(".modal-inner-wrap").css("width","auto")
            });
        });
    }
);
