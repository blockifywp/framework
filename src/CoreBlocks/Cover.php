<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function Blockify\Extensions\BlockSettings\get_placeholder_icon;
use function is_null;

/**
 * Cover class.
 *
 * @since 1.0.0
 */
class Cover implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Renders the cover block.
	 *
	 * @since 0.0.1
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/cover
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom = DOM::create( $block_content );
		$div = DOM::get_element( 'div', $dom );

		if ( ! $div ) {
			return $block_content;
		}

		$url = $block['attrs']['url'] ?? null;

		if ( ! $url ) {
			$imported = $dom->importNode( get_placeholder_icon( $dom ), true );
			$svg      = DOM::node_to_element( $imported );

			$classes   = [];
			$classes[] = 'wp-block-cover__image-background';

			$svg->setAttribute( 'class', implode( ' ', $classes ) );
		}

		$padding = $block['attrs']['style']['spacing']['padding'] ?? null;
		$zIndex  = $block['attrs']['style']['zIndex']['all'] ?? null;

		$styles = CSS::string_to_array( $div->getAttribute( 'style' ) );
		$styles = CSS::add_shorthand_property( $styles, 'padding', $padding );

		if ( ! is_null( $zIndex ) ) {
			$styles['--z-index'] = CSS::format_custom_property( $zIndex );
		}

		$div->setAttribute( 'style', CSS::array_to_string( $styles ) );

		return $dom->saveHTML();
	}

}
