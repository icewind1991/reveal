{{ script('public/reveal') }}
{{ script('public/common') }}
{{ script('public/lib/head.min') }}
{{ script('public/main') }}
{{ style('reveal') }}
{{ style('theme') }}
{{ style('main') }}

<div id="app">
	<div id="reveal" class="view reveal background">
		<div class="slides">
			<section></section>
		</div>
	</div>
	{% for presentation in presentations %}
	<a class="presentation" data-id="{{ presentation.id }}" href="#{{ presentation.id }}">
		<p>{{ presentation.title }}</p>

		<div class="thumbnail">
			<div class="reveal background">
				<span>
					{{ presentation.preview|raw }}
				</span>
			</div>
		</div>
	</a>
	{% endfor %}
</div>




