<?php

namespace Addons\Optimizer;

use Addons\Base\Abstract_Htaccess;

\defined( 'ABSPATH' ) || die;

final class Browser_Cache extends Abstract_Htaccess {

	/**
	 * @var string|null
	 */
	public ?string $template = 'browser-caching.tpl';

	/**
	 * @var array|string[]
	 */
	public array $rules = [
		'enabled'     => '/\#\s+Browser\s+Caching/si',
		'disabled'    => '/\#\s+Browser\s+Caching(.+?)\#\s+Browser\s+Caching\s+END(\n)?/ims',
		'disable_all' => '/\#\s+Browser\s+Caching(.+?)\#\s+Browser\s+Caching\s+END(\n)?/ims',
	];
}
