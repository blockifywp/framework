<?php

declare( strict_types=1 );

namespace Blockify\Framework\CoreBlocks;

use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function esc_attr;
use function esc_html;
use function get_option;
use function implode;
use function is_front_page;
use function is_home;

/**
 * QueryTitle class.
 *
 * @since 1.0.0
 */
class QueryTitle implements Renderable {

	/**
	 * Renders the Archive Title block.
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block         The block.
	 * @param WP_Block $instance      The block instance.
	 *
	 * @hook render_block_core/query-title
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		if ( $block_content ) {
			return $block_content;
		}

		if ( ! is_home() || is_front_page() ) {
			return $block_content;
		}

		$page_for_posts = get_option( 'page_for_posts' );

		if ( ! $page_for_posts ) {
			return '';
		}

		$dom = DOM::create( $block_content );
		$h1  = DOM::create_element( 'h1', $dom );

		$classes = [
			'wp-block-query-title',
		];

		$text_align = $block['attrs']['textAlign'] ?? null;

		if ( $text_align ) {
			$classes[] = 'has-text-align-' . esc_attr( $text_align );
		}

		$h1->setAttribute( 'class', implode( ' ', $classes ) );

		$styles  = [];
		$margin  = $block['attrs']['style']['spacing']['margin'] ?? [];
		$padding = $block['attrs']['style']['spacing']['padding'] ?? [];
		$styles  = CSS::add_shorthand_property( $styles, 'margin', $margin );
		$styles  = CSS::add_shorthand_property( $styles, 'padding', $padding );

		$h1->setAttribute( 'style', CSS::array_to_string( $styles ) );
		$h1->nodeValue = esc_html( get_the_title( $page_for_posts ) );
		$dom->appendChild( $h1 );

		return $dom->saveHTML();
	}
}
