<?php
declare(strict_types=1);

namespace App\Wrapper\Configuration;

use Exception;
use SplObjectStorage;

/**
 * Класс Collection реализует коллекцию со специфическими для конфигурации методами
 * @package App\Wrapper\Configuration
 * @author Sunny
 */
class Collection extends SplObjectStorage
{
	/**
	 * @inheritDoc
	 */
	public function attach($object, $data = null)
	{
		if (!($object instanceof InterfaceSerialization)) {
			throw new Exception('Коллекция принимает только классы унаследованные от AbstractConfiguration');
		}
		
		parent::attach($object, $data);
	}
	
	/**
	 * Удаление из коллекции по индексу
	 * @param int $index
	 * @return bool
	 */
	public function removeByIndex(int $index): bool
	{
		foreach ($this as $i => $object) {
			if ($index === $i) {
				// удаляем из коллекции и подчищаем за собой
				$this->detach($object);
				unset($object);
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Возвращает массив с данными
	 * @return array
	 */
	public function getArray(): array
	{
		$ret = [];
		foreach ($this as $wrapper) {
			/** @var InterfaceSerialization $wrapper */
			$ret[] = $wrapper->getArray();
		}
		
		return $ret;
	}
}
