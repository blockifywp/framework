<?php

declare( strict_types=1 );

namespace Blockify\Extensions;

use Blockify\Core\Container;
use Blockify\Core\Interfaces\Registerable;
use Blockify\Core\Interfaces\Scriptable;
use Blockify\Core\Interfaces\Styleable;
use Blockify\Core\Services\Hooks;
use Blockify\Core\Services\Scripts;
use Blockify\Core\Services\Styles;
use function is_object;

class ExtensionServiceProvider implements Registerable {

	private array $services = [
		BlockSettings\Animation::class,
		BlockSettings\BackdropBlur::class,
		BlockSettings\BoxShadow::class,
		BlockSettings\CopyToClipboard::class,
		BlockSettings\InlineColor::class,
		BlockSettings\InlineSvg::class,
		BlockSettings\Onclick::class,
		BlockSettings\Opacity::class,
		BlockSettings\Placeholder::class,
		BlockSettings\Responsive::class,
		BlockSettings\SubHeading::class,
		BlockSettings\TemplateTags::class,
		BlockSettings\TextShadow::class,
		BlockSettings\Visibility::class,
		BlockVariations\AccordionList::class,
		BlockVariations\Counter::class,
		BlockVariations\CurvedText::class,
		BlockVariations\Grid::class,
		BlockVariations\Icon::class,
		BlockVariations\Marquee::class,
		BlockVariations\RelatedPosts::class,
		BlockVariations\Svg::class,
		CoreBlocks\Button::class,
		CoreBlocks\Buttons::class,
		CoreBlocks\Calendar::class,
		CoreBlocks\Code::class,
		CoreBlocks\Columns::class,
		CoreBlocks\Cover::class,
		CoreBlocks\Details::class,
		CoreBlocks\Group::class,
		CoreBlocks\Heading::class,
		CoreBlocks\Image::class,
		CoreBlocks\ListBlock::class,
		CoreBlocks\Navigation::class,
		CoreBlocks\NavigationSubmenu::class,
		CoreBlocks\PageList::class,
		CoreBlocks\Paragraph::class,
		CoreBlocks\PostAuthor::class,
		CoreBlocks\PostCommentsForm::class,
		CoreBlocks\PostContent::class,
		CoreBlocks\PostDate::class,
		CoreBlocks\PostExcerpt::class,
		CoreBlocks\PostFeaturedImage::class,
		CoreBlocks\PostTemplate::class,
		CoreBlocks\PostTerms::class,
		CoreBlocks\PostTitle::class,
		CoreBlocks\Query::class,
		CoreBlocks\QueryPagination::class,
		CoreBlocks\QueryTitle::class,
		CoreBlocks\Search::class,
		CoreBlocks\Shortcode::class,
		CoreBlocks\SocialLink::class,
		CoreBlocks\SocialLinks::class,
		CoreBlocks\Spacer::class,
		CoreBlocks\TableOfContents::class,
		CoreBlocks\TagCloud::class,
		CoreBlocks\TemplatePart::class,
		CoreBlocks\Video::class,
		DesignSystem\AdminBar::class,
		DesignSystem\BaseCss::class,
		DesignSystem\BlockCss::class,
		DesignSystem\ChildTheme::class,
		DesignSystem\ConicGradient::class,
		DesignSystem\CustomProperties::class,
		DesignSystem\DarkMode::class,
		DesignSystem\DeprecatedStyles::class,
		DesignSystem\Emojis::class,
		DesignSystem\Layout::class,
		DesignSystem\Patterns::class,
		DesignSystem\SystemFonts::class,
		DesignSystem\Templates::class,
		Integrations\AffiliateWP::class,
		Integrations\BbPress::class,
		Integrations\LemonSqueezy::class,
		Integrations\LifterLMS::class,
		Integrations\NinjaForms::class,
		Integrations\SyntaxHighlightingCodeBlock::class,
		Integrations\WooCommerce::class,
	];

	/**
	 * Hooks service.
	 *
	 * @var Hooks
	 */
	private Hooks $hooks;

	/**
	 * Scripts service.
	 *
	 * @var Scripts
	 */
	private Scripts $scripts;

	/**
	 * Styles service.
	 *
	 * @var Styles
	 */
	private Styles $styles;

	/**
	 * ExtensionServiceProvider constructor.
	 *
	 * @param Hooks   $hooks   Hooks service.
	 * @param Scripts $scripts Scripts service.
	 * @param Styles  $styles  Styles service.
	 */
	public function __construct( Hooks $hooks, Scripts $scripts, Styles $styles ) {
		$this->hooks   = $hooks;
		$this->scripts = $scripts;
		$this->styles  = $styles;
	}

	/**
	 * Register services.
	 *
	 * @param Container $container Container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ): void {
		foreach ( $this->services as $id ) {
			$service = $container->create( $id );

			if ( is_object( $service ) ) {
				$this->hooks->add_annotations( $service );
			}

			if ( $service instanceof Scriptable ) {
				$this->scripts->add_callback( [ $service, 'scripts' ] );
			}

			if ( $service instanceof Styleable ) {
				$this->styles->add_callback( [ $service, 'styles' ] );

				$service->styles( $this->styles );
			}
		}
	}
}
