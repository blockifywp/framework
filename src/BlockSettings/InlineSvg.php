<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function array_diff;
use function content_url;
use function dirname;
use function esc_attr;
use function explode;
use function file_exists;
use function file_get_contents;
use function get_template_directory;
use function implode;
use function in_array;
use function method_exists;
use function str_contains;
use function str_replace;
use function urldecode;

/**
 * InlineSvg class.
 *
 * @since 1.5.0
 */
class InlineSvg implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Renders inline SVGs in rich text content.
	 *
	 * @since 0.9.10
	 *
	 * @param string   $block_content Block html content.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		if ( ! str_contains( $block_content, 'has-inline-svg' ) ) {
			return $block_content;
		}

		$dom   = DOM::create( $block_content );
		$first = DOM::get_element( '*', $dom );

		if ( ! $first ) {
			return $block_content;
		}

		$images = $dom->getElementsByTagName( 'img' );

		if ( ! $images->length ) {
			return $block_content;
		}

		foreach ( $images as $index => $img ) {
			$style = CSS::string_to_array( $img->getAttribute( 'style' ) );
			$mask  = $style['-webkit-mask-image'] ?? '';

			if ( ! $mask ) {
				continue;
			}

			$svg_string  = str_replace( [ "url('data:image/svg+xml;utf8,", "')" ], [ '', '' ], $mask );
			$svg_string  = urldecode( $svg_string );
			$svg_dom     = DOM::create( $svg_string );
			$svg_element = DOM::get_element( 'svg', $svg_dom );

			if ( ! $svg_element ) {
				return $block_content;
			}

			$imported = $dom->importNode( $svg_element, true );
			$imported = DOM::node_to_element( $imported );
			$imported->removeAttribute( 'height' );
			$imported->removeAttribute( 'width' );

			foreach ( $img->attributes as $attribute ) {
				if ( $attribute->name === 'style' ) {
					$style = CSS::string_to_array( $img->getAttribute( 'style' ) );
					unset( $style['-webkit-mask-image'] );
					$imported->setAttribute( 'style', CSS::array_to_string( $style ) );
					continue;
				}

				$imported->setAttribute(
					esc_attr( $attribute->name ),
					esc_attr( $attribute->value )
				);
			}

			$imported->setAttribute( 'fill', 'currentColor' );

			$classes = explode( ' ', $img->getAttribute( 'class' ) );
			$classes = array_diff( $classes, [ 'has-inline-svg' ] );

			$classes[] = 'inline-svg';

			$imported->setAttribute( 'class', implode( ' ', $classes ) . ' ' . $svg_element->getAttribute( 'class' ) );

			$block_content = str_replace(
				$dom->saveHTML( $img ),
				$dom->saveHTML( $imported ),
				$block_content
			);
		}

		return $block_content;
	}

	/**
	 * Converts image asset to inline SVG.
	 *
	 * @since 1.5.0
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block
	 *
	 * @return string
	 */
	public function render_inline_svg( string $block_content, array $block, WP_Block $instance ): string {
		$blocks = [
			'core/image',
			'core/site-logo',
			'core/post-featured-image',
		];

		$name = $block['blockName'] ?? '';

		if ( ! in_array( $name, $blocks, true ) ) {
			return $block_content;
		}

		if ( ! str_contains( $block_content, '.svg' ) ) {
			return $block_content;
		}

		$attrs  = $block['attrs'] ?? [];
		$dom    = DOM::create( $block_content );
		$div    = DOM::get_element( 'div', $dom );
		$figure = DOM::get_element( 'figure', $dom );
		$first  = $div ?? $figure ?? null;
		$link   = DOM::get_element( 'a', $first );
		$img    = DOM::get_element( 'img', $link ?? $first );

		if ( ! $img ) {
			return $block_content;
		}

		$file = str_replace(
			content_url(),
			dirname( get_template_directory(), 2 ),
			$img->getAttribute( 'src' )
		);

		if ( ! file_exists( $file ) ) {
			return $block_content;
		}

		$svg = $dom->importNode( DOM::create( file_get_contents( $file ) )->documentElement, true );

		if ( ! method_exists( $svg, 'setAttribute' ) ) {
			return $block_content;
		}

		$width  = $attrs['width'] ?? $img->getAttribute( 'width' );
		$height = $attrs['height'] ?? $img->getAttribute( 'height' );
		$alt    = $attrs['alt'] ?? $img->getAttribute( 'alt' );

		if ( $width ) {
			$svg->setAttribute( 'width', str_replace( 'px', '', (string) $width ) );
		}

		if ( $height ) {
			$svg->setAttribute( 'height', str_replace( 'px', '', (string) $height ) );
		}

		if ( $alt ) {
			$svg->setAttribute( 'aria-label', $alt );
		}

		$svg->setAttribute( 'class', $img->getAttribute( 'class' ) );

		( $link ?? $first )->removeChild( $img );
		( $link ?? $first )->appendChild( $svg );

		$first_classes = explode( ' ', $first->getAttribute( 'class' ) );

		$first_classes[] = 'has-inlined-svg';

		$first->setAttribute( 'class', implode( ' ', $first_classes ) );

		return $dom->saveHTML();
	}

}
