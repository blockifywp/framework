<?php

declare( strict_types=1 );

namespace Blockify\Extensions\Integrations;

use Blockify\Core\Interfaces\Conditional;
use Blockify\Core\Interfaces\Hookable;
use Blockify\Core\Traits\HookAnnotations;
use function function_exists;
use function wp_get_global_settings;

/**
 * Syntax Highlighting Code Block extension.
 *
 * @since 1.0.0
 */
class SyntaxHighlightingCodeBlock implements Hookable, Conditional {

	use HookAnnotations;

	/**
	 * Condition.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function condition(): bool {
		return function_exists( '\\Syntax_Highlighting_Code_Block\\boot' );
	}

	/**
	 * Set syntax highlighting colors defined in theme.json.
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme The theme to use.
	 *
	 * @hook  syntax_highlighting_code_block_style
	 *
	 * @return string
	 */
	public function set_syntax_highlighting_code_theme( string $theme ): string {
		$global_settings = wp_get_global_settings();

		return $global_settings['custom']['highlightJs'] ?? 'atom-one-dark';
	}

}
