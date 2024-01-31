<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function is_null;

/**
 * PostTemplate class.
 *
 * @since 1.0.0
 */
class PostTemplate implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 1.3.2
	 *
	 * @param string   $block_content Block content.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/post-template
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$block_gap = $block['attrs']['style']['spacing']['blockGap'] ?? null;
		$layout    = $block['attrs']['layout']['type'] ?? null;

		if ( ! is_null( $block_gap ) && $layout !== 'grid' ) {
			$dom   = DOM::create( $block_content );
			$first = DOM::get_element( '*', $dom );

			if ( $first ) {
				$first_styles = CSS::string_to_array( $first->getAttribute( 'style' ) );

				$first_styles['gap']       = CSS::format_custom_property( $block_gap );
				$first_styles['display']   = 'flex';
				$first_styles['flex-wrap'] = 'wrap';

				$first->setAttribute( 'style', CSS::array_to_string( $first_styles ) );

				$block_content = $dom->saveHTML();
			}
		}

		return $block_content;
	}
}
