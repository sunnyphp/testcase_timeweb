<!DOCTYPE html>

<html lang="en">
<head>
	<title>{if isset($site.name)}{$site.name|escape} | {/if}{$used_vars.name|escape|default:'Не установлено'}</title>
	<link href="/common/css/bootstrap.min.css" rel="stylesheet">
	<script type="text/javascript" src="/common/js/jquery-3.5.0.min.js"></script>
	<script type="text/javascript" src="/common/js/bootstrap.min.js"></script>
</head>
<body style="background-color: #{$used_vars.bg_color|default:($site.bg_color|default:'ffffff')|escape}">

<div class="container">
	{if isset($blocks)}
		{foreach $blocks as $block}
			{$block}
		{/foreach}
	{else}
		<div class="alert alert-error">
			Необходимо настроить сайт.<br><br>

			<a href="admin.php">Настроить</a>
		</div>
	{/if}
</div>

</body>
</html>
