<?php

declare( strict_types=1 );

namespace Blockify\Extensions\CoreBlocks;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use Blockify\Core\Utilities\Str;
use WP_Block;
use WP_Block_Supports;
use function add_post_type_support;
use function apply_filters;
use function esc_html;
use function esc_html__;
use function explode;
use function get_post_field;
use function get_the_excerpt;
use function get_the_ID;
use function get_the_title;
use function implode;
use function in_array;
use function is_singular;
use function str_replace;
use function trim;
use function wp_trim_words;

/**
 * PostExcerpt class.
 *
 * @since 1.0.0
 */
class PostExcerpt implements Hookable, Renderable {

	use HookAnnotations;

	/**
	 * Renders post excerpt block.
	 *
	 * @since 1.2.4
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block         The block.
	 * @param WP_Block $instance      The block object.
	 *
	 * @hook  render_block_core/post-excerpt
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$query_post_id  = $instance->context['postId'] ?? false;
		$custom_excerpt = get_post_field( 'post_excerpt', $query_post_id ?? get_the_ID() );
		$is_query_loop  = ( WP_Block_Supports::$block_to_render['blockName'] ?? '' ) === 'core/post-template';

		if ( is_singular() && ! $custom_excerpt && ! $is_query_loop ) {
			return '';
		}

		$hide_read_more = $block['attrs']['hideReadMore'] ?? false;

		if ( $hide_read_more ) {
			$dom   = DOM::create( $block_content );
			$first = DOM::get_element( '*', $dom );

			if ( ! $first ) {
				return $block_content;
			}

			$read_more = DOM::get_elements_by_class_name( 'wp-block-post-excerpt__more-text', $dom )[0] ?? null;

			if ( $read_more ) {
				$read_more->parentNode->removeChild( $read_more );
			} else {
				$classes = explode( ' ', $first->getAttribute( 'class' ) );

				if ( ! in_array( 'hide-read-more', $classes, true ) ) {
					$classes[] = 'hide-read-more';
				}

				$first->setAttribute( 'class', implode( ' ', $classes ) );
			}

			$block_content = $dom->saveHTML();
		}

		$more_text = $block['attrs']['moreText'] ?? '';

		if ( $more_text ) {
			$dom       = DOM::create( $block_content );
			$more_link = DOM::get_elements_by_class_name( 'wp-block-post-excerpt__more-link', $dom )[0] ?? null;

			if ( $more_link ) {
				$screen_reader = DOM::create_element( 'span', $dom );

				$screen_reader->setAttribute( 'class', 'screen-reader-text' );

				$post_id    = $instance->context['postId'] ?? '';
				$post_title = get_the_title( $post_id );

				if ( ! $post_title ) {
					$post_title = esc_html__( 'this post', 'blockify' );
				}

				$screen_reader->textContent = esc_html__( ' about ', 'blockify' ) . ( $post_title );

				$more_link->appendChild( $screen_reader );

				$block_content = $dom->saveHTML();
			}
		}

		$excerpt_length  = $block['attrs']['excerptLength'] ?? apply_filters( 'excerpt_length', 55 );
		$default_excerpt = $block['attrs']['defaultExcerpt'] ?? '';
		$excerpt         = $custom_excerpt ?: $default_excerpt;

		if ( ! $excerpt ) {
			$excerpt = get_the_excerpt();
		}

		$dom = DOM::create( $block_content );
		$div = DOM::get_elements_by_class_name( 'wp-block-post-excerpt', $dom )[0] ?? null;
		$p   = DOM::get_elements_by_class_name( 'wp-block-post-excerpt__excerpt', $dom )[0] ?? null;

		if ( ! $p ) {
			$div = $div ?? DOM::create_element( 'div', $dom );
			$p   = DOM::create_element( 'p', $dom );

			$p->textContent = esc_html( $excerpt );
			$p->setAttribute( 'class', 'wp-block-post-excerpt__excerpt' );

			$div->appendChild( $p );
		}

		$p->textContent = esc_html( $excerpt );
		$div_classes    = explode( ' ', $div->getAttribute( 'class' ) );
		$styles         = [];
		$text_color     = $block['attrs']['textColor'] ?? null;

		if ( $text_color ) {
			$custom_property = ! Str::contains_any( $text_color, '#', 'rgb', 'hsl' );

			$styles['color'] = $custom_property ? 'var(--wp--preset--color--' . $text_color . ')' : $text_color;
		}

		if ( $block['attrs']['textAlign'] ?? '' ) {
			$div_classes[] = 'has-text-align-' . $block['attrs']['textAlign'];
		}

		$margin  = $block['attrs']['style']['spacing']['margin'] ?? '';
		$padding = $block['attrs']['style']['spacing']['padding'] ?? '';
		$styles  = CSS::add_shorthand_property( $styles, 'margin', $margin );
		$styles  = CSS::add_shorthand_property( $styles, 'padding', $padding );

		if ( $styles ) {
			$div->setAttribute( 'style', CSS::array_to_string( $styles ) );
		}

		$align = $block['attrs']['align'] ?? '';

		if ( $align ) {
			$div_classes[] = 'align' . $align;
		}

		$div->setAttribute( 'class', trim( implode( ' ', $div_classes ) ) );
		$dom->appendChild( $div );

		// Limit length.
		$p->nodeValue = wp_trim_words(
			$p->nodeValue ?? '',
			$excerpt_length,
		);

		return $dom->saveHTML();
	}

	/**
	 * Adds excerpt support to pages.
	 *
	 * @since 1.3.0
	 *
	 * @hook  after_setup_theme
	 *
	 * @return void
	 */
	public function add_page_excerpt_support(): void {
		add_post_type_support( 'page', 'excerpt' );
	}

	/**
	 * Removes brackets from excerpt more string.
	 *
	 * @since 1.3.0
	 *
	 * @param string $more Read more text.
	 *
	 * @hook  excerpt_more
	 *
	 * @return string
	 */
	public function remove_brackets_from_excerpt( string $more ): string {
		return str_replace( [ '[', ']' ], '', $more );
	}

}
