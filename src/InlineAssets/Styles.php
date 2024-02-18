<?php

declare( strict_types=1 );

namespace Blockify\Framework\InlineAssets;

use function apply_filters;
use function is_admin;
use function wp_add_inline_style;
use function wp_dequeue_style;
use function wp_enqueue_style;
use function wp_register_style;

/**
 * Styles class.
 *
 * @since 1.0.0
 */
class Styles implements Inlinable {

	use AssetsTrait;

	private string $dynamic_styles_url = 'https://blockify-dynamic-styles';

	/**
	 * Enqueue inline styles.
	 *
	 * @hook enqueue_block_assets
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( is_admin() ) {
			return;
		}

		global $template_html;

		$load_all = apply_filters( 'blockify_load_all_styles', ! $template_html );
		$css      = $this->get_inline_assets( $template_html, $load_all );

		wp_dequeue_style( 'wp-block-library-theme' );
		wp_register_style( $this->handle, '' );
		wp_enqueue_style( $this->handle );
		wp_add_inline_style( $this->handle, $css );
	}

	/**
	 * Adds editor styles.
	 *
	 * @hook admin_init
	 *
	 * @return void
	 */
	public function add_editor_styles(): void {
		$blocks = glob( $this->dir . 'core-blocks/*.css' );

		foreach ( $blocks as $block ) {
			add_editor_style( 'vendor/blockify/extensions/public/css/core-blocks/' . basename( $block ) );
		}

		add_editor_style( $this->dynamic_styles_url );
	}

	/**
	 * Generates dynamic editor styles.
	 *
	 * @since 0.9.23
	 *
	 * @param array|bool $response    HTTP response.
	 * @param array      $parsed_args Response args.
	 * @param string     $url         Response URL.
	 *
	 * @hook  pre_http_request
	 *
	 * @return array|bool
	 */
	public function generate_dynamic_styles( $response, array $parsed_args, string $url ) {
		if ( $url === $this->dynamic_styles_url ) {
			$css = $this->get_inline_assets( '', true );

			$response = [
				'body'     => $css,
				'headers'  => [],
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'  => [],
				'filename' => null,
			];
		}

		return $response;
	}
}
