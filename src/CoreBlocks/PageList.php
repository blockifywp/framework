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
 * PageList class.
 *
 * @since 1.0.0
 */
class PageList implements Hookable, Renderable {

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
	 * @hook  render_block_core/page-list
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$block_gap = $block['attrs']['style']['spacing']['blockGap'] ?? null;

		if ( ! $block_gap ) {
			return $block_content;
		}

		$dom = DOM::create( $block_content );
		$ul  = DOM::get_element( 'ul', $dom );

		if ( ! $ul ) {
			return $block_content;
		}

		$styles = CSS::string_to_array( $ul->getAttribute( 'style' ) );

		$styles['--wp--style--block-gap'] = CSS::format_custom_property( $block_gap );

		$ul->setAttribute( 'style', CSS::array_to_string( $styles ) );

		return $dom->saveHTML();
	}

}
