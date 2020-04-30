<?php
declare(strict_types=1);

ini_set('xdebug.var_display_max_depth', '12');

require_once __DIR__.'/../vendor/autoload.php';

use App\Application;
use Symfony\Component\HttpFoundation\Response;

$app = new Application;

// получаем текущую страницу
$current_page = $app->request->getRequestUri();

// ищем страницу пользователя
$page = $app->model_user_page->find($current_page);

// страница не найдена
if ($page === null) {
	(new Response('Страница не найдена', Response::HTTP_NOT_FOUND))->send();
	exit;
}

// формируем страницу
echo $app->view_builder->generate($page);
