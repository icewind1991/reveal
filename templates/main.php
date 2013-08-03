{{ script('public/reveal') }}
{{ script('public/show') }}
{{ script('public/lib/head.min') }}
{{ style('reveal') }}
{{ style('theme') }}
{{ style('main') }}

<div id="app">
	{% for presentation in presentations %}
	{{ presentation.name }}
	<div class="thumbnail">
		<div class="reveal background">
			{{ presentation.preview|raw }}
		</div>
	</div>
	{% endfor %}
</div>



