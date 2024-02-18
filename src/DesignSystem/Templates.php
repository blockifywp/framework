<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use function array_unshift;
use function file_exists;
use function get_post_type;
use function get_queried_object;
use function get_stylesheet_directory;
use function get_template_directory;
use function is_post_type_archive;
use function is_search;

/**
 * Templates extension.
 *
 * @since 1.0.0
 */
class Templates {

	/**
	 * Updates search template hierarchy.
	 *
	 * @since 1.0.0
	 *
	 * @param array $templates Template files to search for, in order.
	 *
	 * @hook  search_template_hierarchy
	 *
	 * @return array
	 */
	public function update_search_template_hierarchy( array $templates ): array {
		if ( is_search() && is_post_type_archive() ) {
			$post_type = get_queried_object()->name ?? get_post_type();
			$slug      = "search-$post_type";
			$child     = get_stylesheet_directory() . "/templates/$slug.html";
			$parent    = get_template_directory() . "/templates/$slug.html";

			if ( file_exists( $child ) || file_exists( $parent ) ) {
				array_unshift( $templates, $slug );
			}
		}

		return $templates;
	}

}
