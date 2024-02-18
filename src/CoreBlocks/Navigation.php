<?php

declare( strict_types=1 );

namespace Blockify\Framework\CoreBlocks;

use Blockify\Framework\InlineAssets\Styleable;
use Blockify\Framework\InlineAssets\Styles;
use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function array_keys;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function str_contains;
use function str_replace;
use function trim;
use function wp_get_global_settings;
use function wp_get_global_styles;
use function wp_list_pluck;

/**
 * Navigation class.
 *
 * @since 1.0.0
 */
class Navigation implements Renderable, Styleable {

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/navigation
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {

		// Replace invalid root relative URLs.
		$block_content = str_replace( 'http://./', './', $block_content );
		$dom           = DOM::create( $block_content );
		$nav           = DOM::get_element( 'nav', $dom );

		if ( ! $nav ) {
			return $block_content;
		}

		$styles       = CSS::string_to_array( $nav->getAttribute( 'style' ) );
		$classes      = explode( ' ', $nav->getAttribute( 'class' ) );
		$overlay_menu = $block['attrs']['overlayMenu'] ?? true;
		$filter       = $block['attrs']['style']['filter'] ?? null;

		if ( $overlay_menu && $filter ) {
			$filter_value = '';

			foreach ( $filter as $property => $value ) {
				if ( $property === 'backdrop' ) {
					continue;
				}

				$value = CSS::format_custom_property( $value ) . 'px';

				$filter_value .= "$property($value) ";
			}

			$styles['--wp--custom--nav--filter'] = trim( $filter_value );

			$background_color = $block['attrs']['backgroundColor'] ?? $block['attrs']['style']['color']['background'] ?? '';

			$global_settings = wp_get_global_settings();
			$color_slugs     = wp_list_pluck( $global_settings['color']['palette']['theme'] ?? [], 'slug' );

			if ( in_array( $background_color, $color_slugs, true ) ) {
				$background_color = "var(--wp--preset--color--{$background_color})";
			}

			if ( $background_color ) {
				$styles['--wp--custom--nav--background-color'] = CSS::format_custom_property( $background_color );
			}

			$nav->setAttribute( 'style', CSS::array_to_string( $styles ) );

			if ( $filter['backdrop'] ?? null ) {
				$classes[] = 'has-backdrop-filter';
			}

			$nav->setAttribute( 'class', implode( ' ', $classes ) );

			$block_content = $dom->saveHTML();
		}

		$spacing = $block['attrs']['style']['spacing'] ?? null;

		if ( ! $spacing ) {
			return $block_content;
		}

		$padding = $spacing['padding'] ?? null;

		unset( $spacing['padding'] );

		foreach ( array_keys( $spacing ) as $attribute ) {
			$prop = $attribute === 'blockGap' ? 'gap' : $attribute;

			if ( is_string( $spacing[ $attribute ] ) ) {
				$styles[ $prop ] = CSS::format_custom_property( $spacing[ $attribute ] );
			}

			if ( is_array( $spacing[ $attribute ] ) ) {
				foreach ( array_keys( $spacing[ $attribute ] ) as $side ) {
					$styles["$prop-$side"] = CSS::format_custom_property( $spacing[ $attribute ][ $side ] );
				}
			}
		}

		$styles = CSS::add_shorthand_property( $styles, '--wp--custom--nav--padding', $padding );

		if ( $styles ) {
			$nav->setAttribute( 'style', CSS::array_to_string( $styles ) );
		}

		$buttons = DOM::get_elements_by_class_name( 'wp-block-navigation-submenu__toggle', $dom );

		foreach ( $buttons as $button ) {
			$span = $button->nextSibling;

			if ( ! $span || $span->tagName !== 'span' ) {
				continue;
			}

			$span->parentNode->removeChild( $span );
			$button->appendChild( $span );
		}

		return $dom->saveHTML();
	}

	/**
	 * Adds CSS for submenu borders.
	 *
	 * @since 0.0.2
	 *
	 * @param Styles $styles Styles instance.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$styles->add_file( 'core-blocks/navigation.css', [ 'wp-block-navigation__submenu-container' ] );
		$styles->add_callback( [ $this, 'get_submenu_styles' ] );
	}

	/**
	 * Returns submenu styles.
	 *
	 * @param string $template_html Template HTML.
	 * @param bool   $load_all      Load all styles.
	 *
	 * @return string
	 */
	public function get_submenu_styles( string $template_html, bool $load_all ): string {
		if ( ! $load_all && ! str_contains( $template_html, 'wp-block-navigation__submenu-container' ) ) {
			return '';
		}

		$global_styles = wp_get_global_styles();
		$border        = $global_styles['blocks']['core/navigation-submenu']['border'] ?? [];
		$styles        = [];

		foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
			if ( ! isset( $border[ $side ] ) ) {
				continue;
			}

			if ( $border[ $side ]['width'] ?? '' ) {
				$styles["border-$side-width"] = $border[ $side ]['width'];
			}

			if ( $border[ $side ]['style'] ?? '' ) {
				$styles["border-$side-style"] = $border[ $side ]['style'];
			}

			if ( $border[ $side ]['color'] ?? '' ) {
				$styles["border-$side-color"] = CSS::format_custom_property( $border[ $side ]['color'] );
			}
		}

		$radius = $border['radius'] ?? null;

		if ( $radius ) {
			$styles['border-radius'] = CSS::format_custom_property( $radius );
		}

		$css = '';

		if ( $styles ) {
			$css = '.wp-block-navigation-submenu{border:0}.wp-block-navigation .wp-block-navigation-item .wp-block-navigation__submenu-container{' . CSS::array_to_string( $styles ) . '}';
		}

		return $css;
	}

}
