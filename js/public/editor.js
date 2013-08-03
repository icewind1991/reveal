function applyPreview(editor, preview) {
	var previewText = '';

	editor.findRange = function (needle, options) {
		options = options || {};
		options.preventScroll = true;
		return this.find(needle, options)
	};

	editor.getElementRange = function (element) {
		var range = editor.findRange('<' + element, {backwards: true}),
			endRange = editor.findRange('</' + element + '>', {backwards: false});
		range.end = endRange.end;
		return range;
	};

	editor.getElementText = function (element) {
		return this.getSession().getTextRange(this.getElementRange(element));
	};

	var showPreview = function () {
		setTimeout(function () {
			var text = editor.getElementText('section');
			text = convertAllLink(text);
			if (text !== previewText) {
				preview.html(text);
				Reveal.initialize({
					controls    : false,
					rollingLinks: false,
					mouseWheel  : true
				});
				Reveal.slide(0, 0, 0);
				previewText = text;
			}
		}, 10);
	};

	editor.getSession().on('change', showPreview);
	editor.getSession().selection.on('changeCursor', showPreview);
	showPreview();
}

$(document).ready(function () {
	if (typeof FileActions !== 'undefined') {
		FileActions.register('text/reveal', 'Edit', OC.PERMISSION_READ, '', function (filename) {
			showFileEditor($('#dir').val(), filename).then(function () {
				var editor = $('#editor'),
					preview = $('<div/>'),
					slides = $('<div/>'),
					reveal = $('<div/>');
				preview.attr('id', 'revealPreview');
				reveal.addClass('reveal background');
				slides.addClass('slides');
				reveal.append(slides);
				preview.append(reveal);
				editor.parent().append(preview);
				editor.css('width', '50%');

				OC.addStyle('reveal', 'reveal');
				OC.addStyle('reveal', 'theme');
				OC.addStyle('reveal', 'editor');
				$.when(
						OC.addScript('reveal', 'public/lib/head.min'),
						OC.addScript('reveal', 'public/reveal'),
						OC.addScript('reveal', 'public/common'),
						OC.addScript('reveal', 'public/reveal'))
					.then(function () {
						applyPreview(window.aceEditor, slides);
					});
			});
		});
		FileActions.setDefault('text/reveal', 'Edit');
	}

	//overwrite these functions from the text editor so we can hide/show the preview

	var hideFileEditorOriginal = hideFileEditor;
	var reopenEditorOriginal = reopenEditor;
	// Fades out the editor.
	hideFileEditor = function () {
		hideFileEditorOriginal();
		if ($('#editor').attr('data-edited') == 'true') {
			$('#preview').hide();
		} else {
			$('#preview').remove();
		}
	};

	// Reopens the last document
	reopenEditor = function () {
		reopenEditorOriginal();
		$('#preview').show();
	};
});
