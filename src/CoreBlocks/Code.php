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
 * Code class.
 *
 * @since 1.0.0
 */
class Code implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block object.
	 *
	 * @hook  render_block_core/code 12
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$attrs  = $block['attrs'] ?? [];
		$margin = $attrs['style']['spacing']['margin'] ?? '';

		if ( $margin ) {
			$dom = DOM::create( $block_content );
			$pre = DOM::get_element( 'pre', $dom );

			if ( $pre ) {
				$pre_styles = CSS::string_to_array( $pre->getAttribute( 'style' ) );
				$pre_styles = CSS::add_shorthand_property( $pre_styles, 'margin', $margin );

				$pre->setAttribute( 'style', CSS::array_to_string( $pre_styles ) );
			}

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

}
