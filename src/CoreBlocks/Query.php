<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function str_contains;

/**
 * Query class.
 *
 * @since 1.0.0
 */
class Query implements Hookable, Renderable {

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
	 * @hook  render_block_core/query
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$block_gap = $block['attrs']['style']['spacing']['blockGap'] ?? null;

		if ( $block_gap ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );

			if ( ! $div ) {
				return $block_content;
			}

			$styles = CSS::string_to_array( $div->getAttribute( 'style' ) );

			$styles['--wp--style--block-gap'] = CSS::format_custom_property( $block_gap );

			$div->setAttribute( 'style', CSS::array_to_string( $styles ) );

			$block_content = $dom->saveHTML();
		}

		$columns = $block['attrs']['displayLayout']['columns'] ?? null;

		if ( $columns && str_contains( $block_content, 'nowrap' ) ) {
			$dom = DOM::create( $block_content );
			$div = DOM::get_element( 'div', $dom );

			if ( $div ) {
				$styles              = CSS::string_to_array( $div->getAttribute( 'style' ) );
				$styles['--columns'] = $columns;
				$div->setAttribute( 'style', CSS::array_to_string( $styles ) );

				$block_content = $dom->saveHTML();
			}
		}

		return $block_content;
	}

}
