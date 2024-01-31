<?php

declare( strict_types=1 );

namespace Blockify\Extensions\BlockSettings;

use Blockify\Core\Interfaces\Renderable;
use Blockify\Core\Interfaces\Scriptable;
use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Styles;
use Blockify\Core\Utilities\CSS;
use Blockify\Core\Utilities\DOM;
use Blockify\Core\Utilities\Str;
use Blockify\Extensions\ExtensionConfig;
use WP_Block;
use function array_diff;
use function array_keys;
use function array_unique;
use function esc_attr;
use function explode;
use function file_exists;
use function file_get_contents;
use function str_contains;

/**
 * Animation class.
 *
 * @since 1.0.0
 */
class Animation implements Renderable, Styleable, Scriptable {

	/**
	 * Provider.
	 *
	 * @since 1.0.0
	 *
	 * @var ExtensionConfig
	 */
	private ExtensionConfig $config;

	/**
	 * Animation constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param ExtensionConfig $config Data service.
	 *
	 * @return void
	 */
	public function __construct( ExtensionConfig $config ) {
		$this->config = $config;
	}

	public function styles( Styles $styles ): void {
		$styles->add( 'animation' )
			->src( $this->config->url . 'public/animation.css' )
			->template_contains( 'has-animation', 'has-scroll-animation' );

		$styles->add( 'animations' )
			->inline( [ $this, 'get_animation_styles' ] );
	}

	/**
	 * Adds animation scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_html The template HTML.
	 *
	 * @return string
	 */
	public function scripts( string $template_html ): string {
		$scripts->add()
			->handle( 'editor' )
			->localize(
				[
					$scripts->prefix,
					[
						'animations' => array_keys( $this->get_animations() ),
					],
				]
			)
			->context( [ AbstractAssets::EDITOR ] );

		$scripts->add()
			->handle( 'animation' )
			->contains( 'has-animation' );

		$js = '';

		if ( ! $template_html || Str::contains_any( $template_html, 'has-animation', 'has-scroll-animation' ) ) {
			$js .= file_get_contents( $this->config->dir . 'public/animation.js' );
		}

		if ( ! $template_html || Str::contains_any( $template_html, 'animation-event:scroll', 'has-scroll-animation' ) ) {
			$js .= file_get_contents( $this->config->dir . 'public/scroll.js' );
		}

		if ( ! $template_html || Str::contains_any( $template_html, 'packery' ) ) {
			$js .= file_get_contents( $this->config->dir . 'public/packery.js' );
		}

		return $js;
	}

	/**
	 * Adds animation attributes to block.
	 *
	 * @since 0.9.10
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block         The block.
	 * @param WP_Block $instance      The block instance.
	 *
	 * @hook  render_block
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$animation = $block['attrs']['animation'] ?? [];

		if ( empty( $animation ) ) {
			return $block_content;
		}

		$infinite = ( $animation['iterationCount'] ?? null ) === '-1' || ( $animation['event'] ?? null ) === 'infinite';

		$dom   = DOM::create( $block_content );
		$first = DOM::get_element( '*', $dom );

		if ( ! $first ) {
			return $block_content;
		}

		$classes = explode( ' ', $first->getAttribute( 'class' ) );
		$classes = array_unique( $classes );
		$styles  = CSS::string_to_array( $first->getAttribute( 'style' ) );

		unset( $styles['animation-play-state'] );

		if ( $infinite ) {
			unset( $styles['--animation-event'] );

			$styles['animation-iteration-count'] = 'infinite';

		} else {
			unset( $styles['animation-name'] );

			$styles['--animation-name'] = esc_attr( $animation['name'] ?? '' );
		}

		$event = $animation['event'] ?? '';

		if ( $event === 'scroll' ) {
			$classes[] = 'animate';
			$classes[] = 'has-scroll-animation';

			$classes = array_diff( $classes, [ 'has-animation' ] );

			$styles['animation-delay']      = 'calc(var(--scroll) * -1s)';
			$styles['animation-play-state'] = 'paused';
			$styles['animation-duration']   = '1s';
			$styles['animation-fill-mode']  = 'both';

			unset( $styles['--animation-event'] );

			$offset = $animation['offset'] ?? '0';

			if ( $offset === '0' ) {
				$offset = '0.01';
			}

			if ( $offset ) {
				$first->setAttribute( 'data-offset', esc_attr( $offset ) );
			}
		}

		if ( $styles ) {
			$first->setAttribute( 'style', CSS::array_to_string( $styles ) );
		}

		$first->setAttribute( 'class', implode( ' ', $classes ) );

		return $dom->saveHTML();
	}

	/**
	 * Returns inline styles for animations.
	 *
	 * @since 0.9.19
	 *
	 * @param string $template_html The template HTML.
	 *
	 * @return string
	 */
	private function get_animation_styles( string $template_html ): string {
		$animations = $this->get_animations();
		$css        = '';

		foreach ( $animations as $name => $animation ) {
			if ( ! $template_html || str_contains( $template_html, "animation-name:{$name}" ) ) {
				$css .= "@keyframes $name" . trim( $animation );
			}
		}

		return $css;
	}

	/**
	 * Gets animations from stylesheet.
	 *
	 * @since 0.9.18
	 *
	 * @hook  blockify_styles
	 *
	 * @return array
	 */
	private function get_animations(): array {
		$file = $this->config->css_dir . 'block-extensions/animations.css';

		if ( ! file_exists( $file ) ) {
			return [];
		}

		$parts      = explode( '@keyframes', file_get_contents( $file ) );
		$animations = [];

		unset( $parts[0] );

		foreach ( $parts as $animation ) {
			$name = trim( explode( '{', $animation )[0] ?? '' );

			$animations[ $name ] = trim( str_replace( $name, '', $animation ) );
		}

		return $animations;
	}

}
