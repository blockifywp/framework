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
 * Spacer class.
 *
 * @since 1.0.0
 */
class Spacer implements Hookable, Renderable {

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
	 * @hook  render_block_core/spacer 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom = DOM::create( $block_content );
		$div = DOM::get_element( 'div', $dom );

		if ( ! $div ) {
			return $block_content;
		}

		$div_styles = CSS::string_to_array( $div->getAttribute( 'style' ) );

		$margin     = $block['attrs']['style']['spacing']['margin'] ?? '';
		$div_styles = CSS::add_shorthand_property( $div_styles, 'margin', $margin );

		$width            = $block['attrs']['width'] ?? '';
		$responsive_width = $block['attrs']['style']['width']['all'] ?? '';

		if ( $width && $responsive_width ) {
			unset ( $div_styles['width'] );
		}

		$div->setAttribute( 'style', CSS::array_to_string( $div_styles ) );

		return $dom->saveHTML();
	}

}
