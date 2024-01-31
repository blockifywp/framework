<?php

declare( strict_types=1 );

namespace Blockify\Extensions\DesignSystem;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\Styles;
use Blockify\Core\Traits\HookAnnotations;

/**
 * Admin bar.
 *
 * @since 1.0.0
 */
class AdminBar implements Hookable, Styleable {

	use HookAnnotations;

	/**
	 * Registers service with access to provider.
	 *
	 * @since 1.0.0
	 *
	 * @param Styles $styles Styles service.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$styles->add()
			->handle( 'admin-bar' )
			->src( 'components/admin-bar.css' )
			->condition( static fn(): bool => is_admin_bar_showing() );
	}

	/**
	 * Removes the default callback for the admin bar.
	 *
	 * @since 1.0.0
	 *
	 * @hook  after_setup_theme
	 *
	 * @return void
	 */
	public function remove_default_callback() {
		add_theme_support( 'admin-bar', [
			'callback' => '__return_false',
		] );
	}
}
