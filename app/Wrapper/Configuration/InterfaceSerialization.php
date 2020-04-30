<?php
declare(strict_types=1);

namespace App\Wrapper\Configuration;

use Exception;

/**
 * Интерфейс InterfaceSerialization описывает методы для сериализации и десериализации данных
 * @package App\Wrapper\Configuration
 * @author Sunny
 */
interface InterfaceSerialization
{
	/**
	 * Создает класс из переданных данных
	 * @param array $data
	 * @return self
	 * @throws Exception
	 */
	public static function getInstance(array $data): self;
	
	/**
	 * Возвращает массив для сохранения в конфигурации
	 * @return array
	 */
	public function getArray(): array;
}
