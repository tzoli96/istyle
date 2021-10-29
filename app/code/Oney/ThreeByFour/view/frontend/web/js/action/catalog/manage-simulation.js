define(
    [
        'jquery'
    ],
    function ($) {
        "use strict";

        $(".open-simulation").on('click', function () {
            $("#container_oney_simulation").css({'display': 'block'});
        });

        $(".close-simulation").on('click', function () {
            $("#container_oney_simulation").css({'display': 'none'});
        });
    }
);
