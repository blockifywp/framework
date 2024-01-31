<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Configurable;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use Blockify\Extensions\ExtensionsConfig;
use WP_Block;
use function in_array;

/**
 * Image class.
 *
 * @since 1.0.0
 */
class Image implements Hookable, Renderable, Configurable {

	use HookAnnotations;
	use ExtensionsConfig;

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

		$attrs    = $block['attrs'] ?? [];
		$id       = $attrs['id'] ?? '';
		$has_icon = ( $attrs['iconSet'] ?? '' ) && ( $attrs['iconName'] ?? '' ) || ( $attrs['iconSvgString'] ?? '' );
		$style    = $attrs['style'] ?? [];
		$has_svg  = $style['svgString'] ?? '';

		// Image options.
		if ( ! $has_icon && ! $has_svg ) {

			if ( in_array( $name, [ 'core/image', 'core/post-featured-image' ], true ) ) {
				$block_content = CSS::add_responsive_classes(
					$block_content,
					$block,
					$this->config['block_settings']['image'],
					(bool) $id
				);
			}
		}

		$margin = $style['spacing']['margin'] ?? '';

		if ( $margin ) {
			$dom    = DOM::create( $block_content );
			$figure = DOM::get_element( 'figure', $dom );

			if ( $figure ) {
				$styles = CSS::string_to_array( $figure->getAttribute( 'style' ) );

				$styles = CSS::add_shorthand_property( $styles, 'margin', $style['spacing']['margin'] ?? [] );

				$figure->setAttribute(
					'style',
					CSS::array_to_string( $styles )
				);
			}

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

}
