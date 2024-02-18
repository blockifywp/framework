<?php

declare( strict_types=1 );

namespace Blockify\Framework\BlockVariations;

use Blockify\Framework\BlockSettings\Responsive;
use Blockify\Utilities\CSS;
use Blockify\Utilities\DOM;
use Blockify\Utilities\Icon as IconUtility;
use Blockify\Utilities\Interfaces\Renderable;
use WP_Block;
use function in_array;
use function is_array;
use function str_contains;
use function str_replace;

/**
 * Icon class.
 *
 * @since 1.0.0
 */
class Icon implements Renderable {

	/**
	 * Responsive settings.
	 *
	 * @var array
	 */
	private array $responsive_settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Responsive $responsive Responsive settings.
	 *
	 * @return void
	 */
	public function __construct( Responsive $responsive ) {
		$this->responsive_settings = $responsive->settings;
	}

	/**
	 * Modifies front end HTML output of block.
	 *
	 * @since 0.0.2
	 *
	 * @param string   $block_content Block HTML.
	 * @param array    $block         Block data.
	 * @param WP_Block $instance      Block object.
	 *
	 * @hook  render_block 12
	 *
	 * @return string
	 */
	public function render( string $block_content, array $block, WP_Block $instance ): string {
		$attrs    = $block['attrs'] ?? [];
		$has_icon = ( $attrs['iconSet'] ?? '' ) && ( $attrs['iconName'] ?? '' ) || ( $attrs['iconSvgString'] ?? '' );

		if ( ! $has_icon ) {
			return $block_content;
		}

		$attrs    = $block['attrs'] ?? [];
		$icon_set = $attrs['iconSet'] ?? strtolower( 'WordPress' );

		$block_content = ! $block_content ? '<figure class="wp-block-image is-style-icon"><img src="" alt=""/></figure>' : $block_content;
		$dom           = DOM::create( $block_content );
		$figure        = DOM::get_element( 'figure', $dom );
		$img           = DOM::get_element( 'img', $figure );

		if ( ! $figure || ! $img ) {
			return $block_content;
		}

		$span         = DOM::change_tag_name( 'span', $img );
		$icon_name    = $attrs['iconName'] ?? 'star-empty';
		$gradient     = $attrs['gradient'] ?? null;
		$span_classes = [ 'wp-block-image__icon' ];

		if ( $gradient ) {
			$span_classes[] = 'has-gradient';
		}

		$figure_classes      = explode( ' ', $figure->getAttribute( 'class' ) );
		$block_extras        = $this->responsive_settings;
		$block_extra_classes = [];

		foreach ( $block_extras as $name => $args ) {
			$block_extra_classes[] = 'has-' . $args['property'];

			if ( ! isset( $args['options'] ) ) {
				continue;
			}

			foreach ( $args['options'] as $option ) {
				$block_extra_classes[] = 'has-' . $args['property'] . '-' . $option['value'];
			}
		}

		foreach ( $figure_classes as $index => $class ) {
			if ( ! str_contains( $class, 'has-' ) ) {
				continue;
			}

			if ( in_array( $class, $block_extra_classes, true ) ) {
				continue;
			}

			if ( str_contains( $class, 'has-display' ) ) {
				continue;
			}

			if ( str_contains( $class, 'has-' ) && str_contains( $class, '-background' ) ) {
				unset( $figure_classes[ $index ] );
				continue;
			}

			$span_classes[] = $class;
			unset( $figure_classes[ $index ] );
		}

		$text_color = $attrs['textColor'] ?? null;

		if ( $text_color ) {
			$figure_classes[] = "has-{$text_color}-color";
		}

		$aria_label = $img->getAttribute( 'alt' ) ? $img->getAttribute( 'alt' ) : str_replace( '-', ' ', $icon_name ) . __( ' icon', 'blockify' );

		$span->setAttribute( 'title', $attrs['title'] ?? $aria_label );

		if ( ! ( $attrs['title'] ?? null ) || ! $aria_label ) {
			$span->setAttribute( 'role', 'img' );
		}

		$span->removeAttribute( 'src' );
		$span->removeAttribute( 'alt' );

		$figure_styles = CSS::string_to_array( $figure->getAttribute( 'style' ) );
		$span_styles   = CSS::string_to_array( $span->getAttribute( 'style' ) );
		$properties    = wp_list_pluck( array_values( $block_extras ), 'property' );

		$custom_properties = array_map(
			static fn( string $property ): string => '--' . $property,
			$properties
		);

		foreach ( $figure_styles as $key => $value ) {
			if ( in_array( $key, $properties, true ) ) {
				continue;
			}

			if ( in_array( $key, $custom_properties, true ) ) {
				continue;
			}

			if ( str_contains( $key, 'margin' ) ) {
				continue;
			}

			$span_styles[ $key ] = $value;
			unset( $figure_styles[ $key ] );
		}

		$svg_string = $attrs['iconSvgString'] ?? IconUtility::get_svg( $icon_set, $icon_name );

		if ( $gradient && $svg_string ) {
			$span_styles['--wp--custom--icon--url'] = 'url(\'data:image/svg+xml;utf8,' . $svg_string . '\')';
		} else {
			unset( $span_styles['--wp--custom--icon--url'] );
		}

		$size = $attrs['iconSize'] ?? null;

		if ( $gradient && $size ) {
			$span_styles['--wp--custom--icon--size'] = $size;
		} else {
			unset( $span_styles['--wp--custom--icon--size'] );
		}

		$custom_text_color = $attrs['style']['color']['text'] ?? null;

		if ( $custom_text_color ) {
			$figure_styles['--wp--custom--icon--color'] = $custom_text_color;
		}

		$background_color = $attrs['backgroundColor'] ?? null;

		if ( $background_color ) {
			$figure_styles['--wp--custom--icon--background'] = "var(--wp--preset--color--{$background_color})";
		}

		if ( $gradient ) {

			if ( $text_color || $custom_text_color ) {
				$figure_styles['--wp--custom--icon--background'] = "var(--wp--preset--gradient--{$gradient})";
			} else {
				$figure_styles['--wp--custom--icon--color'] = "var(--wp--preset--gradient--{$gradient})";
			}
		}

		$border_radius = $attrs['style']['border']['radius'] ?? null;

		if ( $border_radius ) {
			$span_styles['border-radius'] = $border_radius;
		}

		$padding = $attrs['style']['spacing']['padding'] ?? null;

		if ( $padding ) {
			$span_styles = CSS::add_shorthand_property( $span_styles, 'padding', $padding );
		}

		$transform       = $attrs['style']['transform'] ?? [];
		$transform_units = [
			'rotate'    => 'deg',
			'skew'      => 'deg',
			'scale'     => '',
			'translate' => '',
		];

		if ( ! empty( $transform ) && is_array( $transform ) ) {
			$figure_classes[] = 'has-transform';
			$transform_value  = '';

			foreach ( $transform as $key => $value ) {
				if ( $key === 'hover' ) {
					$hover_transform = '';

					foreach ( $value as $hover_key => $hover_value ) {
						$unit            = $transform_units[ $hover_key ] ?? '';
						$hover_transform .= "{$hover_key}({$hover_value}{$unit}) ";
					}

					$figure_styles['--transform-hover'] = $hover_transform;

				} else {
					$unit            = $transform_units[ $key ] ?? '';
					$transform_value .= "{$key}({$value}{$unit}) ";
				}
			}

			if ( ! in_array( 'has-transform', $span_classes, true ) ) {
				$figure_styles['transform'] = $transform_value;
			}
		}

		$figure->setAttribute( 'class', implode( ' ', $figure_classes ) );
		$span->setAttribute( 'class', implode( ' ', $span_classes ) );
		$figure->setAttribute( 'style', CSS::array_to_string( $figure_styles ) );
		$span->setAttribute( 'style', CSS::array_to_string( $span_styles ) );

		$link = DOM::get_element( 'a', $figure );

		if ( $link ) {
			$link->appendChild( $span );
		} else {
			$figure->appendChild( $span );
		}

		if ( ! $gradient ) {
			$icon = IconUtility::get_svg( $icon_set, $icon_name, $size );

			if ( $icon ) {
				$icon_dom      = DOM::create( $icon );
				$imported_icon = $dom->importNode( $icon_dom->firstChild, true );

				$span->appendChild( $imported_icon );
			}
		}

		$block_content = $dom->saveHTML();
		$block_content = CSS::add_responsive_classes(
			$block_content,
			$block,
			$this->responsive_settings
		);
		$block_content = CSS::add_responsive_styles(
			$block_content,
			$block,
			$this->responsive_settings
		);

		return $block_content;

	}

}
