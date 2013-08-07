window.addEventListener("hashchange", onHashChange, false);

function onHashChange() {
	var id = window.location.hash.substr(1);
	if (id) {
		$.get('get/' + id).then(function (content) {
			var body = new PresentationBody(content);
			var title = body.getTitle();
			if (title) {
				document.title = title;
			}
			$('.slides').html(body.getContent());
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
		background = PresentationBody.prototype.convertLink(background);
		if (background.substr(0, 4) === 'http' || background.substr(0, 1) === '/') {
			background = "url('" + background + "')";
		}
		el.css('background', background);
	});

	$('.thumbnail img').each(function (i, el) {
		el = $(el);
		var src = el.attr('src');
		el.attr('src', PresentationBody.prototype.convertLink(src));
	});

	onHashChange();
});
