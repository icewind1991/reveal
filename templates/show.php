{{ script('public/reveal') }}
{{ script('public/show') }}
{{ script('public/lib/head.min') }}
{{ style('reveal') }}
{{ style('theme') }}

<div id="reveal" class="reveal background">
	<div class="slides">
		{{ content|raw }}
	</div>
</div>
