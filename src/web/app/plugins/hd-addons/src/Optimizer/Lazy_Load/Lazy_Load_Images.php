<?php

namespace Addons\Optimizer\Lazy_Load;

\defined( 'ABSPATH' ) || die;

class Lazy_Load_Images extends Abstract_Lazy_Load {

	/**
	 * Regex parts for checking content
	 *
	 * @var string
	 */
	public string $regexp = '/<img[\s\r\n]+.*?>/is';

	/**
	 * Regex for already replaced items
	 *
	 * @var string
	 */
	public string $regex_replaced = "/src=['\"]data:image/is";

	/**
	 * Replace patterns.
	 *
	 * @var array
	 */
	public array $patterns = [
		'/(?<!noscript\>)((<img.*?src=["|\'].*?["|\']).*?(\/?>))/i',
		'/(?<!noscript\>)(<img.*?)(src)=["|\']((?!data).*?)["|\']/i',
		'/(?<!noscript\>)(<img.*?)((srcset)=["|\'](.*?)["|\'])/i',
	];

	/**
	 * Replacements.
	 *
	 * @var array
	 */
	public array $replacements = [
		'$1<noscript>$1</noscript>',
		'$1src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-$2="$3"',
		'$1data-$3="$4"',
	];

	/**
	 * @param $element
	 *
	 * @return string|array
	 */
	public function add_lazyload_class( $element ): string|array {
		return str_replace( '<img', '<img class="lazy"', $element );
	}
}
