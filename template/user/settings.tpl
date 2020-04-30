{if isset($caption) && $caption}
<div class="page-header">
	<h1>{$caption|escape}</h1>
</div>
{/if}

{if isset($alerts) && is_array($alerts)}
	{foreach $alerts as $alert}
		<div class="alert alert-{$alert.type|escape}">{$alert.text|escape}</div>
	{/foreach}
{/if}

{if isset($items) && is_array($items)}
<form class="form-horizontal" method="post">
	{foreach $items as $item}
		{include file="user/setting_item.tpl" item=$item}
	{/foreach}

	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
	</div>
</form>
{else}
	<div class="alert alert-info">Нет настроек :(</div>
{/if}
