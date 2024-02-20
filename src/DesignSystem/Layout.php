<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use function is_admin;
use function str_replace;

/**
 * Layout.
 *
 * @since 0.4.2
 */
class Layout {

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
		if ( is_admin() ) {
			return $theme_json;
		}

		$default      = $theme_json->get_data();
		$new          = [];
		$content_size = $default['settings']['layout']['contentSize'] ?? 'min(calc(100dvw - var(--wp--preset--spacing--lg,2rem)), 720px)';
		$wide_size    = $default['settings']['layout']['wideSize'] ?? 'min(calc(100dvw - var(--wp--preset--spacing--lg,2rem)), 1200px)';

		$new['settings']['layout']['contentSize'] = str_replace( '%', 'dvw', $content_size );
		$new['settings']['layout']['wideSize']    = str_replace( '%', 'dvw', $wide_size );

		$theme_json->update_with( $new );

		return $theme_json;
	}
}
