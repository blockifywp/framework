<?php

declare( strict_types=1 );

namespace Blockify\Extensions\Integrations;

use Blockify\Core\Interfaces\Conditional;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Providers\Data;
use WP_Block_Patterns_Registry;
use WP_Block_Template;
use function add_action;
use function class_exists;
use function remove_action;
use function str_contains;

/**
 * WooCommerce integration.
 *
 * @since 1.0.0
 */
class WooCommerce implements Hookable, Conditional {

	/**
	 * Theme or plugin data.
	 *
	 * @var Data
	 */
	private Data $data;

	/**
	 * WooCommerce constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Data $data Theme or plugin data.
	 *
	 * @return void
	 */
	public function __construct( Data $data ) {
		$this->data = $data;
	}

	/**
	 * Condition.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function condition(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hooks(): void {
		remove_action( 'init', [
			'Automattic\WooCommerce\Blocks\BlockPatterns',
			'register_block_patterns',
		] );

		add_action( 'init', [ $this, 'unregister_woocommerce_block_patterns' ], 11 );
	}

	/**
	 * Unregister WooCommerce block patterns.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function unregister_woocommerce_block_patterns(): void {
		$registry   = WP_Block_Patterns_Registry::get_instance();
		$registered = $registry->get_all_registered();

		foreach ( $registered as $pattern ) {
			$name = $pattern['name'];

			if ( str_contains( $name, 'woocommerce' ) ) {
				$registry->unregister( $name );
			}
		}
	}

	/**
	 * Remove unused templates from editor.
	 *
	 * @since 1.2.9
	 *
	 * @param ?WP_Block_Template[] $query_result  The query result.
	 * @param array                $query         The query.
	 * @param string               $template_type The template type.
	 *
	 * @hook  get_block_templates 10 3
	 *
	 * @return array
	 */
	public function remove_templates( ?array $query_result, array $query, string $template_type ): array {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}

		$woocommerce = class_exists( 'WooCommerce' );

		foreach ( $query_result as $index => $wp_block_template ) {
			$slug = $wp_block_template->slug;

			if ( $this->data->slug !== $wp_block_template->theme ) {
				continue;
			}

			if ( ! $woocommerce && str_contains( $slug, 'product' ) ) {
				unset( $query_result[ $index ] );
			}
		}

		return $query_result;
	}
}
