define([
    "jquery"
], function($) {
    $(document).ready(function() {
        var now = Math.floor(new Date().getTime() / 1000);

        $('.oander-wonder-widget-timed').each(function () {
            var $this = $(this);

            if (now >= $this.data('visibleFrom') && now <= $this.data('visibleTo')) {
                $this.show();
            }
        })
    });
});
