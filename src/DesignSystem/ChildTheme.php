<?php

declare( strict_types=1 );

namespace Blockify\Extensions\DesignSystem;

use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\Styles;
use Blockify\Core\Utilities\Str;
use function file_exists;
use function file_get_contents;
use function get_stylesheet_directory;
use function str_replace;
use function trim;

/**
 * Child theme.
 *
 * @since 0.9.23
 */
class ChildTheme implements Styleable {

	/**
	 * Adds child theme style.css to inline styles.
	 *
	 * @since 0.9.23
	 *
	 * @param Styles $styles Styles service.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$child       = get_stylesheet_directory() . '/style.css';
		$file_exists = file_exists( $child );

		if ( ! $file_exists ) {
			return;
		}

		$content = trim( file_get_contents( $child ) );
		$css     = str_replace(
			Str::between( '/**', '*/', $content ),
			'',
			$content
		);

		$styles->add()
			->handle( 'child-theme' )
			->inline_css( static fn(): string => $css );
	}
}
