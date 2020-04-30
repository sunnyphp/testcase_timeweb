<div data-index="{$index|intval}" data-block="{$block|escape}">
	<input type="hidden" name="blocks[{$index|intval}][_path]" value="{$block|escape}">
	<h4>Блок: {$name|escape|default:'Не установлено'}</h4>
	
	{foreach $settings as $item}
		{if !$is_locked}
			{$item['disabled']=true}
		{/if}
		{include file="user/setting_item.tpl" item=$item}
	{/foreach}
	
	{if $is_locked}
		{* сниппеты зафиксированы, производим настройку *}
		{foreach $snippets as $snippet}
			<div>
				<h5>Сниппет: {$snippet.name|escape|default:'Не установлено'}</h5>
				<input type="hidden" name="blocks[{$index|intval}][_snippets][{$snippet.index}][_key]" value="{$snippet.key|escape}">
				<input type="hidden" name="blocks[{$index|intval}][_snippets][{$snippet.index}][_path]" value="{$snippet.path|escape}">
				{foreach $snippet.variables as $item}
					{include file="user/setting_item.tpl" item=$item}
				{/foreach}
			</div>
		{/foreach}
	{else}
		{* можно выбрать необходимые сниппеты *}
		{foreach $snippets as $snippet}
		<div class="control-group">
			<label class="control-label">Сниппет: {$snippet.name|escape|default:'Не установлено'}</label>
			<div class="controls">
				<select class="js-snippet-variant">
					{foreach $snippet.variants as $key => $value}
						<option value="{$key|escape}">{$value|escape}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{/foreach}

		<div class="control-group">
			<label class="control-label"></label>
			<div class="controls">
				<button class="btn btn-success js-block-lock"><i class="icon-lock icon-white"></i> Зафиксировать выбор</button>
				<button class="btn btn-danger js-block-delete"><i class="icon-trash icon-white"></i> Удалить блок</button>
			</div>
		</div>
	{/if}
	<hr>
</div>
