<!DOCTYPE html>

<html lang="en">
<head>
	<title>Панель управления</title>
	<link href="/common/css/bootstrap.min.css" rel="stylesheet">
	<script type="text/javascript" src="/common/js/jquery-3.5.0.min.js"></script>
	<script type="text/javascript" src="/common/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
	<div class="navbar">
		<div class="navbar-inner">
			<span class="brand">Меню панели управления</span>
			<ul class="nav">
				<li><a href="admin.php"><i class="icon-pencil"></i> Настройки сайта</a></li>
				<li><a href="admin.php?action=listing"><i class="icon-book"></i> Управление страницами</a></li>
				<li><a href="admin.php?action=create"><i class="icon-bookmark"></i> Добавить страницу</a></li>
			</ul>
		</div>
	</div>

	{$content|default:'Не установлено'}
</div>

</body>
</html>
