<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Configurable;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Interfaces\Scriptable;
use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\AbstractAssets;
use Blockify\Core\Services\Assets\Scripts;
use Blockify\Core\Services\Assets\Styles;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Extensions\ExtensionsConfig;
use WP_Block;
use function _wp_to_kebab_case;
use function array_merge;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Responsive class.
 *
 * @since 1.0.0
 */
class Responsive implements Hookable, Renderable, Scriptable, Styleable, Configurable {

	use HookAnnotations;
	use ExtensionsConfig;

	/**
	 * Adds inline block positioning classes.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $block_content Block content.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$style = $block['attrs']['style'] ?? [];

		if ( ! $style ) {
			return $block_content;
		}

		$options = $this->config['block_settings']['responsive'];

		$block_content = CSS::add_responsive_classes(
			$block_content,
			$block,
			$options
		);

		return CSS::add_responsive_styles(
			$block_content,
			$block,
			$options
		);
	}

	/**
	 * Add default block supports.
	 *
	 * @since 0.9.10
	 *
	 * @param Scripts $scripts Scripts service.
	 *
	 * @return void
	 */
	public function scripts( Scripts $scripts ): void {
		$scripts->add()
			->handle( 'editor' )
			->localize( [
				$scripts->prefix,
				[
					'responsiveOptions' => $this->config['block_settings']['responsive'],
					'imageOptions'      => $this->config['block_settings']['image'],
					'filterOptions'     => $this->config['block_settings']['filter'],
				],
			] )
			->context( [ AbstractAssets::EDITOR ] );

	}

	/**
	 * Conditionally adds CSS for utility classes
	 *
	 * @since 0.9.19
	 *
	 * @param Styles $styles Styles service.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$instance = $this;

		$styles->add()
			->handle( $styles->prefix )
			->inline_css( static function ( string $template_html ) use ( $instance ): string {
				return $instance->get_styles( $template_html );
			} );

	}

	/**
	 * Returns inline styles for responsive classes.
	 *
	 * @since 0.9.19
	 *
	 * @param string $template_html Template HTML.
	 *
	 * @return string
	 */
	public function get_styles( string $template_html ): string {
		$options = array_merge(
			$this->config['block_settings']['responsive'],
			$this->config['block_settings']['image'],
		);
		$both    = '';
		$mobile  = '';
		$desktop = '';

		foreach ( $options as $key => $args ) {
			$property       = _wp_to_kebab_case( $key );
			$select_options = $args['options'] ?? [];

			foreach ( $select_options as $option ) {
				$value = $option['value'] ?? '';

				if ( ! $value ) {
					continue;
				}

				$formatted_value = $value;

				if ( 'aspect-ratio' === $property ) {
					$formatted_value = str_replace( '/', '\/', $formatted_value );
				}

				if ( ! $template_html || str_contains( $template_html, " has-{$property}-{$value}" ) ) {
					$both .= sprintf(
						'.has-%1$s-%3$s{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}

				if ( ! $template_html || str_contains( $template_html, " has-{$property}-{$value}-mobile" ) ) {
					$mobile .= sprintf(
						'.has-%1$s-%3$s-mobile{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}

				if ( ! $template_html || str_contains( $template_html, " has-{$property}-{$value}-desktop" ) ) {
					$desktop .= sprintf(
						'.has-%1$s-%3$s-desktop{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}
			}

			// Has custom value.
			if ( ! $select_options ) {

				if ( ! $template_html || str_contains( $template_html, " has-$property" ) ) {
					$both .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s)}',
						$property
					);
				}

				if ( ! $template_html || str_contains( $template_html, "--$property-mobile" ) ) {
					$mobile .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s-mobile,var(--%1$s))}',
						$property
					);
				}

				if ( ! $template_html || str_contains( $template_html, "--$property-desktop" ) ) {
					$desktop .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s-desktop,var(--%1$s))}',
						$property
					);
				}
			}
		}

		$css = '';

		if ( $both ) {
			$css .= $both;
		}

		if ( $mobile ) {
			$css .= sprintf( '@media(max-width:781px){%s}', $mobile );
		}

		if ( $desktop ) {
			$css .= sprintf( '@media(min-width:782px){%s}', $desktop );
		}

		return $css;
	}

}
