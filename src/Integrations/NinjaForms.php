<?php

declare( strict_types=1 );

namespace Blockify\Extensions\Integrations;

use Blockify\Core\Interfaces\Conditional;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Traits\HookAnnotations;
use function wp_dequeue_style;

class NinjaForms implements Hookable, Conditional {

	use HookAnnotations;

	/**
	 * Condition.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function condition(): bool {
		return class_exists( 'Ninja_Forms' );
	}

	/**
	 * Dequeue Ninja Forms CSS.
	 *
	 * @since 0.9.35
	 *
	 * @hook  nf_display_enqueue_scripts
	 *
	 * @return void
	 */
	function dequeue_ninja_forms_css() {
		wp_dequeue_style( 'nf-display' );
	}
}

