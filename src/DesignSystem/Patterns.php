<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use Blockify\Utilities\Pattern;
use WP_Block_Patterns_Registry;
use function apply_filters;
use function array_unique;
use function basename;
use function get_stylesheet_directory;
use function get_template_directory;
use function glob;
use function in_array;
use function is_dir;
use function ksort;
use function register_block_pattern_category;
use function remove_theme_support;
use function str_replace;
use function strtoupper;
use function trailingslashit;
use function ucwords;

/**
 * Patterns extension.
 *
 * @since 0.0.2
 */
class Patterns {

	/**
	 * Removes core block patterns.
	 *
	 * @since 0.0.2
	 *
	 * @hook  init 9
	 *
	 * @return void
	 */
	function remove_core_patterns(): void {
		remove_theme_support( 'core-block-patterns' );
	}

	/**
	 * Manually registers default patterns to avoid loading in child themes.
	 *
	 * @since 1.0.1
	 *
	 * @hook  init
	 *
	 * @return void
	 */
	public function register_default_patterns(): void {
		$all_dirs   = $this->get_pattern_dirs();
		$categories = [];

		foreach ( $all_dirs as $dir ) {
			$files    = glob( $dir . '/*.php' );
			$category = basename( $dir );

			foreach ( $files as $file ) {
				$pattern = basename( $file, '.php' );

				if ( ! isset( $categories[ $category ] ) ) {
					$categories[ $category ] = [];
				}

				$categories[ $category ][ $pattern ] = $file;
			}
		}

		$categories = apply_filters( 'blockify_patterns', $categories );

		ksort( $categories );

		$registered_categories = [];
		$registered_slugs      = [];
		$all_patterns          = WP_Block_Patterns_Registry::get_instance()->get_all_registered() ?? [];

		foreach ( $all_patterns as $pattern ) {
			$registered_slugs[] = $pattern['slug'] ?? '';
		}

		foreach ( $categories as $category => $patterns ) {

			if ( ! in_array( $category, $registered_categories, true ) ) {

				if ( in_array( $category, [ 'cta', 'faq' ], true ) ) {
					$label = strtoupper( $category );
				} else {
					$label = ucwords( str_replace( '-', ' ', $category ) );
				}

				register_block_pattern_category(
					$category,
					[
						'label' => $label,
					]
				);

				$registered_categories[ $category ] = [];
			}

			foreach ( $patterns as $pattern => $file ) {
				$basename = basename( $file, '.php' );

				if ( in_array( $basename, $registered_categories[ $category ], true ) ) {
					continue;
				}

				$registered_categories[ $category ][] = $basename;

				$slug = $category . '-' . $basename;

				if ( in_array( $slug, $registered_slugs, true ) ) {
					continue;
				}

				Pattern::register_from_file( $file );
			}
		}
	}

	/**
	 * Returns array of pattern directories.
	 *
	 * @since 0.0.2
	 *
	 * @return array
	 */
	private function get_pattern_dirs(): array {
		$dirs = array_unique( apply_filters( 'blockify_pattern_dirs', [
			get_template_directory() . '/patterns',
			get_stylesheet_directory() . '/patterns',
		] ) );

		$category_dirs = [];

		foreach ( $dirs as $dir ) {
			$dir = trailingslashit( $dir );

			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$category_dirs = [
				...$category_dirs,
				...glob( $dir . '*', GLOB_ONLYDIR ),
			];
		}

		return $category_dirs;
	}

}
