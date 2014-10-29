(function($) {
  var data = {action: 'rp_spieler_import'};

  $.get(rp_ajax.ajaxurl, data, function(data) {
    $('#loader').remove();
    $('#lade-spieler').append(data);
  });

  return false;
})(jQuery);
