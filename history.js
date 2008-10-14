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

function is_history_diff_form_submitable() {
  var f1 = false, f2 = false;
  $('.diff1').each(function() {
    if ($(this).attr('checked')) f1 = $(this);
  });
  $('.diff2').each(function() {
    if ($(this).attr('checked')) f2 = $(this);
  });
  return f1 && f2 && f1.val() != f2.val();
}

function on_submit_change(evt) {
  var btn = $('#history-diff-form input[type=submit]');
  if (is_history_diff_form_submitable()) {
    btn.removeAttr('disabled');
  } else {
    btn.attr('disabled', true);
  }
}

$(document).ready(function() {
  dp.sh.HighlightAll('code');

  var r = $('.diff1');
  $(r.get(0)).hide();
  r = $('.diff2');
  $(r.get(r.length-1)).hide();

  $('#history-diff-form').submit(is_history_diff_form_submitable);
  $('#history-diff-form input[type=radio]').change(on_submit_change);
  $('#history-diff-form input[type=submit]').attr('disabled', true);

  $('a.submit').click(function(evt) {
    evt.preventDefault();
    fetch_code($(this).attr('submitid'));
    return false;
  });
});
