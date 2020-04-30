<div class="navbar">
	<div class="navbar-inner">
		<span class="brand">
			{$snippets.b2s_logotype|default:'Ошибка конфигурации'}
		</span>
		{if isset($snippets.b2s_menu)}
		<ul class="nav">
			{$snippets.b2s_menu}
		</ul>
		{/if}
	</div>
</div>
