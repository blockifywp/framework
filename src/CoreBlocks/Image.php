<?php

declare( strict_types=1 );

namespace Blockify\Framework\CoreBlocks;

use Blockify\Framework\BlockSettings\Image as ImageSettings;
use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function in_array;

/**
 * Image class.
 *
 * @since 1.0.0
 */
class Image implements Renderable {

	/**
	 * Image settings.
	 *
	 * @var array
	 */
	private array $image_settings;

	/**
	 * Image constructor.
	 *
	 * @param ImageSettings $image Image settings.
	 *
	 * @return void
	 */
	public function __construct( ImageSettings $image ) {
		$this->image_settings = $image->settings;
	}

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block object.
	 *
	 * @hook  render_block 12
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$name = $block['blockName'] ?? '';

		if ( ! in_array( $name, [ 'core/image', 'core/post-featured-image', 'blockify/image-compare' ], true ) ) {
			return $block_content;
		}

		$attrs         = $block['attrs'] ?? [];
		$id            = $attrs['id'] ?? '';
		$has_icon      = ( $attrs['iconSet'] ?? '' ) && ( $attrs['iconName'] ?? '' ) || ( $attrs['iconSvgString'] ?? '' );
		$style         = $attrs['style'] ?? [];
		$has_svg       = $style['svgString'] ?? '';
		$margin        = $style['spacing']['margin'] ?? '';
		$border_radius = $style['border']['radius'] ?? '';

		// Image options.
		if ( ! $has_icon && ! $has_svg ) {

			if ( in_array( $name, [ 'core/image', 'core/post-featured-image' ], true ) ) {
				$block_content = CSS::add_responsive_classes(
					$block_content,
					$block,
					$this->image_settings,
					(bool) $id
				);
			}
		}

		$dom    = DOM::create( $block_content );
		$figure = DOM::get_element( 'figure', $dom );

		if ( $figure ) {
			$styles = CSS::string_to_array( $figure->getAttribute( 'style' ) );

			if ( $margin ) {
				$styles = CSS::add_shorthand_property( $styles, 'margin', $style['spacing']['margin'] ?? [] );
			}

			if ( $border_radius ) {
				$styles = CSS::add_shorthand_property( $styles, 'border-radius', $style['border']['radius'] ?? [] );
			}

			$figure->setAttribute(
				'style',
				CSS::array_to_string( $styles )
			);
		}

		return $dom->saveHTML();
	}

}
