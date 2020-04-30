<?php
declare(strict_types=1);

namespace App\View;

use App\Application;
use Exception;
use Smarty;
use SmartyException;

/**
 * Класс View отвечает за рендер шаблонов в дизайне
 *
 * @property Smarty $engine Класс шаблонизатора
 *
 * @package App\View
 * @author Sunny
 */
class View
{
	/**
	 * Магический метод для возврата субклассов
	 * @param string $property
	 * @return object|null
	 * @throws Exception
	 */
	public function __get(string $property)
	{
		if ($property === 'engine') {
			return $this->{$property} = $this->getEngine();
		}
		
		return null;
	}
	
	/**
	 * Возвращает настроенный класс шаблонизатора
	 * @return Smarty
	 * @throws Exception
	 */
	private function getEngine(): Smarty
	{
		try {
			// настройка шаблонизатора
			$engine = new Smarty;
			$engine->setTemplateDir(Application::fromRoot('template/'));
			$engine->setCompileDir(Application::fromRoot('user_data/compiled_tpl/'));
			$engine->setCacheDir(Application::fromRoot('user_data/cache_tpl/'));
			
			// общие настройки кеша
			$engine->locking_timeout = 1;
			$engine->caching = Smarty::CACHING_OFF;
			
			if (($_SERVER['HTTP_HOST'] ?? 'localhost') === '') {
				// не на продакшене все пересчитывается при каждом запросе
				$engine->force_compile = true;
				$engine->force_cache = true;
			}
		} catch (SmartyException $e) {
			// оборачиваем в нашу ошибку
			throw new Exception('Ошибка при создании класса шаблонизатора: '.$e->getMessage());
		}
		
		return $engine;
	}
	
	/**
	 * Рендер шаблона
	 * @param string $templateFile
	 * @param array $assigns
	 * @return string
	 * @throws SmartyException
	 */
	public function render(string $templateFile, array $assigns = []): string
	{
		if ($assigns) {
			$this->engine->assign($assigns);
		}
		
		return $this->engine->fetch($templateFile);
	}
}
