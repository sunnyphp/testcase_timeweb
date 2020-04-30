<?php
declare(strict_types=1);

namespace App\View;

use App\Application;
use App\Wrapper\Configuration\Configuration;
use Exception;

/**
 * Класс Builder реализует функционал сборщика страницы
 * @package App\View
 * @author Sunny
 */
class Builder
{
	/**
	 * Конструктор класса
	 * @var Application
	 */
	private Application $app;
	
	/**
	 * Класс
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	
	/**
	 * Генерация HTML страницы по указанной обертке
	 * @param Configuration $configuration
	 * @return string
	 * @throws Exception
	 */
	public function generate(Configuration $configuration): string
	{
		// формируем список блоков
		$blocks = [];
		foreach ($configuration->getBlocks() as $block) {
			// формируем список сниппетов
			$snippets = [];
			//var_dump($block);exit;
			foreach ($block->getSnippets() as $snippet) {
				// устанавливаем сниппет по ключу
				$snippets[$snippet->getKey()] = $this->app->view->render($snippet->getTemplate(), [
					'used_vars'	=> $snippet->getUsedVars(),
				]);
			}
			
			// формируем блок
			$blocks[] = $this->app->view->render($block->getTemplate(), [
				'snippets'	=> $snippets,
				'used_vars'	=> $block->getUsedVars(),
			]);
		}
		
		// формируем backbone
		return $this->app->view->render($configuration->getUsedVar('template'), [
			'site'		=> $this->app->model_user_site->getUsedVars(),
			'blocks'	=> $blocks,
			'used_vars'	=> $configuration->getUsedVars(),
		]);
	}
}
