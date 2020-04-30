<?php
declare(strict_types=1);

ini_set('xdebug.var_display_max_depth', '12');

require_once __DIR__.'/../vendor/autoload.php';

use App\Application;
use App\Model\Loader;
use App\Wrapper\Configuration\Block;
use App\Wrapper\Configuration\Configuration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

$app = new Application;

$current_action = $app->request->query->get('action', 'index');
$allow_actions = ['index', 'listing', 'create', 'create_block', 'delete', ];
if (!in_array($current_action, $allow_actions)) {
	$current_action = reset($allow_actions);
}

if ($current_action === 'listing') {

	// список страниц
	$items = [];
	$configuration = $app->configuration->get('user/page.yaml');
	foreach ($app->model_user_page->getCollection() as $i => $page) {
		// получаем переменные страницы
		$variables = [];
		foreach ($configuration->getVariables() as $variable) {
			$variables[] = sprintf(
				'%s: %s%s%s',
				$variable->getCaption(),
				$variable->getPrefix(),
				$page->getUsedVar($variable->getKey(), $variable->getDefault() ?: '—'),
				$variable->getSuffix(),
			);
		}
		
		// формируем массив
		$items[] = [
			'key'			=> $i,
			'link'			=> $page->getUsedVar('uri'),
			'variables'		=> implode(PHP_EOL, $variables),
		];
	}
	
	// формируем содержимое
	$content = $app->view->render('admin/listing.tpl', [
		'caption'	=> 'Управление страницами',
		'items'		=> $items,
	]);

} elseif ($current_action === 'create') {

	// создание страницы
	$content = 'No content';
	
	// сохранение изменений
	if ($app->request->isMethod($app->request::METHOD_POST)) {
		// получаем переменные
		$blocks = $app->request->request->get('blocks');
		$used_vars = $app->request->request->all();
		unset($used_vars['blocks']);
		
		// формируем обертку
		$page = (new Configuration)
			->setUsedVars($used_vars)
		;
		
		// проверяем URI
		if (($uri = $page->getUsedVar('uri')) === null || $app->model_user_page->find($uri) !== null) {
			throw new Exception('Страница с таким адресом уже существует');
		}
		
		// перебираем блоки
		if (is_array($blocks)) {
			foreach ($blocks as $block) {
				// валидация блока
				if (($name = (string)($block['_path'] ?? '')) !== '' && $app->model_builder->hasBlock($name)) {
					// получаем конфигурацию блока
					$blockConf = $app->configuration->get($name);
					
					// формируем блок для страницы
					$pageBlock = (new Block)
						->setPath($blockConf->getPath())
						->setKey($blockConf->getKey())
						->setTemplate($blockConf->getTemplate())
					;
					
					// перебираем сниппеты
					if (is_array($snippets = ($block['_snippets'] ?? false))) {
						foreach ($snippets as $snippet) {
							// валидация сниппета
							if (($name = (string)($snippet['_path'] ?? '')) !== '' && $app->model_builder->hasSnippet($name)) {
								// получаем конфигурацию сниппета
								$snippetConf = $app->configuration->get($name);
								
								// формируем сниппет для блока
								$blockSnippet = (new Block\Snippet)
									->setKey($snippet['_key'])
									->setPath($snippetConf->getPath())
									->setTemplate($snippetConf->getTemplate())
									->setUsedVars($used_vars)
								;
								
								// добавляем оставшиеся переменные
								unset($snippet['_key'], $snippet['_path']);
								if (is_array($snippet) && $snippet !== []) {
									$blockSnippet->setUsedVars($snippet);
								}
								
								// добавляем сниппет в блок
								$pageBlock->addSnippet($blockSnippet);
							}
						}
					}
					
					// добавляем оставшиеся переменные
					unset($block['_path'], $block['_snippets']);
					if (is_array($block) && $block !== []) {
						$pageBlock->setUsedVars($block);
					}
					
					// добавляем блок в страницу
					$page->addBlock($pageBlock);
				}
			}
		}
		
		// добавляем в коллекцию страниц
		$app->model_user_page->getCollection()->attach($page);
		
		// сохраняем изменения
		Loader::save(Application::fromRoot('user_data/page.yaml'), $app->model_user_page->getCollection()->getArray());
		
		// редирект на список страниц
		(new RedirectResponse('admin.php?action=listing'))->send();
		exit;
	}
	
	// переменные для работы
	$settings = $blocks = [];
	
	// формируем список настроек
	$configuration = $app->configuration->get('user/page.yaml');
	foreach ($configuration->getVariables() as $variable) {
		$isNumber = $variable->getType() === $variable::TYPE_NUMBER;
		
		// формируем массив для шаблонизатора
		$settings[] = [
			'key'			=> $variable->getKey(),
			'type'			=> $variable->getType(),
			'caption'		=> $variable->getCaption(),
			'description'	=> $variable->getDescription(),
			'placeholder'	=> $variable->getPlaceholder(),
			'prefix'		=> $variable->getPrefix(),
			'suffix'		=> $variable->getSuffix(),
			'is_required'	=> $variable->isRequired(),
			'is_number'		=> $isNumber,
			'is_min'		=> ($isNumber && $variable->getLimitMin() !== PHP_INT_MIN),
			'is_max'		=> ($isNumber && $variable->getLimitMax() !== PHP_INT_MAX),
			'min'			=> $variable->getLimitMin(),
			'max'			=> $variable->getLimitMax(),
			'value'			=> $variable->getDefault(),
		];
	}
	
	// формируем список блоков
	foreach ($app->model_builder->get('blocks', []) as $block) {
		// получаем конфигурацию блока
		$configuration = $app->configuration->get($block);
		
		// формируем массив для шаблонизатора
		$blocks[] = [
			'value'		=> $block,
			'caption'	=> $configuration->getName(),
		];
	}
	
	// формируем содержимое
	$content = $app->view->render('admin/create.tpl', [
		'caption'	=> 'Добавить страницу',
		'settings'	=> $settings,
		'blocks'	=> $blocks,
	]);

} elseif ($current_action === 'create_block') {

	// добавление блока
	$name = $app->request->request->get('name');
	$index = $app->request->request->getInt('index');
	if (!$app->model_builder->hasBlock((string)$name)) {
		// ошибка
		(new Response('Страница не найдена', Response::HTTP_NOT_FOUND))->send();
		exit;
	}
	
	// массив выбранных сниппетов
	$is_locked = $app->request->request->getBoolean('is_locked');
	$locked_snippets = $app->request->request->get('locked_snippets');
	if (!is_array($locked_snippets)) {
		$locked_snippets = [];
	}
	
	// получаем конфигурацию
	$configuration = $app->configuration->get($name);
	$settings = $snippets = [];
	foreach ($configuration->getVariables() as $variable) {
		$isNumber = $variable->getType() === $variable::TYPE_NUMBER;
		
		// формируем массив для шаблонизатора
		$settings[] = [
			'key'			=> sprintf(
				'blocks[%u][%s]',
				$index,
				$variable->getKey()
			),
			'type'			=> $variable->getType(),
			'caption'		=> $variable->getCaption(),
			'description'	=> $variable->getDescription(),
			'placeholder'	=> $variable->getPlaceholder(),
			'prefix'		=> $variable->getPrefix(),
			'suffix'		=> $variable->getSuffix(),
			'is_required'	=> $variable->isRequired(),
			'is_number'		=> $isNumber,
			'is_min'		=> ($isNumber && $variable->getLimitMin() !== PHP_INT_MIN),
			'is_max'		=> ($isNumber && $variable->getLimitMax() !== PHP_INT_MAX),
			'min'			=> $variable->getLimitMin(),
			'max'			=> $variable->getLimitMax(),
			'value'			=> $variable->getDefault(),
		];
	}
	foreach ($configuration->getSnippets() as $sn_index => $snippet) {
		$variants = $variables = [];
		$is_snippet_used = false;
		
		if (!$is_locked && !$snippet->isRequired()) {
			$variants = [0 => 'Не использовать', ];
		}
		
		foreach ($snippet->getVariants() as $variant) {
			if ($app->model_builder->hasSnippet($variant)) {
				if ($is_locked) {
					// проверяем, является ли этот сниппет выбранным пользователем
					if (in_array($variant, $locked_snippets)) {
						$is_snippet_used = true;
					} else {
						continue;
					}
					
					// получаем конфигурацию сниппета чтобы добраться до его переменных
					$variantConf = $app->configuration->get($variant);
					
					// хаки
					$snippet->setPath($variantConf->getPath());
					$snippet->setName($snippet->getName().' / '.$variantConf->getName());
					
					// перебираем переменные
					foreach ($variantConf->getVariables() as $variable) {
						$isNumber = $variable->getType() === $variable::TYPE_NUMBER;
						
						// формируем массив для шаблонизатора
						$variables[] = [
							'key'			=> sprintf(
								'blocks[%u][_snippets][%u][%s]',
								$index,
								$sn_index,
								$variable->getKey()
							),
							'type'			=> $variable->getType(),
							'caption'		=> $variable->getCaption(),
							'description'	=> $variable->getDescription(),
							'placeholder'	=> $variable->getPlaceholder(),
							'prefix'		=> $variable->getPrefix(),
							'suffix'		=> $variable->getSuffix(),
							'is_required'	=> $variable->isRequired(),
							'is_number'		=> $isNumber,
							'is_min'		=> ($isNumber && $variable->getLimitMin() !== PHP_INT_MIN),
							'is_max'		=> ($isNumber && $variable->getLimitMax() !== PHP_INT_MAX),
							'min'			=> $variable->getLimitMin(),
							'max'			=> $variable->getLimitMax(),
							'value'			=> $variable->getDefault(),
						];
					}
				} else {
					// получаем варианты сниппетов в блоке для последующего выбора
					$variantConf = $app->configuration->get($variant);
					$variants[$variant] = $variantConf->getName();
					$is_snippet_used = true;
				}
			}
		}
		
		if ($is_snippet_used) {
			$snippets[] = [
				'index'		=> $sn_index,
				'key'		=> $snippet->getKey(),
				'path'		=> $snippet->getPath(),
				'name'		=> $snippet->getName(),
				'variants'	=> $variants,
				'variables'	=> $variables,
			];
		}
	}
	
	// возвращаем ответ
	echo $app->view->render('admin/create_block.tpl', [
		'index'				=> $index,
		'block'				=> $name,
		'name'				=> $configuration->getName(),
		'settings'			=> $settings,
		'snippets'			=> $snippets,
		'is_locked'			=> $is_locked,
	]);
	exit;

} elseif ($current_action === 'delete') {

	// удаление страницы
	$index = $app->request->request->getInt('index', -1);
	if ($index >= 0) {
		// удаление страницы
		$app->model_user_page->remove($index);
	}
	
	// возвращаем ответ
	(new JsonResponse(['success' => true, ]))->send();
	exit;

} else {

	// управление настройками сайта
	$configuration = $app->configuration->get('user/site.yaml');
	$items = $alerts = [];
	
	// сохранение данных
	if ($app->request->isMethod($app->request::METHOD_POST)) {
		$save_required = false;
		foreach ($app->request->request->all() as $k => $v) {
			$variable = null;
			foreach ($configuration->getVariables() as $wrapper) {
				if ($wrapper->getKey() === $k) {
					$variable = $wrapper;
					break;
				}
			}
			
			if ($variable !== null) {
				if ($variable->validate($v)) {
					// изменяем переменную
					$app->model_user_site->setUsedVar($k, $v);
					$save_required = true;
				} else {
					// ошибка
					$alerts[] = [
						'type'	=> 'error',
						'text'	=> sprintf(
							'Переменная %s не может быть сохранена, некорректный формат',
							$variable->getCaption()
						),
					];
				}
			} else {
				// ошибка
				$alerts[] = [
					'type'	=> 'error',
					'text'	=> sprintf(
						'Переменная %s не может быть сохранена, ключ не существует в конфигурации',
						$k
					),
				];
			}
		}
		
		if ($save_required) {
			// сохраняем изменения
			Loader::save(Application::fromRoot('user_data/site.yaml'), $app->model_user_site->getArray());
			
			// успех
			$count = count($alerts);
			$alerts[] = [
				'type'	=> 'success',
				'text'	=> 'Изменения сохранены'.($count > 0 ? ' частично, не изменено значений: '.$count : null),
			];
		}
	}
	
	// формируем список настроек
	foreach ($configuration->getVariables() as $variable) {
		$isNumber = $variable->getType() === $variable::TYPE_NUMBER;
		
		// формируем массив для шаблонизатора
		$items[] = [
			'key'			=> $variable->getKey(),
			'type'			=> $variable->getType(),
			'caption'		=> $variable->getCaption(),
			'description'	=> $variable->getDescription(),
			'placeholder'	=> $variable->getPlaceholder(),
			'prefix'		=> $variable->getPrefix(),
			'suffix'		=> $variable->getSuffix(),
			'is_required'	=> $variable->isRequired(),
			'is_number'		=> $isNumber,
			'is_min'		=> ($isNumber && $variable->getLimitMin() !== PHP_INT_MIN),
			'is_max'		=> ($isNumber && $variable->getLimitMax() !== PHP_INT_MAX),
			'min'			=> $variable->getLimitMin(),
			'max'			=> $variable->getLimitMax(),
			'value'			=> $app->model_user_site->getUsedVar($variable->getKey(), $variable->getDefault()),
		];
	}
	
	// формируем содержимое
	$content = $app->view->render($configuration->getTemplate(), [
		'alerts'	=> $alerts,
		'caption'	=> $configuration->getName(),
		'items'		=> $items,
	]);
}

// выводим шаблон
echo $app->view->render('admin/index.tpl', [
	'content'	=> $content,
]);
