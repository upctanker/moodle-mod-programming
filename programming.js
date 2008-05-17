function showasplaintext(iotext) {
    var wnd = window.open('', '_blank', 'width=400, height=300, location=0, resizable=1, menubar=0, scrollbars=1');

    var lines = iotext.find('span.programming-io');

    for (var i = 0; i < lines.length; i++) {
	    wnd.document.write('<span style="font-family: monospace; background-color: #f0f0f0">');
	    wnd.document.write($(lines.get(i)).html());
		wnd.document.write('</span>');
		wnd.document.write('<br />');
	}
    wnd.document.close();
    $('img', wnd.document.body).remove();
}
