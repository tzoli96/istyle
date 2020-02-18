require([
    "jquery"
], function ($) {

    const options = {
        url: '/istylecustomization/customer/header',
        headerId: '#header-account-toggle',
        iconClass: ".icon-user",
        monogramClass: ".monogram-user"
    };

   // $(document).ready(function () {
     //   if ($(options.headerId).length) {
            $.ajax({
                url: options.url,
                type: "GET"
            }).done(function(response){
                if (response && $(options.headerId+" "+options.iconClass).is(":visible")) {
                    $(options.headerId+" "+options.iconClass).hide();
                    if ($(options.headerId+" "+options.monogramClass).length) {
                        $(options.headerId+" "+options.monogramClass).show();
                    } else {
                        $(options.headerId).html("<span class='"+options.monogramClass+"'>"+response+"</span>");
                    }

                } else {
                    $(options.headerId+" "+options.monogramClass).hide();
                    $(options.headerId+" "+options.iconClass).show();
                }
            });
      //  }
    //});

});