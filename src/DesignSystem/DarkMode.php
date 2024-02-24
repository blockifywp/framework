<?php

declare( strict_types=1 );

namespace Blockify\Framework\DesignSystem;

use Blockify\Framework\InlineAssets\Styleable;
use Blockify\Framework\InlineAssets\Styles;
use Blockify\Utilities\Color;
use Blockify\Utilities\CSS;
use Blockify\Utilities\JSON;
use Blockify\Utilities\Str;
use function array_diff;
use function array_replace;
use function array_unique;
use function explode;
use function filter_input;
use function hexdec;
use function in_array;
use function is_null;
use function is_string;
use function ltrim;
use function round;
use function sprintf;
use function substr;
use function wp_get_global_settings;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const INPUT_COOKIE;
use const INPUT_GET;

/**
 * Dark mode.
 *
 * @since 0.9.10
 */
class DarkMode implements Styleable {

	/**
	 * Color shade map.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $map = [
		'primary'   => [
			950 => 50,
			900 => 100,
			800 => 200,
			700 => 300,
			600 => 400,
			500 => 500,
			400 => 600,
			300 => 700,
			200 => 800,
			100 => 900,
			50  => 950,
			25  => 1000,
		],
		'secondary' => [
			950 => 50,
			900 => 100,
			800 => 200,
			700 => 300,
			600 => 400,
			500 => 500,
			400 => 600,
			300 => 700,
			200 => 800,
			100 => 900,
			50  => 950,
			25  => 1000,
		],
		'neutral'   => [
			950 => 0,
			900 => 50,
			800 => 100,
			700 => 200,
			600 => 300,
			500 => 400,
			400 => 500,
			300 => 600,
			200 => 700,
			100 => 800,
			50  => 900,
			0   => 950,
		],
		'success'   => [
			600 => 100,
			500 => 500,
			100 => 600,
		],
		'warning'   => [
			600 => 100,
			500 => 500,
			100 => 600,
		],
		'error'     => [
			600 => 100,
			500 => 500,
			100 => 600,
		],
	];

	/**
	 * Custom properties.
	 *
	 * @since 1.3.0
	 *
	 * @var CustomProperties
	 */
	private CustomProperties $custom_properties;

	/**
	 * DarkMode constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param CustomProperties $custom_properties Custom properties.
	 */
	public function __construct( CustomProperties $custom_properties ) {
		$this->custom_properties = $custom_properties;
	}

	/**
	 * Sets default body class.
	 *
	 * @since 0.9.10
	 *
	 * @param array $classes Body classes.
	 *
	 * @hook  body_class
	 *
	 * @return array
	 */
	public function add_dark_mode_body_class( array $classes ): array {
		$cookie          = filter_input( INPUT_COOKIE, 'blockifyDarkMode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$url_param       = filter_input( INPUT_GET, 'dark_mode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$stylesheet_dir  = get_stylesheet_directory();
		$global_settings = wp_get_global_settings();
		$default_mode    = $global_settings['custom']['darkMode']['defaultMode'] ?? 'light';
		$both_classes    = [ 'is-style-light', 'is-style-dark' ];

		$classes[] = 'default-mode-' . $default_mode;

		if ( ! $cookie ) {
			$classes[] = 'is-style-' . $default_mode;
		}

		if ( $cookie === 'true' ) {
			$classes[] = 'is-style-dark';
		} else {
			if ( $cookie === 'false' ) {
				$classes[] = 'is-style-light';
			} else {
				if ( $cookie === 'auto' ) {
					$classes = array_diff( $classes, $both_classes );

					$classes[] = 'default-mode-auto';
				}
			}
		}

		if ( $url_param ) {
			$classes = array_diff( $classes, $both_classes );

			$classes[] = $url_param === 'true' ? 'is-style-dark' : 'is-style-light';
		}

		return array_unique( $classes );
	}

	/**
	 * Adds dark mode styles.
	 *
	 * @since 1.3.0
	 *
	 * @param Styles $styles Styles.
	 *
	 * @return void
	 */
	public function styles( Styles $styles ): void {
		$settings          = wp_get_global_settings();
		$palette           = $settings['color']['palette']['theme'] ?? [];
		$custom            = array_replace(
			JSON::compute_theme_vars( $settings['custom'] ?? [] ),
			$this->custom_properties->get_custom_properties(),
		);
		$colors            = Color::get_color_values( $palette );
		$gradients         = Color::get_color_values( $settings['color']['gradients']['theme'] ?? [], 'gradient' );
		$system            = Color::get_system_colors();
		$light_settings    = $settings['custom']['lightMode'] ?? null;
		$dark_settings     = $settings['custom']['darkMode'] ?? null;
		$opposite_settings = $light_settings ?? $dark_settings ?? null;
		$default_mode      = 'light';
		$opposite_mode     = 'dark'; // $default_mode === 'light' ? 'dark' : 'light';
		$default_styles    = [];
		$opposite_styles   = [];

		foreach ( $colors as $slug => $value ) {
			$explode = explode( '-', $slug );
			$name    = $explode[0] ?? '';
			$shade   = $explode[1] ?? '';

			if ( is_null( $shade ) || in_array( $slug, $system, true ) ) {
				continue;
			}

			$default_styles["--wp--preset--color--{$slug}"] = $value;

			$opposite_shade = $this->map[ $name ][ $shade ] ?? '';
			$opposite_value = $colors[ $name . '-' . $opposite_shade ] ?? '';

			if ( ( (int) $opposite_shade ?? 0 ) === 1000 ) {
				$opposite_value = $this->darken( $colors[ $name . '-950' ] ?? '', 50 );
			}

			if ( isset( $opposite_settings['palette'][ $slug ] ) ) {
				$opposite_value = $opposite_settings['palette'][ $slug ];
			}

			if ( $opposite_value ) {
				$opposite_styles["--wp--preset--color--{$slug}"] = $opposite_value;
			}
		}

		foreach ( $gradients as $slug => $value ) {
			$default_styles["--wp--preset--gradient--{$slug}"] = $value;

			$opposite_value = $opposite_settings['gradients'][ $slug ] ?? $value;

			$opposite_styles["--wp--preset--gradient--{$slug}"] = $opposite_value;
		}

		foreach ( $custom as $name => $value ) {
			if ( is_string( $value ) && Str::contains_any( $value, '--wp--preset--color--', '--wp--preset--gradient--' ) ) {
				$default_styles[ $name ]  = $value;
				$opposite_styles[ $name ] = $value;
			}
		}

		$css = "html .is-style-{$default_mode}{" . CSS::array_to_string( $default_styles ) . '}';
		$css .= "html .is-style-{$opposite_mode}{" . CSS::array_to_string( $opposite_styles ) . '}';

		$styles->add_string( $css );
	}

	/**
	 * Darkens a hex color by a given percentage.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hex        Hex color.
	 * @param int    $percentage Percentage.
	 *
	 * @return string
	 */
	private function darken( string $hex, int $percentage ): string {
		$hex        = ltrim( $hex, '#' );
		$percentage = $percentage / 100;

		// Convert the hex color to RGB.
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Reduce each color component by half (mix with 50% black).
		$r = round( $r * $percentage );
		$g = round( $g * $percentage );
		$b = round( $b * $percentage );

		return sprintf( "#%02x%02x%02x", $r, $g, $b );
	}

}
