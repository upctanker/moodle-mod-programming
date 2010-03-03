$(document).ready(function() {
  $('a.showasplaintext').click(function() {
    window.open(this.href, '_blank', 'width=400, height=300, location=0, resizable=1, menubar=0, scrollbars=1');
    return false;
  });
});
