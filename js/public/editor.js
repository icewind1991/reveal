OCA.Reveal = {};

OCA.Reveal.overWriteEditor = function () {
	if (window.hideFileEditor) {
		var hideFileEditorOriginal = window.hideFileEditor;
		var reopenEditorOriginal = window.reopenEditor;
	}
	// Fades out the editor.
	window.hideFileEditor = function () {
		hideFileEditorOriginal();
		if ($('#editor').attr('data-edited') === 'true') {
			$('#reveal_preview').hide();
		} else {
			$('#reveal_preview').remove();
		}
	};

	// Reopens the last document
	window.reopenEditor = function () {
		reopenEditorOriginal();
		$('#md_preview').show();
	};
};

OCA.Reveal.mathJaxLoaded = false;
OCA.Reveal.scriptsLoaded = false;
OCA.Reveal.stylesLoaded = false;

OCA.Reveal.Editor = function (editor, head, dir) {
	this.dir = dir;
	this.previewText = '';
	this.ace = null;
	this.aceSession = null;
	this.editor = editor;
	this.head = head;
	this.preview = $('<div/>');
	this.wrapper = $('<div/>');
	this.slides = $('<div/>');
	this.reveal = $('<div/>');
	this.activeStyle = null;
	this.preLoadedImages = [];
};

OCA.Reveal.Editor.prototype.init = function (editor, editorSession) {
	this.ace = editor;
	this.aceSession = editorSession;
	this.loadMathJax();
	this.loadStyles();
	this.loadScripts().then(function () {
		this.preview.attr('id', 'revealPreview');
		this.reveal.addClass('reveal background');
		this.slides.addClass('slides');
		this.reveal.append(this.slides);
		this.preview.append(this.reveal);

		this.wrapper.attr('id', 'preview_wrapper');
		this.wrapper.append(this.preview);
		this.editor.parent().append(this.wrapper);
		this.editor.css('width', '50%');
		this.ace.resize();
		var onChange = _.debounce(this._onChange.bind(this), 100);

		editorSession.on('change', onChange);
		editorSession.selection.on('changeCursor', onChange);
		onChange();
	}.bind(this));
};

OCA.Reveal.Editor.prototype._onChange = function () {
	var text = this.aceSession.getValue();
	this.showPreview(text);
};

OCA.Reveal.Editor.loadStyle = function (path) {
	style = $('<link rel="stylesheet" type="text/css" href="' + path + '"/>');
	$(this.head).append(style);
};

OCA.Reveal.Editor.prototype.loadStyles = function () {
	if (OCA.Reveal.stylesLoaded) {
		return;
	}

	OC.addStyle('reveal', 'reveal');
	OC.addStyle('reveal', 'editor');
};

OCA.Reveal.Editor.prototype.loadScripts = function () {
	if (!OCA.Files_Markdown.scriptsLoaded) {
		OCA.Files_Markdown.scriptsLoaded = $.when(
				OC.addScript('reveal', 'public/lib/head.min'),
				OC.addScript('reveal', 'public/reveal'),
				OC.addScript('reveal', 'public/common'),
				OC.addScript('reveal', 'public/reveal'))
			.then(function () {

			});
	}
	return OCA.Files_Markdown.scriptsLoaded;
};

OCA.Reveal.Editor.prototype.loadMathJax = function () {
	if (OCA.Files_Markdown.mathJaxLoaded) {
		return;
	}
	OCA.Files_Markdown.mathJaxLoaded = true;
	var script = document.createElement("script");
	script.type = "text/x-mathjax-config";
	script[(window.opera ? "innerHTML" : "text")] =
		"MathJax.Hub.Config({\n" +
			"  tex2jax: { inlineMath: [['$','$'], ['\\\\(','\\\\)']] }\n" +
			"});";
	this.head.appendChild(script);

	var path = OC.filePath('files_markdown', 'js', 'mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML');

	//insert using native dom to prevent jquery from removing the script tag
	script = document.createElement("script");
	script.src = path;
	this.head.appendChild(script);
};

OCA.Reveal.Editor.prototype.findRange = function (needle, options) {
	options = options || {};
	options.preventScroll = true;
	return this.ace.find(needle, options);
};

OCA.Reveal.Editor.prototype.getElementRange = function (element) {
	var range = this.findRange('<' + element, {backwards: true}),
		endRange = this.findRange('</' + element + '>', {backwards: false});
	range.end = endRange.end;
	return range;
};

OCA.Reveal.Editor.prototype.getElementText = function (element) {
	return this.aceSession.getTextRange(this.getElementRange(element));
};

OCA.Reveal.Editor.prototype.setStyle = function (style) {
	if (!style || (this.activeStyle && this.activeStyle.attr('href') != style)) {
		return;
	}
	if (this.activeStyle) {
		this.activeStyle.remove();
	}
	this.activeStyle = $('<link/>');
	this.activeStyle.attr({
		'rel': 'stylesheet',
		'href': style
	});
	$(this.head).append(this.activeStyle)
};

OCA.Reveal.Editor.prototype.makeLink = function (link) {
	if (!link) {
		return link;
	}
	if (link.substr(0, 7) === 'http://' || link.substr(0, 8) === 'https://' || link.substr(0, 3) === '://') {
		return link;
	} else {
		if (link.substr(0, 1) !== '/') {
			link = this.dir + '/' + link;
		}
		return OC.generateUrl('apps/files/ajax/download.php?dir={dir}&files={file}', {
			dir: OC.dirname(link),
			file: OC.basename(link)
		});
	}
};

OCA.Reveal.Editor.prototype.preloadImage = function (src) {
	if (this.preLoadedImages.indexOf(src) === -1) {
		this.preLoadedImages.push(src);
		(new Image()).src = src;
	}
};

OCA.Reveal.Editor.prototype.preloadImages = function (text) {
	var html = $(text);

	var images = $('img', html);
	images.each(function (i, embed) {
		this.preloadImage(this.makeLink($(embed).attr('data-src')));
	}.bind(this));

	var backgrounds = $('[data-background]', html);
	backgrounds.each(function (i, embed) {
		this.preloadImage(this.makeLink($(embed).attr('data-background')));
	}.bind(this));
};

OCA.Reveal.Editor.prototype.showPreview = function (fullText) {
	var linkText = fullText.match(/<link[^>]*>/);
	var styles = [];
	if (linkText) {
		for (var i = 0; i < linkText.length; i++) {
			var link = $(linkText[i]);
			if (link && link.attr('rel') == 'stylesheet' && link.attr('href')) {
				styles.push(this.makeLink(link.attr('href')));
			}
		}
	}
	//make sure we can load the html without loading images
	fullText = fullText.replace(/ src ?/g, ' data-src');
	if (styles.length === 0) {
		styles.push(OC.filePath('reveal', 'css', 'theme/default.css'));
	}
	for (i = 0; i < styles.length; i++) {
		this.setStyle(styles[i]);
	}
	this.preloadImages(fullText);

	var text = this.getElementText('section');
	var body = new PresentationBody(text);
	text = body.getContent();
	if (text !== this.previewText && text) {
		text = text.replace(/ src ?=/g, ' data-src=');
		var html = $(text);

		$('[data-src]', html).each(function (i, embed) {
			embed = $(embed);
			embed.attr('src', this.makeLink(embed.attr('data-src')));
		}.bind(this));
		if (html.attr('data-background')) {
			html.attr('data-background', this.makeLink(html.attr('data-background')));
		}

		this.slides.html(html);

		Reveal.initialize({
			controls: false,
			rollingLinks: false,
			mouseWheel: true
		});
		Reveal.slide(0, 0, 0);
		this.previewText = text;
		if (window.MathJax) {
			MathJax.Hub.Queue(["Typeset", MathJax.Hub, this.preview[0]]);
		}
	}
};

$(document).ready(function () {
	if (OCA.Files) {
		OCA.Files.fileActions.register('text/reveal', 'Edit', OC.PERMISSION_READ, '', function (filename, context) {
			window.showFileEditor(context.dir, filename).then(function () {
				var editor = new OCA.Reveal.Editor($('#editor'), $('head')[0], context.dir);
				editor.init(window.aceEditor, window.aceEditor.getSession());
			});
		});
		OCA.Files.fileActions.setDefault('text/reveal', 'Edit');

		OCA.Reveal.overWriteEditor();
	}
});
