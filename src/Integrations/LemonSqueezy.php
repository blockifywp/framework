<?php

declare( strict_types=1 );

namespace Blockify\Extensions\Integrations;

use Blockify\Core\Interfaces\Conditional;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Traits\HookAnnotations;
use Blockify\Core\Utilities\DOM;
use function array_filter;
use function defined;
use function explode;
use function implode;

/**
 * Lemon Squeezy extension.
 *
 * @since 1.0.0
 */
class LemonSqueezy implements Hookable, Conditional {

	use HookAnnotations;

	/**
	 * Condition.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function condition(): bool {
		return defined( 'LSQ_PATH' );
	}

	/**
	 * Renders a Lemon Squeezy button.
	 *
	 * @param string $html Block HTML.
	 *
	 * @hook render_block_lemonsqueezy/ls-button
	 *
	 * @return string
	 */
	public function render_lemonsqueezy_button( string $html ): string {
		$dom    = DOM::create( $html );
		$div    = DOM::get_dom_element( 'div', $dom );
		$button = DOM::get_dom_element( 'div', $div );
		$link   = DOM::get_dom_element( 'a', $button );

		if ( ! $link ) {
			return $html;
		}

		$link_classes = [
			'wp-element-button',
			...explode( ' ', $link->getAttribute( 'class' ) ),
		];

		$link_classes = array_filter(
			$link_classes,
			static fn( $class ) => $class !== 'wp-block-button__link'
		);

		$link->setAttribute( 'class', implode( ' ', $link_classes ) );

		return $dom->saveHTML();
	}

}

