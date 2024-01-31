<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function str_replace;

/**
 * PostAuthor class.
 *
 * @since 1.0.0
 */
class PostAuthor implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.1
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/post-author
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom    = DOM::create( $block_content );
		$div    = DOM::get_element( 'div', $dom );
		$styles = [];

		if ( ! $div ) {
			return $block_content;
		}

		$style = $div->getAttribute( 'style' );

		if ( $block['attrs']['style']['border'] ?? null ) {
			$styles['border-width']  = $block['attrs']['style']['border']['width'] ?? null;
			$styles['border-style']  = $block['attrs']['style']['border']['style'] ?? null;
			$styles['border-color']  = $block['attrs']['style']['border']['color'] ?? null;
			$styles['border-radius'] = $block['attrs']['style']['border']['radius'] ?? null;
		}

		$gap = $block['attrs']['style']['spacing']['blockGap'] ?? null;

		if ( $gap ) {
			$styles['--wp--style--block-gap'] = CSS::format_custom_property( $gap );
		}

		$div->setAttribute(
			'style',
			( $style ? $style . ';' : '' ) . CSS::array_to_string( $styles )
		);

		return str_replace(
			[ '<p ', '</p>' ],
			[ '<span ', '</span>' ],
			$dom->saveHTML()
		);
	}
}
