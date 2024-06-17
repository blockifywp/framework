<?php

declare( strict_types=1 );

use Blockify\Container\ContainerFactory;
use Blockify\Framework\InlineAssets\Scriptable;
use Blockify\Framework\InlineAssets\Scripts;
use Blockify\Framework\InlineAssets\Styleable;
use Blockify\Framework\InlineAssets\Styles;
use Blockify\Hooks\Hook;

/**
 * Blockify singleton.
 *
 * @since 0.1.0
 */
final class Blockify {

	/**
	 * Services.
	 *
	 * @since 1.0.0
	 *
	 * @var string[]
	 */
	public const SERVICES = [
		Blockify\Framework\BlockSettings\AdditionalStyles::class,
		Blockify\Framework\BlockSettings\Animation::class,
		Blockify\Framework\BlockSettings\BackdropBlur::class,
		Blockify\Framework\BlockSettings\BoxShadow::class,
		Blockify\Framework\BlockSettings\CopyToClipboard::class,
		Blockify\Framework\BlockSettings\CssFilter::class,
		Blockify\Framework\BlockSettings\Image::class,
		Blockify\Framework\BlockSettings\InlineColor::class,
		Blockify\Framework\BlockSettings\InlineSvg::class,
		Blockify\Framework\BlockSettings\Onclick::class,
		Blockify\Framework\BlockSettings\Opacity::class,
		Blockify\Framework\BlockSettings\Placeholder::class,
		Blockify\Framework\BlockSettings\Responsive::class,
		Blockify\Framework\BlockSettings\SubHeading::class,
		Blockify\Framework\BlockSettings\TemplateTags::class,
		Blockify\Framework\BlockSettings\TextShadow::class,
		Blockify\Framework\BlockVariations\AccordionList::class,
		Blockify\Framework\BlockVariations\Counter::class,
		Blockify\Framework\BlockVariations\CurvedText::class,
		Blockify\Framework\BlockVariations\Grid::class,
		Blockify\Framework\BlockVariations\Icon::class,
		Blockify\Framework\BlockVariations\Marquee::class,
		Blockify\Framework\BlockVariations\Newsletter::class,
		Blockify\Framework\BlockVariations\RelatedPosts::class,
		Blockify\Framework\BlockVariations\Svg::class,
		Blockify\Framework\CoreBlocks\Button::class,
		Blockify\Framework\CoreBlocks\Buttons::class,
		Blockify\Framework\CoreBlocks\Calendar::class,
		Blockify\Framework\CoreBlocks\Code::class,
		Blockify\Framework\CoreBlocks\Columns::class,
		Blockify\Framework\CoreBlocks\Cover::class,
		Blockify\Framework\CoreBlocks\Details::class,
		Blockify\Framework\CoreBlocks\Group::class,
		Blockify\Framework\CoreBlocks\Heading::class,
		Blockify\Framework\CoreBlocks\Image::class,
		Blockify\Framework\CoreBlocks\ListBlock::class,
		Blockify\Framework\CoreBlocks\Navigation::class,
		Blockify\Framework\CoreBlocks\NavigationSubmenu::class,
		Blockify\Framework\CoreBlocks\PageList::class,
		Blockify\Framework\CoreBlocks\Paragraph::class,
		Blockify\Framework\CoreBlocks\PostAuthor::class,
		Blockify\Framework\CoreBlocks\PostCommentsForm::class,
		Blockify\Framework\CoreBlocks\PostContent::class,
		Blockify\Framework\CoreBlocks\PostDate::class,
		Blockify\Framework\CoreBlocks\PostExcerpt::class,
		Blockify\Framework\CoreBlocks\PostFeaturedImage::class,
		Blockify\Framework\CoreBlocks\PostTemplate::class,
		Blockify\Framework\CoreBlocks\PostTerms::class,
		Blockify\Framework\CoreBlocks\PostTitle::class,
		Blockify\Framework\CoreBlocks\Query::class,
		Blockify\Framework\CoreBlocks\QueryPagination::class,
		Blockify\Framework\CoreBlocks\QueryTitle::class,
		Blockify\Framework\CoreBlocks\Search::class,
		Blockify\Framework\CoreBlocks\Shortcode::class,
		Blockify\Framework\CoreBlocks\SocialLink::class,
		Blockify\Framework\CoreBlocks\SocialLinks::class,
		Blockify\Framework\CoreBlocks\Spacer::class,
		Blockify\Framework\CoreBlocks\TableOfContents::class,
		Blockify\Framework\CoreBlocks\TagCloud::class,
		Blockify\Framework\CoreBlocks\TemplatePart::class,
		Blockify\Framework\CoreBlocks\Video::class,
		Blockify\Framework\DesignSystem\AdminBar::class,
		Blockify\Framework\DesignSystem\BaseCss::class,
		Blockify\Framework\DesignSystem\BlockCss::class,
		Blockify\Framework\DesignSystem\BlockStyles::class,
		Blockify\Framework\DesignSystem\BlockScripts::class,
		Blockify\Framework\DesignSystem\BlockSupports::class,
		Blockify\Framework\DesignSystem\ChildTheme::class,
		Blockify\Framework\DesignSystem\ConicGradient::class,
		Blockify\Framework\DesignSystem\CustomProperties::class,
		Blockify\Framework\DesignSystem\DarkMode::class,
		Blockify\Framework\DesignSystem\DeprecatedStyles::class,
		Blockify\Framework\DesignSystem\Emojis::class,
		Blockify\Framework\DesignSystem\EditorAssets::class,
		Blockify\Framework\DesignSystem\Layout::class,
		Blockify\Framework\DesignSystem\Patterns::class,
		Blockify\Framework\DesignSystem\SystemFonts::class,
		Blockify\Framework\DesignSystem\Templates::class,
		Blockify\Framework\Integrations\AffiliateWP::class,
		Blockify\Framework\Integrations\BbPress::class,
		Blockify\Framework\Integrations\GravityForms::class,
		Blockify\Framework\Integrations\LemonSqueezy::class,
		Blockify\Framework\Integrations\LifterLMS::class,
		Blockify\Framework\Integrations\NinjaForms::class,
		Blockify\Framework\Integrations\SyntaxHighlightingCodeBlock::class,
		Blockify\Framework\Integrations\WooCommerce::class,
	];

	/**
	 * Registers container with service provider.
	 *
	 * @param string $file Main plugin or theme file.
	 *
	 * @return void
	 */
	public static function register( string $file ): void {
		static $container = null;

		if ( ! is_null( $container ) || ! file_exists( $file ) ) {
			return;
		}

		if ( ! did_action( 'after_setup_theme' ) ) {
			add_action(
				'after_setup_theme',
				static fn() => self::register( $file )
			);
			return;
		}

		$container = ContainerFactory::create( self::class );
		$scripts   = $container->make( Scripts::class, $file );
		$styles    = $container->make( Styles::class, $file );

		foreach ( self::SERVICES as $id ) {
			$service = $container->make( $id );

			if ( is_object( $service ) ) {
				Hook::annotations( $service );
			}

			if ( $service instanceof Scriptable ) {
				$service->scripts( $scripts );
			}

			if ( $service instanceof Styleable ) {
				$service->styles( $styles );
			}
		}

		Hook::annotations( $scripts );
		Hook::annotations( $styles );
	}
}

