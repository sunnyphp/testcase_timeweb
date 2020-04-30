<?php
declare(strict_types=1);

namespace App\Model\User;

use App\Application;
use App\Model\Loader;
use App\Wrapper\Configuration\Collection;
use App\Wrapper\Configuration\Configuration;
use Exception;

/**
 * Класс PageCollectionModel реализует коллекцию настроек страниц пользователя
 * @package App\Model
 * @author Sunny
 */
class PageCollectionModel
{
	/**
	 * Класс приложения
	 * @var Application
	 */
	private Application $app;
	
	/**
	 * Коллекция классов-страниц
	 * @var Collection|Configuration[]
	 */
	private Collection $collection;
	
	/**
	 * Путь до файла с конфигурацией
	 * @var string
	 */
	private string $path;
	
	/**
	 * Конструктор класса
	 * @param Application $app
	 * @param string $file_path
	 * @throws Exception
	 */
	public function __construct(Application $app, string $file_path)
	{
		$this->app = $app;
		$this->collection = new Collection;
		$this->path = $app->fromRoot('user_data/'.$file_path);
		
		foreach (Loader::getArray($this->path) as $data) {
			$this->collection->attach(Configuration::getInstance($data));
		}
	}
	
	/**
	 * Ищет в коллекции подходящую модель страницы и возвращает ее конфигурацию или NULL
	 * @param string $page
	 * @return Configuration|null
	 */
	public function find(string $page): ?Configuration
	{
		foreach ($this->collection as $wrapper) {
			if ($wrapper->getUsedVar('uri') === $page) {
				return $wrapper;
			}
		}
		
		return null;
	}
	
	/**
	 * Удаляет элемент из коллекции
	 * @param int $index
	 * @return bool
	 */
	public function remove(int $index): bool
	{
		// удаление по индексу
		$success = $this->collection->removeByIndex($index);
		if ($success) {
			// сохранение в файл
			Loader::save($this->path, $this->collection->getArray());
		}
		
		return $success;
	}
	
	/**
	 * Возвращает коллекцию с моделями страниц
	 * @return Collection|Configuration[]
	 * @throws Exception
	 */
	public function getCollection(): Collection
	{
		return $this->collection;
	}
}
