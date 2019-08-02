<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace unionco\components\enum;

/**
 * The PeriodType class is an abstract class that defines the various time period lengths that are available in Craft.
 * This class is a poor man's version of an enum, since PHP does not have support for native enumerations.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
abstract class Components
{
    // Constants
    // =========================================================================

    const Accordion = 'Accordion';
    const FlexibleContent = 'Flexible Content';
    const CopyContent = 'Copy Content';
    const FeatureList = 'Feature List';
    const CardGrid = 'Card Grid';
    const CardMasonry = 'Card Masonry';
    const Carousel = 'Carousel';
    const CarouselHero = 'Hero Carousel';
    const CarouselText = 'Text Carousel';
    const GalleryGrid = 'Grid Gallery';
    const GalleryMasonry = 'Masonry Gallery';
    const SpanningStripe = 'Spanning Stripe';
    const LinkList = 'Link List';
    const Reviews = 'Reviews';
    const Tabs = 'Tabs';

    const ALL = [
        self::Accordion,
        self::CardGrid,
        self::CardMasonry,
        self::Carousel,
        self::CarouselHero,
        self::CarouselText,
        self::CopyContent,
        self::FlexibleContent,
        self::FeatureList,
        self::GalleryGrid,
        self::GalleryMasonry,
        self::LinkList,
        self::Reviews,
        self::SpanningStripe,
        self::Tabs,
    ];
}
