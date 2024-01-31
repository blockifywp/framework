<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\Styles;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use Blockify\Core\Utilities\Str;
use DOMDocument;
use DOMElement;
use WP_Block;
use function apply_filters;
use function array_merge;
use function esc_attr;
use function esc_html__;
use function esc_url;
use function explode;
use function implode;
use function in_array;
use function is_archive;
use function property_exists;
use function str_contains;
use function str_replace;

/**
 * Placeholder class.
 *
 * @since 1.0.0
 */
class Placeholder implements Hookable, Renderable, Styleable {

	use HookAnnotations;

	/**
	 * Conditionally adds placeholder styles.
	 *
	 * @since 0.9.10
	 *
	 * @param Styles $styles Styles instance.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$styles->add()
			->handle( 'placeholder' )
			->src( 'block-extensions/placeholder-image.css' )
			->condition( static fn( string $template_html ): bool => str_contains( $template_html, 'is-placeholder' ) || is_archive() );
	}

	/**
	 * Returns placeholder HTML element string.
	 *
	 * @since 0.9.10
	 *
	 * @param string   $block_content Block content.
	 * @param array    $block         Block attributes.
	 * @param WP_Block $instance      Block object.
	 *
	 * @hook  render_block 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$attrs           = $block['attrs'] ?? [];
		$id              = $attrs['id'] ?? '';
		$has_icon        = ( $attrs['iconSet'] ?? '' ) && ( $attrs['iconName'] ?? '' ) || ( $attrs['iconSvgString'] ?? '' );
		$style           = $attrs['style'] ?? [];
		$has_svg         = $style['svgString'] ?? '';
		$use_placeholder = $attrs['usePlaceholder'] ?? true;

		if ( $id || $has_icon || $has_svg ) {
			return $block_content;
		}

		if ( ! $use_placeholder || $use_placeholder === 'none' ) {
			return $block_content;
		}

		if ( Str::contains_any( $block_content, 'is-style-icon', 'is-style-svg' ) ) {
			return $block_content;
		}

		$dom    = DOM::create( $block_content );
		$figure = DOM::get_element( 'figure', $dom );
		$img    = DOM::get_element( 'img', $figure );
		$link   = DOM::get_element( 'a', $figure );
		$svg    = DOM::get_element( 'svg', $link ?? $figure );

		if ( $svg ) {
			return $block_content;
		}

		if ( $img && $img->getAttribute( 'src' ) ) {
			return $block_content;
		}

		$block_name = str_replace(
			'core/',
			'',
			$block['blockName'] ?? ''
		);

		$block_content = $block_content ?: "<figure class='wp-block-{$block_name}'></figure>";
		$dom           = DOM::create( $block_content );
		$figure        = DOM::get_element( 'figure', $dom );

		if ( ! $figure ) {
			return $block_content;
		}

		$img = DOM::get_element( 'img', $figure );

		if ( $img ) {
			$figure->removeChild( $img );
		}

		$classes = explode( ' ', $figure->getAttribute( 'class' ) );

		if ( ! in_array( 'is-placeholder', $classes, true ) ) {
			$classes[] = 'is-placeholder';
		}

		if ( $block['align'] ?? null ) {
			$classes[] = 'align' . $block['align'];
		}

		$is_link     = $block['attrs']['isLink'] ?? false;
		$placeholder = $this->get_placeholder_icon( $dom );

		if ( $placeholder->tagName === 'svg' ) {
			$classes[] = 'has-placeholder-icon';
		}

		if ( $is_link ) {
			$context = (object) ( property_exists( $instance, 'context' ) ? $instance->context : null );
			$link    = DOM::create_element( 'a', $dom );
			$id_key  = 'postId';

			if ( property_exists( $context, $id_key ) ) {
				$post_id = $context->$id_key ?? null;
				$href    = get_permalink( $post_id );

				if ( $href ) {
					$link->setAttribute( 'href', esc_url( $href ) );
				}
			}

			$link_target = $block['linkTarget'] ?? '';

			if ( $link_target ) {
				$link->setAttribute( 'target', $link_target );
			}

			$rel = esc_attr( $block['rel'] ?? '' );

			if ( $rel ) {
				$link->setAttribute( 'rel', $rel );
			}

			$link_classes = explode( ' ', $link->getAttribute( 'class' ) );

			if ( ! in_array( 'wp-block-image__link', $link_classes, true ) ) {
				$link_classes[] = 'wp-block-image__link';
			}

			if ( ! in_array( 'is-placeholder', $classes, true ) && ! in_array( 'is-placeholder', $link_classes, true ) ) {
				$link_classes[] = 'is-placeholder';
			}

			$link->setAttribute(
				'class',
				implode( ' ', $link_classes )
			);
			$link->appendChild( $placeholder );
			$figure->appendChild( $link );
		} else {
			$figure->appendChild( $placeholder );
		}

		$style            = $block['attrs']['style'] ?? [];
		$spacing          = $style['spacing'] ?? [];
		$margin           = $spacing['margin'] ?? [];
		$padding          = $spacing['padding'] ?? [];
		$border           = $style['border'] ?? [];
		$radius           = $border['radius'] ?? [];
		$aspect_ratio     = $block['attrs']['aspectRatio'] ?? null;
		$background_color = $block['attrs']['backgroundColor'] ?? null;

		$styles = [
			'width'                      => $block['width'] ?? null,
			'height'                     => $block['height'] ?? null,
			'border-width'               => $border['width'] ?? null,
			'border-style'               => $border['style'] ?? ( ( $border['width'] ?? null ) ? 'solid' : null ),
			'border-color'               => $border['color'] ?? null,
			'border-top-left-radius'     => $radius['topLeft'] ?? null,
			'border-top-right-radius'    => $radius['topRight'] ?? null,
			'border-bottom-left-radius'  => $radius['bottomLeft'] ?? null,
			'border-bottom-right-radius' => $radius['bottomRight'] ?? null,
			'position'                   => $style['position']['all'] ?? null,
			'top'                        => $style['top']['all'] ?? null,
			'right'                      => $style['right']['all'] ?? null,
			'bottom'                     => $style['bottom']['all'] ?? null,
			'left'                       => $style['left']['all'] ?? null,
			'z-index'                    => $style['zIndex']['all'] ?? null,
		];

		$styles = CSS::add_shorthand_property( $styles, 'margin', $margin );
		$styles = CSS::add_shorthand_property( $styles, 'padding', $padding );

		if ( $aspect_ratio && $aspect_ratio !== 'auto' ) {
			$styles['aspect-ratio'] = $aspect_ratio;
		}

		if ( $background_color === 'transparent' ) {
			$classes[] = 'has-transparent-background-color';
		} else {
			$styles['background-color'] = $background_color;
		}

		$css = CSS::array_to_string(
			array_merge(
				CSS::string_to_array(
					$figure->getAttribute( 'style' )
				),
				$styles,
			)
		);

		if ( $css ) {
			$figure->setAttribute( 'style', $css );
		}

		$figure->setAttribute( 'class', implode( ' ', $classes ) );

		return $dom->saveHTML();
	}

	/**
	 * Returns placeholder icon element.
	 *
	 * @param DOMDocument $dom DOM document.
	 *
	 * @return DOMElement
	 */
	public function get_placeholder_icon( DOMDocument $dom ): DOMElement {

		$svg_title = esc_html__( 'Image placeholder', 'blockify' );
		$svg_icon  = <<<HTML
<svg xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 64 64" width="32" height="32">
	<title>$svg_title</title>
	<circle cx="52" cy="18" r="7"/>
	<path d="M47 32.1 39 41 23 20.9 0 55.1h64z"/>
</svg>
HTML;

		/**
		 * Filters the SVG icon for the placeholder image.
		 *
		 * @since 1.3.0
		 *
		 * @param string $svg_icon  SVG icon.
		 * @param string $svg_title SVG title.
		 */
		$svg_icon    = apply_filters( 'blockify_placeholder_svg', $svg_icon, $svg_title );
		$svg_dom     = DOM::create( $svg_icon );
		$svg_element = DOM::get_element( 'svg', $svg_dom );

		if ( ! $svg_element ) {
			return DOM::create_element( 'span', $dom );
		}

		$svg_classes   = explode( ' ', $svg_element->getAttribute( 'class' ) );
		$svg_classes[] = 'wp-block-image__placeholder-icon';

		$svg_element->setAttribute( 'class', implode( ' ', $svg_classes ) );
		$svg_element->setAttribute( 'fill', 'currentColor' );

		$imported = $dom->importNode( $svg_element, true );

		return DOM::node_to_element( $imported );
	}

}
