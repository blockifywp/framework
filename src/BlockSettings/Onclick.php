<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\DOM;
use Blockify\Core\Utilities\JS;
use WP_Block;
use function str_contains;
use function strval;

/**
 * Onclick class.
 *
 * @since 1.0.0
 */
class Onclick implements Hookable, Renderable {

	use HookAnnotations;

	private TemplateTags $template_tags;

	/**
	 * Onclick constructor.
	 *
	 * @param TemplateTags $template_tags TemplateTags instance.
	 */
	public function __construct( TemplateTags $template_tags ) {
		$this->template_tags = $template_tags;
	}

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block args.
	 *
	 * @hook  render_block
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$js = strval( $block['attrs']['onclick'] ?? '' );

		if ( ! $js ) {
			return $block_content;
		}

		// Allow JS to contain template tags.
		$js       = $this->template_tags->render( $js, $block, $instance );
		$on_click = JS::format_inline_js( $js );
		$link     = null;
		$name     = $block['blockName'] ?? '';

		// Groups and buttons.
		if ( $on_click && $block_content ) {
			$dom  = DOM::create( $block_content );
			$div  = DOM::get_element( 'div', $dom );
			$link = DOM::get_element( 'a', $div );

			if ( $link && $name === 'core/button' ) {
				$link->setAttribute( 'onclick', $on_click );
			} else {
				if ( $div ) {
					$div->setAttribute( 'onclick', $on_click );
				}
			}

			$block_content = $dom->saveHTML();
		}

		// Icon.
		if ( $on_click && $block_content && $link === null ) {
			$dom    = DOM::create( $block_content );
			$figure = DOM::get_element( 'figure', $dom );
			$img    = DOM::get_element( 'img', $figure );

			if ( $img && ! str_contains( $figure->getAttribute( 'class' ), 'wp-block-post-featured-image' ) ) {
				$img->setAttribute( 'onclick', $on_click );
			}

			$block_content = $dom->saveHTML();
		}

		return $block_content;
	}

}
