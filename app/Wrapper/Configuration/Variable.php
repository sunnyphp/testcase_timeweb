<?php
declare(strict_types=1);

namespace App\Wrapper\Configuration;

use Exception;

/**
 * Класс Configuration реализует обертку для доступа к переменным конфигурации
 * @package App\Wrapper\Configuration
 * @author Sunny
 */
class Variable implements InterfaceSerialization
{
	/**
	 * Тип input-поля для пользователя: текст
	 * @var string
	 */
	public const TYPE_TEXT = 'text';
	
	/**
	 * Тип input-поля для пользователя: числовое значение
	 * @var string
	 */
	public const TYPE_NUMBER = 'number';
	
	/**
	 * Тип валидации значения от пользователя: текст
	 * @var string
	 */
	public const VALIDATION_TEXT = 'text';
	
	/**
	 * Тип валидации значения от пользователя: числовое значение
	 * @var string
	 */
	public const VALIDATION_INTEGER = 'integer';
	
	/**
	 * Тип валидации значения от пользователя: hex-цвет
	 * @var string
	 */
	public const VALIDATION_COLOR = 'color';
	
	/**
	 * Ключ переменной
	 * @var string|null
	 */
	private ?string $key = null;
	
	/**
	 * Название переменной для пользователя
	 * @var string|null
	 */
	private ?string $caption = null;
	
	/**
	 * Описание переменной для пользователя
	 * @var string|null
	 */
	private ?string $description = null;
	
	/**
	 * Тип input-поля для пользователя
	 * @var string
	 */
	private string $type = 'text';
	
	/**
	 * Тип валидации значения от пользователя (text, integer, регулярное выражение)
	 * @var string
	 */
	private string $validation = self::VALIDATION_TEXT;
	
	/**
	 * Значение по умолчанию для input-поля
	 * @var int|float|string|null
	 */
	private $default = null;
	
	/**
	 * Плейсхолдер для input-поля
	 * @var string|null
	 */
	private ?string $placeholder = null;
	
	/**
	 * Префикс для поля ввода
	 * @var string|null
	 */
	private ?string $prefix = null;
	
	/**
	 * Суффикс для поля ввода
	 * @var string|null
	 */
	private ?string $suffix = null;
	
	/**
	 * Флаг обязательности заполнения значения
	 * @var bool
	 */
	private bool $required = false;
	
	/**
	 * Минимальное значение (при validation=integer)
	 * @var int
	 */
	private int $limit_min = PHP_INT_MIN;
	
	/**
	 * Максимальное значение (при validation=integer)
	 * @var int
	 */
	private int $limit_max = PHP_INT_MAX;
	
	/**
	 * @inheritDoc
	 */
	public static function getInstance(array $data): Variable
	{
		$self = new self;
		
		// проверяем обязательные ключи
		if (!isset($data['key'], $data['caption'])) {
			throw new Exception('Не передан какой-то ключ конфигурации: key, caption');
		}
		
		// установка обязательных данных
		$self
			->setKey((string)$data['key'])
			->setCaption((string)$data['caption'])
		;
		
		// остальное
		if (($data['type'] ?? null) !== null) {
			$self->setType((string)$data['type']);
		}
		if (($data['description'] ?? null) !== null) {
			$self->setDescription((string)$data['description']);
		}
		if (($data['validation'] ?? null) !== null) {
			$self->setValidation((string)$data['validation']);
		}
		if (($data['default'] ?? null) !== null) {
			$self->setDefault((string)$data['default']);
		}
		if (($data['placeholder'] ?? null) !== null) {
			$self->setPlaceholder((string)$data['placeholder']);
		}
		if (($data['prefix'] ?? null) !== null) {
			$self->setPrefix((string)$data['prefix']);
		}
		if (($data['suffix'] ?? null) !== null) {
			$self->setSuffix((string)$data['suffix']);
		}
		if (($data['required'] ?? false) !== false) {
			$self->setRequired(true);
		}
		if (($data['limit_min'] ?? PHP_INT_MIN) !== PHP_INT_MIN) {
			$self->setLimitMin((int)$data['limit_min']);
		}
		if (($data['limit_max'] ?? PHP_INT_MAX) !== PHP_INT_MAX) {
			$self->setLimitMax((int)$data['limit_max']);
		}
		
		return $self;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getArray(): array
	{
		$return = [
			'key'		=> $this->getKey(),
			'caption'	=> $this->getCaption(),
		];
		
		if (($value = $this->getDefault()) !== null) {
			$return['default'] = $value;
		}
		if (($value = $this->getDescription()) !== null) {
			$return['description'] = $value;
		}
		if (($value = $this->getPlaceholder()) !== null) {
			$return['placeholder'] = $value;
		}
		
		if (($type = $this->getType()) === self::TYPE_NUMBER) {
			// числовое значение
			$return['type'] = $type;
			
			if (($value = $this->getLimitMin()) !== PHP_INT_MIN) {
				$return['limit_min'] = $value;
			}
			
			if (($value = $this->getLimitMax()) !== PHP_INT_MAX) {
				$return['limit_max'] = $value;
			}
		} elseif ($type !== self::TYPE_TEXT) {
			// другие типы
			$return['type'] = $type;
		}
		if (($value = $this->getValidation()) !== self::VALIDATION_TEXT) {
			$return['validation'] = $value;
		}
		if (($value = $this->getPrefix()) !== null) {
			$return['prefix'] = $value;
		}
		if (($value = $this->getSuffix()) !== null) {
			$return['suffix'] = $value;
		}
		if ($this->isRequired()) {
			$return['required'] = true;
		}
		
		return $return;
	}
	
	/**
	 * Валидирует переданное значение на основе параметров класса
	 * @param mixed $data
	 * @return bool
	 */
	public function validate($data): bool
	{
		// требуется значение, но не указано
		if ($this->isRequired() && ($data === null || $data === '')) {
			return false;
		}
		
		if ($this->getType() === self::TYPE_NUMBER) {
			// требуется числовое значение
			if (!is_int($data) && !is_float($data) && !ctype_digit($data)) {
				return false;
			}
			
			// проверяем лимиты
			if (($min = $this->getLimitMin()) !== PHP_INT_MIN) {
				if ($data < $min) {
					return false;
				}
			}
			if (($max = $this->getLimitMax()) !== PHP_INT_MAX) {
				if ($data > $max) {
					return false;
				}
			}
		} elseif ($this->getType() === self::TYPE_TEXT) {
			// требуется текстовое значение
			if (!is_string($data)) {
				return false;
			}
			
			// текстовое значение
			if ($this->getValidation() === self::VALIDATION_COLOR) {
				if (preg_match('~^(?:[a-f\d]{3}|[a-f\d]{6})$~is', $data) !== 1) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Возвращает ключ переменной
	 * @return string|null
	 */
	public function getKey(): ?string
	{
		return $this->key;
	}
	
	/**
	 * Устанавливает ключ переменной
	 * @param string|null $key
	 * @return Variable
	 */
	public function setKey(?string $key): Variable
	{
		$this->key = $key;
		
		return $this;
	}
	
	/**
	 * Возвращает название переменной для пользователя
	 * @return string|null
	 */
	public function getCaption(): ?string
	{
		return $this->caption;
	}
	
	/**
	 * Устанавливает название переменной для пользователя
	 * @param string|null $caption
	 * @return Variable
	 */
	public function setCaption(?string $caption): Variable
	{
		$this->caption = $caption;
		
		return $this;
	}
	
	/**
	 * Возвращает описание переменной для пользователя
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	/**
	 * Устанавливает описание переменной для пользователя
	 * @param string|null $description
	 * @return Variable
	 */
	public function setDescription(?string $description): Variable
	{
		$this->description = $description;
		
		return $this;
	}
	
	/**
	 * Возвращает тип input-поля для пользователя
	 * @return string
	 *@see TYPE_NUMBER
	 * @see TYPE_TEXT
	 */
	public function getType(): string
	{
		return $this->type;
	}
	
	/**
	 * Устанавливает тип input-поля для пользователя
	 * @param string $type
	 * @return Variable
	 *@see TYPE_TEXT
	 * @see TYPE_NUMBER
	 */
	public function setType(string $type): Variable
	{
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * Возвращает тип валидации значения от пользователя
	 * @see VALIDATION_TEXT
	 * @see VALIDATION_INTEGER
	 * @see VALIDATION_COLOR
	 * @return string константа или регулярное выражение
	 */
	public function getValidation(): string
	{
		return $this->validation;
	}
	
	/**
	 * Устанавливает тип валидации значения от пользователя
	 * @see VALIDATION_TEXT
	 * @see VALIDATION_INTEGER
	 * @see VALIDATION_COLOR
	 * @param string $validation константа или регулярное выражение
	 * @return Variable
	 */
	public function setValidation(string $validation): Variable
	{
		$this->validation = $validation;
		
		return $this;
	}
	
	/**
	 * Возвращает значение по умолчанию для input-поля
	 * @return float|int|string|null
	 */
	public function getDefault()
	{
		return $this->default;
	}
	
	/**
	 * Устанавливает значение по умолчанию для input-поля
	 * @param float|int|string|null $default
	 * @return Variable
	 */
	public function setDefault($default)
	{
		$this->default = $default;
		
		return $this;
	}
	
	/**
	 * Возвращает плейсхолдер для input-поля
	 * @return string|null
	 */
	public function getPlaceholder(): ?string
	{
		return $this->placeholder;
	}
	
	/**
	 * Устанавливает плейсхолдер для input-поля
	 * @param string|null $placeholder
	 * @return Variable
	 */
	public function setPlaceholder(?string $placeholder): Variable
	{
		$this->placeholder = $placeholder;
		
		return $this;
	}
	
	/**
	 * Возвращает префикс для поля ввода
	 * @return string|null
	 */
	public function getPrefix(): ?string
	{
		return $this->prefix;
	}
	
	/**
	 * Устанавливает префикс для поля ввода
	 * @param string|null $prefix
	 * @return Variable
	 */
	public function setPrefix(?string $prefix): Variable
	{
		$this->prefix = $prefix;
		
		return $this;
	}
	
	/**
	 * Возвращает суффикс для поля ввода
	 * @return string|null
	 */
	public function getSuffix(): ?string
	{
		return $this->suffix;
	}
	
	/**
	 * Устанавливает суффикс для поля ввода
	 * @param string|null $suffix
	 * @return Variable
	 */
	public function setSuffix(?string $suffix): Variable
	{
		$this->suffix = $suffix;
		
		return $this;
	}
	
	/**
	 * Возвращает True если значение обязательно к заполнению значения
	 * @return bool
	 */
	public function isRequired(): bool
	{
		return $this->required;
	}
	
	/**
	 * Устанавливает флаг обязательности заполнения значением
	 * @param bool $required
	 * @return Variable
	 */
	public function setRequired(bool $required): Variable
	{
		$this->required = $required;
		
		return $this;
	}
	
	/**
	 * Возвращает минимальное значение (при validation=integer)
	 * @return int
	 */
	public function getLimitMin(): int
	{
		return $this->limit_min;
	}
	
	/**
	 * Устанавливает минимальное значение (при validation=integer)
	 * @param int $limit_min
	 * @return Variable
	 */
	public function setLimitMin(int $limit_min): Variable
	{
		$this->limit_min = $limit_min;
		
		return $this;
	}
	
	/**
	 * Возвращает максимальное значение (при validation=integer)
	 * @return int
	 */
	public function getLimitMax(): int
	{
		return $this->limit_max;
	}
	
	/**
	 * Устанавливает максимальное значение (при validation=integer)
	 * @param int $limit_max
	 * @return Variable
	 */
	public function setLimitMax(int $limit_max): Variable
	{
		$this->limit_max = $limit_max;
		
		return $this;
	}
}
