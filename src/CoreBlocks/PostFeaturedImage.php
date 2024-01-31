<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function esc_attr;
use function explode;
use function implode;

/**
 * PostFeaturedImage class.
 *
 * @since 1.0.0
 */
class PostFeaturedImage implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 1.3.0
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/post-featured-image
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom    = DOM::create( $block_content );
		$figure = DOM::get_element( 'figure', $dom );
		$img    = DOM::get_element( 'img', $figure );

		if ( ! $figure ) {
			return $block_content;
		}

		$figure_classes = explode( ' ', $figure->getAttribute( 'class' ) );
		$attrs          = $block['attrs'] ?? [];
		$shadow_preset  = esc_attr( $attrs['shadowPreset'] ?? '' );
		$hover_preset   = esc_attr( $attrs['shadowPresetHover'] ?? '' );
		$use_custom     = $attrs['useCustomBoxShadow'] ?? null;
		$shadow_custom  = $attrs['style']['boxShadow'] ?? null;
		$hover_custom   = $attrs['style']['boxShadow']['hover'] ?? null;
		$border_radius  = $attrs['style']['border']['radius'] ?? null;

		if ( $shadow_preset ) {
			$figure_classes[] = 'has-shadow';
			$figure_classes[] = "has-{$shadow_preset}-shadow";
		}

		if ( $hover_preset ) {
			$figure_classes[] = "has-{$hover_preset}-shadow-hover";
		}

		$figure->setAttribute( 'class', implode( ' ', $figure_classes ) );

		$figure_styles = CSS::string_to_array( $figure->getAttribute( 'style' ) );

		if ( $use_custom && $shadow_custom ) {
			$color = $shadow_custom['color'] ?? null;

			if ( $color ) {
				$figure_styles['--wp--custom--box-shadow--color'] = CSS::format_custom_property( $color );
			}
		}

		if ( $use_custom && $hover_custom ) {
			$color = $hover_custom['color'] ?? null;

			if ( $color ) {
				$figure_styles['--wp--custom--box-shadow--hover--color'] = CSS::format_custom_property( $color );
			}
		}

		if ( $border_radius ) {
			$figure_styles['border-radius'] = $border_radius;
		}

		$img_classes = $img ? explode( ' ', $img->getAttribute( 'class' ) ) : [];

		$transform       = $attrs['style']['transform'] ?? [];
		$transform_units = [
			'rotate'    => 'deg',
			'skew'      => 'deg',
			'scale'     => '',
			'translate' => '',
		];

		if ( ! empty( $transform ) && is_array( $transform ) ) {
			$transform_value = '';

			foreach ( $transform as $key => $value ) {
				$unit            = $transform_units[ $key ] ?? '';
				$transform_value .= "{$key}({$value}{$unit}) ";
			}

			if ( ! in_array( 'has-transform', $img_classes, true ) ) {
				$figure_styles['transform'] = $transform_value;
			}
		}

		$filter = $attrs['style']['filter'] ?? [];

		if ( ! empty( $filter ) && is_array( $filter ) ) {
			$filter_options = get_filter_options();
			$filter_value   = '';

			foreach ( $filter as $key => $value ) {
				$unit         = $filter_options[ $key ]['unit'] ?? '';
				$filter_value .= "{$key}({$value}{$unit}) ";
			}

			if ( ! in_array( 'has-filter', $img_classes, true ) ) {
				$figure_styles['filter'] = $filter_value;
			}
		}

		if ( $figure_styles ) {
			$figure->setAttribute( 'style', CSS::array_to_string( $figure_styles ) );
		}

		return $dom->saveHTML();
	}

}
