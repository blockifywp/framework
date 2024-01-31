<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;

/**
 * Buttons block.
 *
 * @since 1.0.0
 */
class Buttons implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      The block instance.
	 *
	 * @hook  render_block_core/buttons 10
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$margin  = $block['attrs']['style']['spacing']['margin'] ?? [];
		$padding = $block['attrs']['style']['spacing']['padding'] ?? [];

		if ( $margin || $padding ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );

			if ( $div ) {
				$styles = CSS::string_to_array( $div->getAttribute( 'style' ) );
				$styles = CSS::add_shorthand_property( $styles, 'margin', $margin );
				$styles = CSS::add_shorthand_property( $styles, 'padding', $padding );

				$div->setAttribute( 'style', CSS::array_to_string( $styles ) );

				$block_content = $dom->saveHTML();
			}
		}

		return $block_content;
	}

}
