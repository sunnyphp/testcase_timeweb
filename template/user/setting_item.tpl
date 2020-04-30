<div class="control-group">
	<label class="control-label">{$item.caption|escape}</label>
	<div class="controls">
		{if $item.type === 'textarea'}
			<textarea rows="6" name="{$item.key}"{if $item.placeholder} placeholder="{$item.placeholder|escape|nl2br}"{/if}{if $item.is_required} required{/if}{if isset($item.disabled) && $item.disabled} disabled{/if}>{if $item.value !== null}{$item.value|escape|nl2br}{/if}</textarea>
		{else}
			<div class="{if $item.prefix} input-prepend{else} input-append{/if}">
				{if $item.prefix}<span class="add-on">{$item.prefix|escape}</span>{/if}
				<input name="{$item.key}" type="{if $item.is_number}number{else}text{/if}" value="{if $item.value !== null}{$item.value|escape}{/if}"{if $item.is_min} min="{$item.min}"{/if}{if $item.is_max} max="{$item.max}"{/if}{if $item.placeholder} placeholder="{$item.placeholder|escape}"{/if}{if $item.is_required} required{/if}{if isset($item.disabled) && $item.disabled} disabled{/if}>
				{if $item.suffix}<span class="add-on">{$item.suffix|escape}</span>{/if}
			</div>
		{/if}
		{if $item.description}<span class="help-block"><i class="icon-question-sign"></i> {$item.description|escape}</span>{/if}
	</div>
</div>
