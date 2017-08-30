define([
  'jquery'
], function ($) {
  'use strict';

  $(function() {
    $("input:file").change(function (){
      var fileName = $(this).val();
      $(".filename").html(fileName);
    });
  });

});
