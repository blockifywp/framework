<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use Blockify\Framework\InlineAssets\Styleable;
use Blockify\Framework\InlineAssets\Styles;
use Blockify\Utilities\CSS;
use function str_contains;
use function str_replace;
use function wp_get_global_settings;

/**
 * Conic gradient.
 *
 * @since 1.0.0
 */
class ConicGradient implements Styleable {

	/**
	 * Converts custom linear or radial gradient into conic gradient.
	 *
	 * @since 1.0.0
	 *
	 * @param Styles $styles Styles.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$settings  = wp_get_global_settings();
		$gradients = $settings['color']['gradients']['custom'] ?? [];
		$css       = [];

		foreach ( $gradients as $gradient ) {
			$slug = $gradient['slug'] ?? '';

			if ( ! str_contains( $slug, 'custom-conic-' ) ) {
				continue;
			}

			$value = str_replace(
				'linear-gradient(',
				'conic-gradient(from ',
				$gradient['gradient']
			);

			$css[ '--wp--preset--gradient--' . $slug ] = $value;
		}

		$css = 'body{' . CSS::array_to_string( $css ) . '}';

		$styles->add_string( $css, [ 'custom-conic-' ] );
	}

}
