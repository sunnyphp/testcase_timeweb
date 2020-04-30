<?php
declare(strict_types=1);

namespace App\Wrapper\Configuration;

use App\Wrapper\Configuration\Block\Snippet;
use Exception;

/**
 * Класс AbstractConfiguration реализует стандартные методы конфигурации для блоков и сниппетов
 * @package App\Wrapper\Configuration
 * @author Sunny
 */
abstract class AbstractConfiguration implements InterfaceSerialization
{
	/**
	 * Путь до файла конфигурации
	 * @var string|null
	 */
	private ?string $path = null;
	
	/**
	 * Ключ используемый в шаблоне *.tpl
	 * @var string|null
	 */
	private ?string $key = null;
	
	/**
	 * Название отображаемое пользователю
	 * @var string|null
	 */
	private ?string $name = null;
	
	/**
	 * Используемый шаблон
	 * @var string|null
	 */
	private ?string $template = null;
	
	/**
	 * Коллекция блоков
	 * @var Collection|Block[]
	 */
	private Collection $blocks;
	
	/**
	 * Коллекция сниппетов
	 * @var Collection|Snippet[]
	 */
	private Collection $snippets;
	
	/**
	 * Доступные для использования переменные
	 * @var Collection|Variable[]
	 */
	private Collection $variables;
	
	/**
	 * Доступные варианты зависимых блоков/сниппетов
	 * @var string[]
	 */
	private array $variants = [];
	
	/**
	 * Массив используемых переменных в виде ключ-значение
	 * @var string[]
	 */
	private array $used_vars = [];
	
	/**
	 * Флаг обязательности заполнения значения
	 * @var bool
	 */
	private bool $required = false;
	
	/**
	 * Конструктор класса
	 */
	public function __construct()
	{
		$this->blocks = new Collection;
		$this->snippets = new Collection;
		$this->variables = new Collection;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getInstance(array $data): self
	{
		$self = new static;
		
		if (($data['path'] ?? null) !== null) {
			$self->setPath((string)$data['path']);
		}
		if (($data['key'] ?? null) !== null) {
			$self->setKey((string)$data['key']);
		}
		if (($data['name'] ?? null) !== null) {
			$self->setName((string)$data['name']);
		}
		if (($data['template'] ?? null) !== null) {
			$self->setTemplate((string)$data['template']);
		}
		if (is_array($data['blocks'] ?? false)) {
			foreach ($data['blocks'] as $variable) {
				$self->blocks->attach(Block::getInstance($variable));
			}
		}
		if (is_array($data['snippets'] ?? false)) {
			foreach ($data['snippets'] as $variable) {
				$self->snippets->attach(Snippet::getInstance($variable));
			}
		}
		if (is_array($data['variables'] ?? false)) {
			foreach ($data['variables'] as $variable) {
				$self->variables->attach(Variable::getInstance($variable));
			}
		}
		if (is_array($data['variants'] ?? false)) {
			$self->setVariants($data['variants']);
		}
		if (is_array($data['used_vars'] ?? false)) {
			$self->setUsedVars($data['used_vars']);
		}
		if (($data['required'] ?? false) !== false) {
			$self->setRequired(true);
		}
		
		return $self;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getArray(): array
	{
		$ret = [];
		
		if (($value = $this->getPath()) !== null) {
			$ret['path'] = $value;
		}
		if (($value = $this->getKey()) !== null) {
			$ret['key'] = $value;
		}
		if (($value = $this->getName()) !== null) {
			$ret['name'] = $value;
		}
		if (($value = $this->getTemplate()) !== null) {
			$ret['template'] = $value;
		}
		if ($this->isBlocks()) {
			$ret['blocks'] = $this->getBlocks()->getArray();
		}
		if ($this->isSnippets()) {
			$ret['snippets'] = $this->getSnippets()->getArray();
		}
		if ($this->isVariables()) {
			$ret['variables'] = $this->getVariables()->getArray();
		}
		if (($value = $this->getVariants()) !== []) {
			$ret['variants'] = $value;
		}
		if (($value = $this->getUsedVars()) !== []) {
			$ret['used_vars'] = $value;
		}
		if ($this->isRequired()) {
			$ret['required'] = true;
		}
		
		return $ret;
	}
	
	/**
	 * Возвращает путь до файла конфигурации
	 * @return string|null
	 */
	public function getPath(): ?string
	{
		return $this->path;
	}
	
	/**
	 * Устанавливает путь до файла конфигурации
	 * @param string|null $path
	 * @return self
	 */
	public function setPath(?string $path): self
	{
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Возвращает используемый ключ в шаблоне *.tpl
	 * @return string|null
	 */
	public function getKey(): ?string
	{
		return $this->key;
	}
	
	/**
	 * Устанавливает используемый ключ в шаблоне *.tpl
	 * @param string|null $key
	 * @return self
	 */
	public function setKey(?string $key): self
	{
		$this->key = $key;
		
		return $this;
	}
	
	/**
	 * Возвращает название отображаемое пользователю
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}
	
	/**
	 * Устанавливает название отображаемое пользователю
	 * @param string|null $name
	 * @return self
	 */
	public function setName(?string $name): self
	{
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Возвращает используемый шаблон
	 * @return string|null
	 */
	public function getTemplate(): ?string
	{
		return $this->template;
	}
	
	/**
	 * Устанавливает используемый шаблон
	 * @param string|null $template
	 * @return self
	 */
	public function setTemplate(?string $template): self
	{
		$this->template = $template;
		
		return $this;
	}
	
	/**
	 * Возвращает True если есть зависимость от блоков
	 * @return bool
	 */
	public function isBlocks(): bool
	{
		return count($this->blocks) > 0;
	}
	
	/**
	 * Добавляет блок в коллекцию
	 * @param AbstractConfiguration $block
	 * @return $this
	 * @throws Exception
	 */
	public function addBlock(AbstractConfiguration $block): self
	{
		$this->blocks->attach($block);
		
		return $this;
	}
	
	/**
	 * Возвращает коллекцию блоков
	 * @return Collection|Block[]
	 */
	public function getBlocks(): Collection
	{
		return $this->blocks;
	}
	
	/**
	 * Возвращает True если есть зависимость от сниппетов
	 * @return bool
	 */
	public function isSnippets(): bool
	{
		return count($this->snippets) > 0;
	}
	
	/**
	 * Добавляет сниппет в коллекцию
	 * @param AbstractConfiguration $snippet
	 * @return $this
	 * @throws Exception
	 */
	public function addSnippet(AbstractConfiguration $snippet): self
	{
		$this->snippets->attach($snippet);
		
		return $this;
	}
	
	/**
	 * Возвращает коллекцию сниппетов
	 * @return Collection|Snippet[]
	 */
	public function getSnippets(): Collection
	{
		return $this->snippets;
	}
	
	/**
	 * Возвращает True если есть классы с описанием переменных
	 * @return bool
	 */
	public function isVariables(): bool
	{
		return count($this->variables) > 0;
	}
	
	/**
	 * Добавляет доступную для использования в шаблоне переменную в коллекцию
	 * @param AbstractConfiguration $variable
	 * @return $this
	 * @throws Exception
	 */
	public function addVariable(AbstractConfiguration $variable): self
	{
		$this->variables->attach($variable);
		
		return $this;
	}
	
	/**
	 * Возвращает доступные для использования в шаблоне переменные
	 * @return Collection|Variable[]
	 */
	public function getVariables(): Collection
	{
		return $this->variables;
	}
	
	/**
	 * Возвращает True если указанный в параметре вариант есть в списке вариантов зависимых блоков/сниппетов
	 * @param string $variant
	 * @return bool
	 */
	public function isVariant(string $variant): bool
	{
		return in_array($variant, $this->variants);
	}
	
	/**
	 * Возвращает доступные варианты зависимых блоков/сниппетов
	 * @return string[]
	 */
	public function getVariants(): array
	{
		return $this->variants;
	}
	
	/**
	 * Устанавливает доступные варианты зависимых блоков/сниппетов
	 * @param array $variants
	 * @return self
	 */
	public function setVariants(array $variants): self
	{
		$this->variants = $variants;
		
		return $this;
	}
	
	/**
	 * Возвращает значение используемой переменной по ключу или значение по умолчанию
	 * @param int|float|string $key Ключ
	 * @param mixed $default Значение по умолчанию
	 * @return mixed
	 */
	public function getUsedVar($key, $default = null)
	{
		return ($this->used_vars[$key] ?? $default);
	}
	
	/**
	 * Устанавливает значение используемой переменной
	 * @param int|float|string $key Ключ
	 * @param mixed $value Значение
	 * @return self
	 */
	public function setUsedVar($key, $value): self
	{
		$this->used_vars[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Возвращает массив используемых переменных в виде ключ-значение
	 * @return string[]
	 */
	public function getUsedVars(): array
	{
		return $this->used_vars;
	}
	
	/**
	 * Устанавливает массив используемых переменных в виде ключ-значение
	 * @param string[] $used_vars
	 * @return self
	 */
	public function setUsedVars(array $used_vars): self
	{
		$this->used_vars = $used_vars;
		
		return $this;
	}
	
	/**
	 * Возвращает True если установлен флаг обязательности заполнения значения
	 * @return bool
	 */
	public function isRequired(): bool
	{
		return $this->required;
	}
	
	/**
	 * Устанавливает флаг обязательности заполнения значения
	 * @param bool $required
	 * @return self
	 */
	public function setRequired(bool $required): self
	{
		$this->required = $required;
		
		return $this;
	}
}
