<?php
declare(strict_types=1);

namespace App\Model\User;

use App\Application;
use App\Model\AbstractModel;
use App\Wrapper\Configuration\Configuration;
use Exception;

/**
 * Класс SiteModel реализует модель для доступа к общим настройкам сайта
 * @package App\Model
 * @author Sunny
 */
class SiteModel extends AbstractModel
{
	/**
	 * @inheritDoc
	 */
	public function __construct()
	{
		$this->setPathPrefix(Application::fromRoot('user_data/'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function get(string $file_path): Configuration
	{
		try {
			return parent::get($file_path);
		} catch (Exception $e) {
			return new Configuration;
		}
	}
}
