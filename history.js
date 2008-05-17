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
        $('#code').html($(xml).find('code').text());
        $('div.dp-highlighter').remove();
        dp.sh.HighlightAll('code');
      }
    });
};

change_select_display = function () {
    var oldsel = $('#submitid').get(0);
    var i;
    var table = $('<table id="submits_form" class="generaltable"></table>');
    var tbody = $('<tbody />');
    table.append(tbody);
    for (i = 0; i < oldsel.length; i++) {
        tbody.append('<tr><td><a href="#" onclick="fetch_code(' + oldsel.options.item(i).value + ')">' + oldsel.options.item(i).firstChild.data + '</a></td></tr>');
    }
    var oldform = $('#submits_form');
    oldform.after(table);
    oldform.remove();
};

$(document).ready(function() {
  change_select_display();
  dp.sh.HighlightAll('code');
});
