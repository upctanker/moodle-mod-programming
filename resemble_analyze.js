/**
 * In this function, the form is changed to use javascript analyze 
 * instead of the server side analyze.
 */
function change_form() {
    $('#begin_analyze').click(resemble_analyze);
}

function resemble_analyze(evt) {

    $('#resemble_analyze_form').append('<div id="state"/>');

    return fetch_index();
}

var results1, results2, record;
function fetch_index() {

    var parse_lines = function(html) {
        $('state').append('<p>begin to parse record: ' + record.index + '</p>');

        lines = html.split('\n');
        var s = 0, c = 0, i;
        var ret = '';
        
        for (i = 0; i < lines.length; i++) {
            var m;
            var line = lines[i];
            switch (s) {
            case 0:
                if (line.match(/^<TR><TD><A[^>]*>(\d+-\d+)<\/A>/)) {
                    s = 1;
                    if (ret != '') ret += ';';
                    ret += m[1] + ',';
                }
                break;
            case 1:
                if (line.match(/^<TD><A[^>]*>(\d+-\d+)<\/A>/)) {
                    s = 0;
                    ret += m[1];
                }
                break;
            }
        }

        return ret;
    };

    var fetch_next_topfile = function() {

        if (results1.length > 0) {
            record = results1.shift();
            $('state').append('<p>begin to fetch record: ' + record.index + '</p>');
            var filename = '/match' + record.index + '-top.html';
            $.ajax({
                type : 'GET',
                url : $('input[@name=url]').get(0).value + filename,
                success : function(html) {
                    record.matchedlines = parse_lines(html);
                    results2.push(record);
                    fetch_next_topfile();
                }
            });
        } else {
            // fetched all the result
            window.alert('finished');
        }
    };

    var parse_index = function(html) {

        var lines = html.split('\n');
        var i, s = 0;
        var lowest = $('input[name=lowest]').get(0).value;

        $('#state').append('<p>index file fetched</p>');
        results1 = new Array();
        for (i = 0; i < lines.length; i++) {
            var m;
            var line = lines[i];
            switch(s) {
            case 0:
                if (line.match(/<TABLE>/)) {
                    s = 1;
                    c = 0;
                }
                break;
            case 1:
                if (m = line.match(/^<TR><TD><A HREF="([^"]*)">(\d*)?-(\d*)\.\w* \((\d*)%\)<\/A>/)) {
                    record = new Object();
                    record.submitid1 = m[3];
                    record.percent1 = m[4];
                    s = 2;
                }
                break;
            case 2:
                if (m = line.match(/<TD><A HREF="([^"]*)">(\d*)?-(\d*)\.\w* \((\d*)%\)<\/A>/)) {
                    record.submitid2 = m[3];
                    record.percent2 = m[4];
                    s = 3;
                }
                break;
            case 3:
                if (m = line.match(/<TD ALIGN=right>(\d+)/)) {
                    record.matchedcount = m[1];
    				if (record.percent1 > lowest || record.percent2 > lowest) {
                        record.index = i;
                        results1.push(record);
				    }
                    s = 1;
                }
            break;
            }
        }
        $('state').append('<p>index file parsed, ' + results1.length + ' records found.</p>');

        results2 = new Array();
        fetch_next_topfile();
    };

    var url = $('input[@name=url]').get(0).value;
    window.alert(url);
    $.ajax({
        type: 'GET',
        url: url,
        dataType: 'html',
        success: parse_index
    });

    return false;
}

$(document).ready(change_form);
