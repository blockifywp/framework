<?php

declare( strict_types=1 );

namespace Blockify\Framework\BlockSettings;

use Blockify\Framework\InlineAssets\Scriptable;
use Blockify\Framework\InlineAssets\Scripts;
use Blockify\Framework\InlineAssets\Styleable;
use Blockify\Framework\InlineAssets\Styles;
use Blockify\Utilities\CSS;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function _wp_to_kebab_case;
use function array_merge;
use function is_admin;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Responsive class.
 *
 * @since 1.0.0
 */
class Responsive implements Renderable, Scriptable, Styleable {

	/**
	 * Responsive settings.
	 *
	 * @var array
	 */
	public array $settings = [
		'position'            => [
			'property' => 'position',
			'label'    => 'Position',
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => 'Relative',
					'value' => 'relative',
				],
				[
					'label' => 'Absolute',
					'value' => 'absolute',
				],
				[
					'label' => 'Sticky',
					'value' => 'sticky',
				],
				[
					'label' => 'Fixed',
					'value' => 'fixed',
				],
				[
					'label' => 'Static',
					'value' => 'static',
				],
			],
		],
		'top'                 => [
			'property' => 'top',
			'label'    => 'Top',
		],
		'right'               => [
			'property' => 'right',
			'label'    => 'Right',
		],
		'bottom'              => [
			'property' => 'bottom',
			'label'    => 'Bottom',
		],
		'left'                => [
			'property' => 'left',
			'label'    => 'Left',
		],
		'zIndex'              => [
			'property' => 'z-index',
			'label'    => 'Z-Index',
		],
		'display'             => [
			'property' => 'display',
			'label'    => 'Display',
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => 'None',
					'value' => 'none',
				],
				[
					'label' => 'Flex',
					'value' => 'flex',
				],
				[
					'label' => 'Inline Flex',
					'value' => 'inline-flex',
				],
				[
					'label' => 'Block',
					'value' => 'block',
				],
				[
					'label' => 'Inline Block',
					'value' => 'inline-block',
				],
				[
					'label' => 'Inline',
					'value' => 'inline',
				],
				[
					'label' => 'Grid',
					'value' => 'grid',
				],
				[
					'label' => 'Inline Grid',
					'value' => 'inline-grid',
				],
				[
					'label' => 'Contents',
					'value' => 'contents',
				],
			],
		],
		'order'               => [
			'property' => 'order',
			'label'    => 'Order',
		],
		'gridTemplateColumns' => [
			'property' => 'grid-template-columns',
			'label'    => 'Columns',
		],
		'gridTemplateRows'    => [
			'property' => 'grid-template-rows',
			'label'    => 'Rows',
		],
		'gridColumnStart'     => [
			'property' => 'grid-column-start',
			'label'    => 'Column Start',
		],
		'gridColumnEnd'       => [
			'property' => 'grid-column-end',
			'label'    => 'Column End',
		],
		'gridRowStart'        => [
			'property' => 'grid-row-start',
			'label'    => 'Row Start',
		],
		'gridRowEnd'          => [
			'property' => 'grid-row-end',
			'label'    => 'Row End',
		],
		'overflow'            => [
			'property' => 'overflow',
			'label'    => 'Overflow',
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => 'Hidden',
					'value' => 'hidden',
				],
				[
					'label' => 'Visible',
					'value' => 'visible',
				],
			],
		],
		'pointerEvents'       => [
			'property' => 'pointer-events',
			'label'    => 'Pointer Events',
			'options'  => [
				[
					'label' => '',
					'value' => '',
				],
				[
					'label' => 'None',
					'value' => 'none',
				],
				[
					'label' => 'All',
					'value' => 'all',
				],
			],
		],
		'width'               => [
			'property' => 'width',
			'label'    => 'Width',
		],
		'minWidth'            => [
			'property' => 'min-width',
			'label'    => 'Min Width',
		],
		'maxWidth'            => [
			'property' => 'max-width',
			'label'    => 'Max Width',
		],
	];

	/**
	 * Image settings.
	 *
	 * @var array
	 */
	private array $image_settings;

	/**
	 * Responsive constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Image $image Image settings.
	 *
	 * @return void
	 */
	public function __construct( Image $image ) {
		$this->image_settings = $image->settings;
	}

	/**
	 * Adds inline block positioning classes.
	 *
	 * @since 1.0.0
	 *
	 * @param string   $block_content Block content.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block instance.
	 *
	 * @hook  render_block 11
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$style = $block['attrs']['style'] ?? [];

		if ( ! $style ) {
			return $block_content;
		}

		$options = $this->settings;

		$block_content = CSS::add_responsive_classes(
			$block_content,
			$block,
			$options
		);

		return CSS::add_responsive_styles(
			$block_content,
			$block,
			$options
		);
	}

	/**
	 * Add default block supports.
	 *
	 * @since 0.9.10
	 *
	 * @param Scripts $scripts Scripts service.
	 *
	 * @return void
	 */
	public function scripts( Scripts $scripts ): void {
		$scripts->add_data(
			'responsiveOptions',
			$this->settings,
			[],
			is_admin()
		);
	}

	/**
	 * Conditionally adds CSS for utility classes
	 *
	 * @since 0.9.19
	 *
	 * @param Styles $styles Styles service.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$styles->add_callback( [ $this, 'get_styles' ] );
	}

	/**
	 * Returns inline styles for responsive classes.
	 *
	 * @since 0.9.19
	 *
	 * @param string $template_html Template HTML.
	 * @param bool   $load_all      Load all assets.
	 *
	 * @return string
	 */
	public function get_styles( string $template_html, bool $load_all ): string {
		$options = array_merge(
			$this->settings,
			$this->image_settings,
		);
		$both    = '';
		$mobile  = '';
		$desktop = '';

		foreach ( $options as $key => $args ) {
			$property       = _wp_to_kebab_case( $key );
			$select_options = $args['options'] ?? [];

			foreach ( $select_options as $option ) {
				$value = $option['value'] ?? '';

				if ( ! $value ) {
					continue;
				}

				$formatted_value = $value;

				if ( 'aspect-ratio' === $property ) {
					$formatted_value = str_replace( '/', '\/', $formatted_value );
				}

				if ( $load_all || str_contains( $template_html, " has-{$property}-{$value}" ) ) {
					$both .= sprintf(
						'.has-%1$s-%3$s{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}

				if ( $load_all || str_contains( $template_html, " has-{$property}-{$value}-mobile" ) ) {
					$mobile .= sprintf(
						'.has-%1$s-%3$s-mobile{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}

				if ( $load_all || str_contains( $template_html, " has-{$property}-{$value}-desktop" ) ) {
					$desktop .= sprintf(
						'.has-%1$s-%3$s-desktop{%1$s:%2$s !important}',
						$property,
						$value,
						$formatted_value,
					);
				}
			}

			// Has custom value.
			if ( ! $select_options ) {

				if ( $load_all || str_contains( $template_html, " has-$property" ) ) {
					$both .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s)}',
						$property
					);
				}

				if ( $load_all || str_contains( $template_html, "--$property-mobile" ) ) {
					$mobile .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s-mobile,var(--%1$s))}',
						$property
					);
				}

				if ( $load_all || str_contains( $template_html, "--$property-desktop" ) ) {
					$desktop .= sprintf(
						'.has-%1$s{%1$s:var(--%1$s-desktop,var(--%1$s))}',
						$property
					);
				}
			}
		}

		$css = '';

		if ( $both ) {
			$css .= $both;
		}

		if ( $mobile ) {
			$css .= sprintf( '@media(max-width:781px){%s}', $mobile );
		}

		if ( $desktop ) {
			$css .= sprintf( '@media(min-width:782px){%s}', $desktop );
		}

		return $css;
	}

}
