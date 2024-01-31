<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
use function str_replace;

/**
 * Columns class.
 *
 * @since 1.0.0
 */
class Columns implements Hookable, Renderable {

	use HookAnnotations;

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
		$class      = 'is-stacked-on-mobile';
		$is_stacked = $block['attrs']['stackedOnMobile'] ?? null;

		if ( $is_stacked && $block['attrs']['isStackedOnMobile'] === false ) {
			$class = 'is-not-stacked-on-mobile';
		}

		if ( $class === 'is-stacked-on-mobile' ) {
			$block_content = str_replace( 'wp-block-columns ', 'wp-block-columns is-stacked-on-mobile ', $block_content );
			$dom           = DOM::create( $block_content );
			$div           = DOM::get_element( 'div', $dom );

			if ( $div ) {
				$div_classes = explode( ' ', $div->getAttribute( 'class' ) );

				if ( ! in_array( $class, $div_classes ) ) {
					$div_classes[] = $class;
				}

				$div_classes = array_unique( $div_classes );

				$div->setAttribute( 'class', implode( ' ', $div_classes ) );
			}

			$block_content = $dom->saveHTML();
		}

		$margin = $block['attrs']['style']['spacing']['margin'] ?? null;

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

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

}
