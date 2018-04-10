$(document).ready(function () {
	$('ul').prev('a').addClass('highlight');

	$('.list > li a').dblclick(function () {
		$(this).parent().children('ul').toggle();
	});

	$('body > .divTable').height($(window).height());
	console.log('Table Height: ' + $('body > .divTable').height());	
	console.log('Viewport - Header Height: ' + ($(window).height() - $('#header').height()));
	console.log('Viewport Height: ' + $(window).height());
});

function loadPage(url) {
	$.get(url, function (data) {
		$("#content").html(data);
	});
}