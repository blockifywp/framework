<?php

declare( strict_types=1 );

namespace Blockify\Extensions\DesignSystem;

use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Assets\Styles;
use function is_array;
use function is_string;
use function str_contains;

/**
 * Base CSS.
 *
 * @since 1.0.0
 */
class BaseCss implements Styleable {

	/**
	 * Registers styles.
	 *
	 * @since 1.0.0
	 *
	 * @param Styles $styles Styles.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$style_groups = $this->get_stylesheets();
		$instance     = $this;

		foreach ( $style_groups as $group => $stylesheets ) {
			foreach ( $stylesheets as $name => $condition ) {
				$styles->add()
					->handle( $group . '-' . $name )
					->src( $group . '/' . $name . '.css' )
					->condition(
						static fn( string $template_html ): bool => $instance->check_condition( $template_html, $condition )
					);
			}
		}

		$styles->remove( 'wp-block-library-theme' );
	}

	/**
	 * Checks if a condition is met.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_html Template HTML.
	 * @param mixed  $condition     Condition.
	 *
	 * @return bool
	 */
	private function check_condition( string $template_html, $condition ): bool {
		if ( ! $template_html ) {
			return true;
		}

		if ( is_string( $condition ) ) {
			return str_contains( $template_html, $condition );
		}

		if ( is_array( $condition ) ) {
			foreach ( $condition as $sub_condition ) {
				if ( is_string( $sub_condition ) && str_contains( $template_html, $sub_condition ) ) {
					return true;
				} elseif ( $sub_condition ) {
					return true;
				}
			}
		}

		return (bool) $condition;
	}

	/**
	 * Adds conditional stylesheets inline.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_stylesheets(): array {
		$styles = [];

		$styles['elements'] = [
			'all'        => true,
			'anchor'     => '<a',
			'big'        => '<big',
			'blockquote' => '<blockquote',
			'body'       => true,
			'button'     => [
				'<button',
				'type="button"',
				'type="submit"',
				'type="reset"',
				'nf-form',
				'wp-element-button',
			],
			'caption'    => 'wp-element-caption',
			'checkbox'   => 'type="checkbox"',
			'cite'       => '<cite',
			'code'       => '<code',
			'hr'         => '<hr',
			'form'       => [
				'<fieldset',
				'<form',
				'<input',
				'nf-form',
				'wp-block-search',
			],
			'heading'    => true,
			'html'       => true,
			'list'       => [ '<ul', '<ol' ],
			'mark'       => '<mark',
			'pre'        => '<pre',
			'radio'      => 'type="radio"',
			'small'      => '<small',
			'strong'     => '<strong',
			'sub'        => '<sub',
			'sup'        => '<sup',
			'svg'        => '<svg',
			'table'      => '<table',
		];

		// Admin bar handled by service.
		$styles['components'] = [
			'border'             => true,
			'edit-link'          => 'edit-link',
			'screen-reader-text' => true,
			'site-blocks'        => true,
		];

		$styles['block-styles'] = [
			'badge'            => 'is-style-badge',
			'button-outline'   => 'is-style-outline',
			'button-secondary' => 'is-style-secondary',
			'button-ghost'     => 'is-style-ghost',
			'check-circle'     => 'is-style-check-circle',
			'check-outline'    => [ 'is-style-check-outline', 'is-style-checklist-circle' ],
			'checklist'        => 'is-style-checklist',
			'curved-text'      => 'is-style-curved-text',
			'divider-angle'    => 'is-style-angle',
			'divider-curve'    => 'is-style-curve',
			'divider-fade'     => 'is-style-fade',
			'divider-round'    => 'is-style-round',
			'divider-wave'     => 'is-style-wave',
			'heading'          => [ 'is-style-heading', 'is-style-summary-heading', 'is-style-list-heading' ],
			'list-dash'        => 'is-style-dash',
			'list-heading'     => 'is-style-heading',
			'list-none'        => 'is-style-none',
			'notice'           => 'is-style-notice',
			'numbered-list'    => 'is-style-numbered',
			'search-toggle'    => 'is-style-toggle',
			'square-list'      => 'is-style-square',
			'sub-heading'      => 'is-style-sub-heading',
			'surface'          => 'is-style-surface',
		];

		$styles['block-variations'] = [
			'accordion' => 'is-style-accordion',
			'counter'   => 'is-style-counter',
			'icon'      => 'is-style-icon',
			'marquee'   => 'is-marquee',
			'svg'       => 'is-style-svg',
		];

		// Placeholder handled by service.
		$styles['block-extensions'] = [
			'animation'     => [ 'has-animation', 'will-animate' ],
			'aspect-ratio'  => 'has-aspect-ratio-',
			'box-shadow'    => 'has-box-shadow',
			'gradient-mask' => '-gradient-background',
			'shadow'        => [ 'has-shadow', 'has-box-shadow', 'has-text-shadow' ],
			'transform'     => 'has-transform',
		];

		$styles['text-formats'] = [
			'animation'  => [ 'has-text-animation', 'typewriter' ],
			'arrow'      => 'is-underline-arrow',
			'brush'      => 'is-underline-brush',
			'circle'     => 'is-underline-circle',
			'scribble'   => 'is-underline-scribble',
			'gradient'   => 'has-text-gradient',
			'highlight'  => 'has-inline-color',
			'underline'  => 'has-text-underline',
			'font-size'  => 'has-inline-font-size',
			'inline-svg' => 'inline-svg',
			'outline'    => 'has-text-outline',
		];

		$styles['utilities'] = [
			'align'     => 'vertical-align-top',
			'dark-mode' => [
				'is-style-light',
				'is-style-dark',
				'hide-light-mode',
				'hide-dark-mode',
			],
			'flex'      => [
				'flex',
				'justify-center',
				'justify-space-between',
				'align-content-center',
				'align-stretch',
			],
			'height'    => [ 'height-100', 'height-auto' ],
			'margin'    => [
				'margin-auto',
				'margin-top-auto',
				'margin-left-auto',
				'margin-right-auto',
				'margin-bottom-auto',
				'no-margin',
			],
			'wrap'      => [ 'nowrap', 'wrap' ],
		];

		return $styles;
	}
}
