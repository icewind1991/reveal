window.addEventListener("hashchange", onHashChange, false);

function onHashChange() {
	var id = window.location.hash.substr(1);
	if (id) {
		$.get('get/' + id).then(function (content) {
			content = convertAllLink(content);
			console.log(content);
			$('.slides').html(content);
			$('.view').show();
			initialize();
			setTimeout(function () {
				console.log('0');
				Reveal.slide(0, 0, 0);
			}, 10);
		});
	} else {
		$('.slides').html('<section/>');
		$('.view').hide();
	}
}

$(document).ready(function () {
	$('section[data-background]').each(function (i, el) {
		el = $(el);
		var background = el.data('background');
		background = convertLink(background);
		if (background.substr(0, 4) === 'http' || background.substr(0, 1) === '/') {
			background = "url('" + background + "')";
		}
		console.log(background);
		el.css('background', background);
	});

	onHashChange();
});
