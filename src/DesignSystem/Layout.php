<?php

declare( strict_types=1 );

namespace Blockify\Extensions\DesignSystem;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Traits\HookAnnotations;
use function array_merge;
use function is_admin;
use function str_replace;

/**
 * Layout.
 *
 * @since 0.4.2
 */
class Layout implements Hookable {

	use HookAnnotations;

	/**
	 * Changes layout size unit from vw to % in editor.
	 *
	 * @since 0.4.2
	 *
	 * @param mixed $theme_json WP_Theme_JSON_Data | WP_Theme_JSON_Data_Gutenberg.
	 *
	 * @hook  wp_theme_json_data_theme
	 *
	 * @return mixed
	 */
	public function fix_editor_layout_sizes( $theme_json ) {
		$default      = $theme_json->get_data();
		$new          = [];
		$content_size = $default['settings']['layout']['contentSize'] ?? 'min(calc(100dvw - var(--wp--preset--spacing--lg,2rem)), 720px)';
		$wide_size    = $default['settings']['layout']['wideSize'] ?? 'min(calc(100dvw - var(--wp--preset--spacing--lg,2rem)), 1200px)';

		if ( is_admin() ) {
			$content_size = str_replace( 'dvw', '%', $content_size );
			$wide_size    = str_replace( 'dvw', '%', $wide_size );
		}

		$new['settings']['layout']['contentSize'] = $content_size;
		$new['settings']['layout']['wideSize']    = $wide_size;

		$theme_json->update_with( array_merge( $default, $new ) );

		return $theme_json;
	}
}
