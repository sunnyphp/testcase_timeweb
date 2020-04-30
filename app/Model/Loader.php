<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Класс Loader реализует загрузку и сохранение данных из/в YAML
 * @package App\Model
 * @author Sunny
 */
class Loader
{
	/**
	 * Сохраняет YAML
	 * @param string $path Путь до YAML файла
	 * @param mixed $data Данные
	 * @return bool
	 * @throws IOException
	 */
	public static function save(string $path, $data): bool
	{
		// опции формирования Yaml
		$opts = Yaml::DUMP_OBJECT_AS_MAP;
		$opts |= Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK;
		$opts |= Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE;
		
		// формируем Yaml документ и сохраняем в файл
		(new Filesystem)->dumpFile($path, Yaml::dump($data, PHP_INT_MAX, 2, $opts));
		
		// возвращаем True или исключение
		return true;
	}
	
	/**
	 * Загружает YAML, парсит его и возвращает в виде массива
	 * @param string $path Путь до YAML файла
	 * @return array
	 */
	public static function getArray(string $path): array
	{
		$array = [];
		
		// получаем данные
		$result = @file_get_contents($path);
		if ($result !== false) {
			// парсим данные
			$array = Yaml::parse($result);
			if (!is_array($array)) {
				$array = [];
			}
		}
		
		return $array;
	}
}
