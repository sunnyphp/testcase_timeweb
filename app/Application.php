<?php
declare(strict_types=1);

namespace App;

use App\Model\Loader;
use App\Model\User;
use App\View;
use App\Wrapper\Configuration\Builder;
use App\Wrapper\Configuration\Configuration;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Класс Application реализует приложение
 * @property Filesystem $fs Класс для доступа к файловой системе
 * @property Model\ConfigurationModel $configuration Класс для доступа к конфигруациям
 * @property Builder $model_builder Класс для доступа к настройкам билдера
 * @property User\PageCollectionModel $model_user_page Пользовательские настройки страниц
 * @property Configuration $model_user_site Пользовательские настройки сайта
 * @property Request $request Компонент Symfony HTTP Foundation
 * @property View\View $view Компонент Smarty шаблонизатор
 * @property View\Builder $view_builder Сборщик HTML страницы
 * @package App
 * @author Sunny
 */
class Application
{
	/**
	 * Метод для возврата субклассов
	 * @param string $property
	 * @return null
	 * @throws Exception
	 */
	public function __get(string $property)
	{
		switch ($property) {
			// класс для доступа к файловой системе
			case 'fs':
				return $this->{$property} = new Filesystem;
				
			// модель: конфигурация
			case 'configuration':
				return $this->{$property} = new Model\ConfigurationModel;
			
			// настройки билдера
			case 'model_builder':
				$data = Loader::getArray(self::fromRoot('configuration/core/builder.yaml'));
				
				return $this->{$property} = new Builder($data);
			
			// модель: общие настройки сайта
			case 'model_user_site':
				return $this->{$property} = (new User\SiteModel)->get('site.yaml');
			
			// модель: страницы сайта
			case 'model_user_page':
				return $this->{$property} = new User\PageCollectionModel($this, 'page.yaml');
			
			// компонент Symfony HTTP Foundation
			case 'request':
				return $this->{$property} = Request::createFromGlobals();
			
			// компонент Smarty шаблонизатор
			case 'view':
				return $this->{$property} = new View\View;
			
			// сборщик HTML страницы
			case 'view_builder':
				return $this->{$property} = new View\Builder($this);
		}
		
		return null;
	}
	
	/**
	 * Возвращает путь от корня до указанного в параметре
	 * @param string $path
	 * @return string
	 */
	public static function fromRoot(?string $path = null): string
	{
		// определяем путь до корня сайта в ФС
		$rootPath = realpath(dirname(__DIR__)).DIRECTORY_SEPARATOR;
		
		// возвращаем только путь до корня
		if ($path === null || $path === '') {
			return $rootPath;
		}
		
		return $rootPath.ltrim(self::escapePath($path), '/');
	}
	
	/**
	 * Конвертация разделителей директорий
	 * @param string $path
	 * @return string
	 */
	public static function escapePath(string $path): string
	{
		$ds = DIRECTORY_SEPARATOR;
		$separatorConvert = [
			'\\'	=> '/',
			'/'		=> '\\',
		];
		
		// конвертация типов разделителей директорий
		$path = str_replace([$separatorConvert[$ds], $ds.$ds], $ds, $path);
		
		return $path;
	}
}
