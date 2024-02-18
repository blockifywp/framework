<?php

declare( strict_types=1 );

namespace Blockify\Framework\CoreBlocks;

use Blockify\Framework\BlockSettings\Responsive;
use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Icon;
use Blockify\Utilities\Interfaces\Renderable;
use Blockify\Utilities\JS;
use Blockify\Utilities\Str;
use WP_Block;
use function array_unique;
use function esc_attr;
use function esc_url;
use function explode;
use function implode;
use function in_array;
use function str_contains;
use function str_replace;
use function wp_get_global_settings;

/**
 * Button block.
 *
 * @since 0.0.2
 */
class Button implements Renderable {

	/**
	 * Responsive settings.
	 *
	 * @var array
	 */
	private array $responsive_settings;

	/**
	 * Constructor.
	 *
	 * @param Responsive $responsive Block settings.
	 *
	 * @return void
	 */
	public function __construct( Responsive $responsive ) {
		$this->responsive_settings = $responsive->settings;
	}

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block         The full block, including name and attributes.
	 * @param WP_Block $instance      The block instance.
	 *
	 * @hook  render_block 9
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$block_name = $block['blockName'] ?? null;

		// Using render_block for earlier priority.
		if ( 'core/button' !== $block_name ) {
			return $block_content;
		}

		if ( str_contains( $block_content, 'is-style-outline' ) ) {
			$dom    = DOM::create( $block_content );
			$div    = DOM::get_element( 'div', $dom );
			$anchor = DOM::get_element( 'a', $div );

			if ( $anchor ) {
				$classes = explode( ' ', $anchor->getAttribute( 'class' ) );
				$anchor->setAttribute(
					'class',
					implode(
						' ',
						[
							...$classes,
							'wp-element-button',
						]
					)
				);

				$block_content = $dom->saveHTML();
			}
		}

		if ( str_contains( $block_content, '-border-' ) ) {
			$global_settings = wp_get_global_settings();
			$dom             = DOM::create( $block_content );
			$div             = DOM::get_element( 'div', $dom );
			$link            = DOM::get_element( 'a', $dom );

			if ( ! $div ) {
				$div = DOM::create_element( 'div', $dom );
			}

			if ( ! $link ) {
				$link = DOM::create_element( 'a', $dom );
			}

			$classes     = explode( ' ', $div->getAttribute( 'class' ) );
			$styles      = explode( ';', $div->getAttribute( 'style' ) );
			$div_classes = [];
			$div_styles  = [];

			foreach ( $classes as $class ) {
				if ( ! str_contains( $class, '-border-' ) ) {
					$div_classes[] = $class;
				}
			}

			foreach ( $styles as $style ) {
				if ( ! str_contains( $style, 'border-' ) ) {
					$div_styles[] = $style;
				}
			}

			$border_width = $block['attrs']['style']['border']['width'] ?? null;
			$border_color = $block['attrs']['style']['border']['color'] ?? null;

			$link_styles = CSS::string_to_array( $link->getAttribute( 'style' ) );

			if ( $border_width || $border_color ) {
				$border_width = $border_width ?? $global_settings['custom']['border']['width'];

				$link_styles['line-height'] = "calc(1em - $border_width)";
			}

			$link->setAttribute( 'style', CSS::array_to_string( $link_styles ) );
			$div->setAttribute( 'class', implode( ' ', $div_classes ) );
			$div->setAttribute( 'style', implode( ';', $div_styles ) );

			if ( ! $div->getAttribute( 'style' ) ) {
				$div->removeAttribute( 'style' );
			}

			$block_content = $dom->saveHTML();
		}

		$icon_set  = $block['attrs']['iconSet'] ?? '';
		$icon_name = $block['attrs']['iconName'] ?? '';
		$icon      = $icon_set && $icon_name ? Icon::get_svg( $icon_set, $icon_name ) : '';

		if ( $icon ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );

			if ( ! $div ) {
				$div   = DOM::create_element( 'div', $dom );
				$div   = DOM::node_to_element( $dom->appendChild( $div ) );
				$class = esc_attr( $block['attrs']['className'] ?? '' );

				$div->setAttribute( 'class', 'wp-block-button ' . $class );
			}

			$div_styles = CSS::string_to_array( $div->getAttribute( 'style' ) );

			foreach ( $div_styles as $key => $style ) {
				if ( str_contains( $key, '--wp--custom--icon--' ) ) {
					unset( $div_styles[ $key ] );
				}
			}

			$div->setAttribute( 'style', CSS::array_to_string( $div_styles ) );

			$a = DOM::get_element( 'a', $div );

			if ( ! $a ) {
				$a = DOM::create_element( 'a', $dom );

				$a->setAttribute( 'class', 'wp-block-button__link wp-element-button' );

				$a = DOM::node_to_element( $div->appendChild( $a ) );
			}

			$svg_dom  = DOM::create( $icon );
			$svg      = DOM::get_element( 'svg', $svg_dom );
			$imported = DOM::node_to_element( $dom->importNode( $svg, true ) );
			$gap      = $block['attrs']['style']['spacing']['blockGap'] ?? null;
			$classes  = explode( ' ', $a->getAttribute( 'class' ) );
			$styles   = CSS::string_to_array( $a->getAttribute( 'style' ) );

			if ( $gap ) {
				$styles['gap'] = CSS::format_custom_property( $gap );
			}

			$padding = $block['attrs']['style']['spacing']['padding'] ?? [];
			$styles  = CSS::add_shorthand_property( $styles, 'padding', $padding );

			$text_color = $block['attrs']['textColor'] ?? null;

			if ( $text_color ) {
				$styles['color'] = CSS::format_custom_property( $text_color );
			}

			$background_color = $block['attrs']['backgroundColor'] ?? null;

			if ( $background_color ) {
				$styles['background-color'] = CSS::format_custom_property( $background_color );
				$classes[]                  = 'has-background';
			}

			$border_width = $block['attrs']['style']['border']['width'] ?? null;
			$border_style = $block['attrs']['style']['border']['style'] ?? null;
			$border_color = $block['attrs']['style']['border']['color'] ?? null;

			if ( $border_width ) {
				$styles['border-width'] = CSS::format_custom_property( $border_width );
			}

			if ( $border_style ) {
				$styles['border-style'] = CSS::format_custom_property( $border_style );
			}

			if ( $border_color ) {
				$styles['border-color'] = CSS::format_custom_property( $border_color );
			}

			if ( $styles ) {
				$a->setAttribute( 'style', CSS::array_to_string( $styles ) );
			}

			$a->setAttribute( 'class', implode( ' ', array_unique( $classes ) ) );

			$on_click = $block['attrs']['onclick'] ?? null;

			if ( $on_click ) {
				$a->setAttribute( 'onclick', JS::format_inline_js( $on_click ) );
			}

			$url = $block['attrs']['url'] ?? $a->getAttribute( 'href' );

			if ( ! $url ) {
				if ( ! $on_click ) {
					$a->setAttribute( 'href', '#' );
				} else {
					$a->setAttribute( 'href', 'javascript:void(0)' );
				}
			}

			$size = esc_attr( $block['attrs']['iconSize'] ?? null ) ?: '20';

			if ( str_contains( $size, 'var' ) ) {
				$svg_styles = CSS::string_to_array( $svg->getAttribute( 'style' ) );

				unset ( $svg_styles['enable-background'] );

				$svg_styles['height'] = CSS::format_custom_property( $size );
				$svg_styles['width']  = CSS::format_custom_property( $size );

				$imported->setAttribute( 'style', CSS::array_to_string( $svg_styles ) );

			} else {
				$imported->setAttribute( 'height', $size );
				$imported->setAttribute( 'width', $size );
			}

			$fill = $imported->getAttribute( 'fill' );

			if ( ! $fill ) {
				$imported->setAttribute( 'fill', 'currentColor' );
			}

			$icon_position = $block['attrs']['iconPosition'] ?? 'end';

			if ( $icon_position === 'start' ) {
				$svg = $a->insertBefore( $imported, $a->firstChild );
			} else {
				$svg = $a->appendChild( $imported );
			}

			$title = $svg->insertBefore(
				DOM::create_element( 'title', $dom ),
				$svg->firstChild
			);

			$text = $dom->createTextNode( Str::title_case( $block['attrs']['iconName'] ?? '' ) );

			$title->appendChild( $text );

			$block_content = CSS::add_responsive_classes(
				$dom->saveHTML(),
				$block,
				$this->responsive_settings
			);
		}

		$url = esc_url( $block['attrs']['url'] ?? '' );

		if ( ! $url ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );
			$a   = DOM::get_element( 'a', $div );

			if ( $a ) {
				$href = $a->getAttribute( 'href' );

				if ( $href ) {
					$a->setAttribute( 'href', $href );
				} else {

					$on_click = $block['attrs']['onclick'] ?? null;

					if ( ! $on_click ) {
						$a->setAttribute( 'href', '#' );
					} else {
						$a->setAttribute( 'href', 'javascript:void(0)' );
					}
				}
			}

			$block_content = $dom->saveHTML();
		}

		$size = esc_attr( $block['attrs']['size'] ?? 'medium' );

		if ( in_array( $size, [ 'small', 'large' ] ) ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );

			if ( $div ) {
				$div_classes   = explode( ' ', $div->getAttribute( 'class' ) );
				$div_classes[] = "is-style-$size";
				$div->setAttribute( 'class', implode( ' ', $div_classes ) );
			}

			$block_content = $dom->saveHTML();
		}

		$inner_html = $block['innerHTML'] ?? $block['innerContent'] ?? $block_content;
		$back_urls  = [
			'javascript:history.go(-1)',
			'javascript: history.go(-1)',
		];

		foreach ( $back_urls as $back_url ) {
			if ( str_contains( $inner_html, $back_url ) ) {
				$block_content = str_replace( 'href="#"', 'href="' . $back_url . '"', $block_content );
			}
		}

		if ( str_contains( $block_content, 'javascript:void' ) ) {
			$block_content = str_replace(
				[
					'http://javascript:void',
					'target="_blank"',
				],
				[
					'javascript:void',
					'disabled',
				],
				$block_content
			);
		}

		if ( str_contains( $block_content, 'href="http://#"' ) ) {
			$block_content = str_replace(
				[
					'href="http://#"',
					'target="_blank"',
				],
				[
					'href="#"',
					'',
				],
				$block_content
			);
		}

		if ( str_contains( $block_content, 'http://http' ) ) {
			$block_content = str_replace( 'http://http', 'http', $block_content );
		}

		return $block_content;
	}
}
