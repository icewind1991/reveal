function initialize() {
	Reveal.initialize({
		// Display controls in the bottom right corner
		controls    : true,

		// Display a presentation progress bar
		progress    : true,

		// Push each slide change to the browser history
		history     : false,

		// Enable keyboard shortcuts for navigation
		keyboard    : true,

		// Enable the slide overview mode
		overview    : true,

		// Loop the presentation
		loop        : false,

		// Number of milliseconds between automatically proceeding to the
		// next slide, disabled when set to 0
		autoSlide   : 0,

		// Enable slide navigation via mouse wheel
		mouseWheel  : true,

		// Apply a 3D roll to links on hover
		rollingLinks: false,

		// Transition style
		// default/cube/page/concave/linear(2d)
		transition  : "linear",

		dependencies: [
			{ src: OC.linkTo('reveal', 'js/public/lib/classList.js'), condition: function () {
				return !document.body.classList;
			} },
			{ src: OC.linkTo('reveal', 'js/public/plugin/markdown/marked.js'), condition: function () {
				return !!document.querySelector('[data-markdown]');
			} },
			{ src: OC.linkTo('reveal', 'js/public/plugin/markdown/markdown.js'), condition: function () {
				return !!document.querySelector('[data-markdown]');
			} },
			{ src: OC.linkTo('reveal', 'js/public/plugin/highlight/highlight.js'), async: true, callback: function () {
				hljs.initHighlightingOnLoad();
			} },
			{ src: OC.linkTo('reveal', 'js/public/plugin/zoom-js/zoom.js'), async: true, condition: function () {
				return !!document.body.classList;
			} },
			{ src: OC.linkTo('reveal', 'js/public/plugin/notes/notes.js'), async: true, condition: function () {
				return !!document.body.classList;
			} }
		]
	});
}

function PresentationBody(content) {
	this.rawContent = content;
}

PresentationBody.prototype.convertLink = function (link) {
	if (link.substr(0, 1) === '/' && (link.indexOf('jpg') || link.indexOf('jpeg') || link.indexOf('bmp') || link.indexOf('bmp'))) {
		link = OC.linkTo('', 'index.php') + '/apps/reveal/image/?path=' + encodeURIComponent(link);
	}
	return link;
};

PresentationBody.prototype.getContent = function () {
	var that = this;
	return this.rawContent.replace(/(\"|\')(\/[^\'\"]+)(\"|\')/g, function (match, quote, link) {
		return quote + that.convertLink(link) + quote;
	});
};

PresentationBody.prototype.getTitle = function () {
	var match = this.rawContent.match(/\<title\>(.*)\<\/title\>/);
	if (match) {
		return match[1];
	} else {
		return null;
	}
};
