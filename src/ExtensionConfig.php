<?php

declare( strict_types=1 );

namespace Blockify\Extensions;

use Blockify\Core\Services\Data;

/**
 * Extensions service provider.
 *
 * @since 1.0.0
 */
class ExtensionConfig {

	/**
	 * Extensions directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public string $dir;

	/**
	 * Extensions URL.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public string $url;

	/**
	 * Extensions CSS directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public string $css_dir;

	/**
	 * Extensions constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Theme or plugin data.
	 *
	 * @return void
	 */
	public function __construct( Data $data ) {
		$this->dir     = $data->dir . 'vendor/blockify/extensions/';
		$this->url     = $data->url . 'vendor/blockify/extensions/';
		$this->css_dir = $this->dir . 'public/css/';
	}
}
