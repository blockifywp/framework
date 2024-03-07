<?php

declare( strict_types=1 );

namespace Blockify\Framework\CoreBlocks;

use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function count;

/**
 * Columns class.
 *
 * @since 1.0.0
 */
class Columns implements Renderable {

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/columns
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$attrs  = $block['attrs'] ?? [];
		$margin = $attrs['style']['spacing']['margin'] ?? null;

		if ( $margin ) {
			$dom   = DOM::create( $block_content );
			$first = DOM::get_element( 'div', $dom );

			if ( $first ) {
				$styles = CSS::string_to_array( $first->getAttribute( 'style' ) );
				$styles = CSS::add_shorthand_property( $styles, 'margin', $margin );

				$first->setAttribute( 'style', CSS::array_to_string( $styles ) );
			}

			$block_content = $dom->saveHTML();
		}

		$dom = DOM::create( $block_content );
		$div = DOM::get_element( 'div', $dom );

		if ( $div ) {
			$column_count = (string) count( $block['innerBlocks'] ?? 0 );

			$div->setAttribute( 'data-columns', $column_count );

			$styles = CSS::string_to_array( $div->getAttribute( 'style' ) );

			$styles['--columns'] = $column_count;

			$div->setAttribute( 'style', CSS::array_to_string( $styles ) );

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

}
