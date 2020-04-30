<?php
declare(strict_types=1);

namespace App\Model;

use App\Application;
use App\Wrapper\Configuration\Configuration;
use Exception;

/**
 * Класс AbstractModel реализует стадартные методы модели для доступа к пользовательским файлам с настройками
 * @package App\Model\AbstractModel
 * @author Sunny
 */
abstract class AbstractModel
{
	/**
	 * Префикс пути
	 * @var string
	 */
	private string $path_prefix;
	
	/**
	 * Конструктор класса
	 */
	public function __construct()
	{
		$this->setPathPrefix(Application::fromRoot('configuration/'));
	}
	
	/**
	 * Возвращает префикс пути
	 * @return string
	 */
	public function getPathPrefix(): string
	{
		return $this->path_prefix;
	}
	
	/**
	 * Устанавливает префикс пути
	 * @param string $path_prefix
	 * @return AbstractModel
	 */
	public function setPathPrefix(string $path_prefix): self
	{
		$this->path_prefix = $path_prefix;
		
		return $this;
	}
	
	/**
	 * Возвращает конфигурацию по указанному пути
	 * @param string $file_path Путь относительно папки configuration
	 * @return Configuration
	 * @throws Exception
	 */
	public function get(string $file_path): Configuration
	{
		$file_path = Application::escapePath($file_path);
		if (is_file($this->path_prefix.$file_path)) {
			// парсим конфигурацию
			$array = Loader::getArray($this->path_prefix.$file_path);
			if ($array !== []) {
				// формируем конфигурацию и возвращаем её
				return Configuration::getInstance($array);
			}
		}
		
		throw new Exception('Конфигурация не может быть получена: '.htmlspecialchars($this->path_prefix.$file_path));
	}
}
