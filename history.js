fetch_code = function(submitid) {
    // Adjust element value of preview and print form
    var preview = $('#print_preview_submit_id');
    if (preview != null) preview.value = submitid;
    var print = $('#print_submit_id');
    if (print != null) print.value = submitid;

    // fetch code
    $.ajax({
      type: 'GET',
      url: 'history_fetch_code.php?submitid=' + submitid,
      success: function(xml) {
        $('#code').text(xml);
        $('div.dp-highlighter').remove();
        dp.sh.HighlightAll('code');
      }
    });
};

$(document).ready(function() {
  dp.sh.HighlightAll('code');

  var r = $('.diff1');
  $(r.get(0)).hide();
  r = $('.diff2');
  $(r.get(r.length-1)).hide();

  $('a.submit').click(function(evt) {
    evt.preventDefault();
    fetch_code($(this).attr('submitid'));
    return false;
  });
});
