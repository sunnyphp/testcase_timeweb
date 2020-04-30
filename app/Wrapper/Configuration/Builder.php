<?php
declare(strict_types=1);

namespace App\Wrapper\Configuration;

/**
 * Класс Builder реализует обертку для доступа к настройкам билдера
 * @package App\Wrapper\Configuration
 * @author Sunny
 */
class Builder
{
	/**
	 * Данные в виде ключ-значение
	 * @var array 
	 */
	private array $data = [];
	
	/**
	 * Конструктор класса
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	
	/**
	 * Возвращает значение по ключу или значение по умолчанию
	 * @param int|float|string $key Ключ
	 * @param mixed $default Значение по умолчанию
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return ($this->data[$key] ?? $default);
	}
	
	/**
	 * Возвращает True если в настройках билдера есть указанный сниппет
	 * @param string $snippet
	 * @return bool
	 */
	public function hasSnippet(string $snippet): bool
	{
		$array = $this->get('snippets', []);
		if (!is_array($array)) {
			$array = [];
		}
		
		return in_array($snippet, $array);
	}
	
	/**
	 * Возвращает True если в настройках билдера есть указанный блок
	 * @param string $block
	 * @return bool
	 */
	public function hasBlock(string $block): bool
	{
		$array = $this->get('blocks', []);
		if (!is_array($array)) {
			$array = [];
		}
		
		return in_array($block, $array);
	}
	
	/**
	 * Возвращает True если в настройках билдера есть указанный шаблон
	 * @param string $template
	 * @return bool
	 */
	public function hasTemplate(string $template): bool
	{
		$array = $this->get('templates', []);
		if (!is_array($array)) {
			$array = [];
		}
		
		return in_array($template, $array);
	}
}
