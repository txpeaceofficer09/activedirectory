$(document).ready(function () {
	$('ul').prev('a').addClass('highlight');

	$('.list > li a').dblclick(function () {
		$(this).parent().children('ul').toggle();
	});

	var nH = $(window).height() - $('#header').height();
	var cW = $(window).width() - $('#nav').outerWidth();

	$('body > .divTable').height($(window).height());
	$('#nav').height(nH);
	$('#content').height(nH).width(cW);
});

function loadPage(url) {
	$.get(url, function (data) {
		$("#content").html(data);
	});
}

$(window).onresize(function () {
	var nH = $(window).height() - $('#header').height();
	var cW = $(window).width() - $('#nav').width();

	$('body > .divTable').height($(window).height());
	$('#nav').height(nH);
	$('#content').height(nH).width(cW);
});