<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Interfaces\Scriptable;
use Blockify\Core\Services\Assets\Scripts;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use WP_Block;
use function str_contains;

/**
 * Details class.
 *
 * @since 1.0.0
 */
class Details implements Hookable, Renderable, Scriptable {

	use HookAnnotations;

	/**
	 * Renders the details block.
	 *
	 * @since 0.0.1
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block_core/details
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$dom     = DOM::create( $block_content );
		$details = DOM::get_element( 'details', $dom );

		if ( ! $details ) {
			return $block_content;
		}

		$summary = DOM::get_element( 'summary', $details );
		$padding = $block['attrs']['style']['spacing']['padding'] ?? [];

		if ( $summary && $padding ) {
			$summary_styles = CSS::string_to_array( $summary->getAttribute( 'style' ) );

			$summary_styles['padding-top']    = $padding['top'] ?? '';
			$summary_styles['padding-bottom'] = $padding['bottom'] ?? '';
			$summary_styles['padding-left']   = $padding['left'] ?? '';
			$summary_styles['margin-top']     = 'calc(0px - ' . ( $padding['top'] ?? '' ) . ')';
			$summary_styles['margin-bottom']  = 'calc(0px - ' . ( $padding['bottom'] ?? '' ) . ')';
			$summary_styles['margin-left']    = 'calc(0px - ' . ( $padding['left'] ?? '' ) . ')';

			$summary->setAttribute( 'style', CSS::array_to_string( $summary_styles ) );
		}

		return $dom->saveHTML();
	}

	/**
	 * Adds assets.
	 *
	 * @since 0.0.1
	 *
	 * @param Scripts $scripts The scripts service.
	 *
	 * @return void
	 */
	public function scripts( Scripts $scripts ): void {
		$scripts->add()
			->handle( 'details-block' )
			->src( 'public/details.js' )
			->condition( static fn( string $template_html ): bool => str_contains( $template_html, 'wp-block-details' ) );
	}

}
