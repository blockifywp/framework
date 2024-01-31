<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Interfaces\Scriptable;
use Blockify\Core\Services\Assets\AbstractAssets;
use Blockify\Core\Services\Assets\Scripts;
use Blockify\Core\Traits\HookAnnotations;
use WP_Block;
use function array_intersect;
use function array_keys;
use function array_map;
use function explode;
use function function_exists;
use function get_option;
use function get_plugins;
use function in_array;
use function is_array;
use function is_user_logged_in;
use function trim;
use function wp_get_current_user;
use function wp_list_pluck;
use const DIRECTORY_SEPARATOR;

/**
 * Visibility class.
 *
 * @since 1.0.0
 */
class Visibility implements Hookable, Renderable, Scriptable {

	use HookAnnotations;

	/**
	 * Render block visibility.
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook render_block 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$visibility = $block['attrs']['visibility'] ?? [];

		if ( empty( $visibility ) || ! is_array( $visibility ) ) {
			return $block_content;
		}

		$status = $visibility['status'] ?? 'all';

		if ( $status === 'logged-in' && ! is_user_logged_in() ) {
			return '';
		}

		if ( $status === 'logged-out' && is_user_logged_in() ) {
			return '';
		}

		$roles = [];

		foreach ( $visibility['roles'] ?? [] as $role ) {
			$roles[] = $role['value'];
		}

		if ( ! empty( $roles ) && is_array( $roles ) ) {
			$user = wp_get_current_user();

			if ( ! array_intersect( $roles, $user->roles ) ) {
				return '';
			}
		}

		$post_meta = wp_list_pluck( $visibility['postMeta'] ?? [], 'value' );

		if ( $post_meta ) {
			$post_id = get_the_ID();

			foreach ( $post_meta as $meta ) {
				if ( empty( trim( get_post_meta( $post_id, $meta, true ) ) ) ) {
					return '';
				}
			}
		}

		$plugins = wp_list_pluck( $visibility['plugins'] ?? [], 'value' );

		if ( $plugins ) {
			$active_plugin_slugs = array_map(
				static fn( string $path ): string => explode( DIRECTORY_SEPARATOR, $path )[0] ?? '',
				get_option( 'active_plugins', [] )
			);

			foreach ( $plugins as $plugin ) {

				if ( ! in_array( $plugin, $active_plugin_slugs, true ) ) {
					return '';
				}
			}
		}

		// Deprecated.
		$cookie = $visibility['cookie'] ?? '';

		if ( ! empty( $cookie ) && isset( $_COOKIE[ $cookie ] ) ) {
			return '';
		}

		return $block_content;
	}

	/**
	 * Add user roles to editor data.
	 *
	 * @param Scripts $scripts Scripts service.
	 *
	 * @return void
	 */
	public function scripts( Scripts $scripts ): void {
		global $wp_roles;

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$data['userRoles'] = $wp_roles->role_names;
		$data['plugins']   = array_map(
			static fn( $plugin_path ): string => explode( DIRECTORY_SEPARATOR, $plugin_path )[0] ?? '',
			array_keys( get_plugins() )
		);
		$data['postMeta']  = get_option( 'blockify' )['postMetaKeys'] ?? [];

		$scripts->add()
			->handle( 'editor' )
			->localize( [
				$scripts->prefix,
				$data,
			] )
			->context( [ AbstractAssets::EDITOR ] );
	}

}
