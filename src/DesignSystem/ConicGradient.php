<?php

declare( strict_types=1 );

namespace Blockify\Extensions\DesignSystem;

use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\Styles;
use Blockify\Core\Utilities\CSS;
use function str_contains;
use function str_replace;
use function wp_get_global_settings;

class ConicGradient implements Styleable {

	/**
	 * Converts custom linear or radial gradient into conic gradient.
	 *
	 * @since 0.9.10
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

		$styles->add()
			->inline_css( static fn(): string => $css )
			->condition( static fn( string $template_html ): bool => str_contains( $template_html, 'custom-conic-' ) );
	}

}
