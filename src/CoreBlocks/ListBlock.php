<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function array_unshift;
use function esc_attr;
use function explode;

/**
 * List class.
 *
 * @since 1.0.0
 */
class ListBlock implements Hookable, Renderable {

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
	 * @hook  render_block_core/list 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom = DOM::create( $block_content );
		$ul  = DOM::get_element( 'ul', $dom );
		$ol  = DOM::get_element( 'ol', $dom );

		if ( ! $ul && ! $ol ) {
			return $block_content;
		}

		$block_gap       = $block['attrs']['style']['spacing']['blockGap'] ?? null;
		$justify_content = $block['attrs']['layout']['justifyContent'] ?? '';
		$margin          = $block['attrs']['style']['spacing']['margin'] ?? [];
		$list            = $ul ?? $ol;
		$styles          = CSS::string_to_array( $list->getAttribute( 'style' ) );

		if ( $block_gap === '0' || $block_gap ) {
			$styles['gap'] = CSS::format_custom_property( $block_gap );
		}

		if ( $justify_content ) {
			$styles['display']         = 'flex';
			$styles['flex-wrap']       = 'wrap';
			$styles['justify-content'] = esc_attr( $justify_content );
		}

		$styles = CSS::add_shorthand_property( $styles, 'margin', $margin );

		if ( $styles ) {
			$list->setAttribute( 'style', CSS::array_to_string( $styles ) );
		}

		$classes = explode( ' ', $list->getAttribute( 'class' ) );

		array_unshift( $classes, 'wp-block-list' );

		$list->setAttribute( 'class', implode( ' ', $classes ) );

		return $dom->saveHTML();
	}

}
