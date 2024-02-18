<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use Blockify\Framework\InlineAssets\Styles;
use function array_flip;
use function file_get_contents;
use function is_a;
use function str_replace;
use function str_starts_with;
use function trim;
use function wp_add_inline_style;

/**
 * Block CSS.
 *
 * @since 0.9.19
 */
class BlockCss {

	/**
	 * CSS dir.
	 *
	 * @var string
	 */
	private string $css_dir;

	/**
	 * BlockCss constructor.
	 *
	 * @since 0.9.19
	 *
	 * @param Styles $styles Package config.
	 *
	 * @return void
	 */
	public function __construct( Styles $styles ) {
		$this->css_dir = $styles->dir;
	}

	/**
	 * Adds conditional block styles.
	 *
	 * Uses wp_add_inline_style instead of wp_enqueue_block_style for less output.
	 *
	 * @since 0.9.19
	 *
	 * @hook  enqueue_block_assets 11
	 *
	 * @return void
	 */
	public function add_block_styles(): void {
		global $wp_styles;

		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			return;
		}

		$dir     = $this->css_dir . 'core-blocks/';
		$handles = array_flip( $wp_styles->queue );

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! isset( $handles[ $handle ] ) ) {
				continue;
			}

			if ( ! str_starts_with( $handle, 'wp-block-' ) ) {
				continue;
			}

			$slug = str_replace( 'wp-block-', '', $handle );
			$file = $dir . $slug . '.css';

			if ( ! file_exists( $file ) ) {
				continue;
			}

			wp_add_inline_style(
				$handle,
				trim( file_get_contents( $file ) )
			);
		}
	}
}

