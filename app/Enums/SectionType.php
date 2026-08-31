<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SectionType: string implements HasLabel
{
    case HomeHero = 'home_hero';
    case HomeTrustBar = 'home_trust_bar';
    case HomeWhatsappBanner = 'home_whatsapp_banner';
    case Instagram = 'instagram';
    case InstagramPost = 'instagram_post';
    case Testimonial = 'testimonial';
    case About = 'about';
    case FaqGroup = 'faq_group';
    case Portfolio = 'portfolio';

    public function getLabel(): string
    {
        return match ($this) {
            self::HomeHero => 'Home · Topo (hero)',
            self::HomeTrustBar => 'Home · Barra de confiança',
            self::HomeWhatsappBanner => 'Home · Banner WhatsApp',
            self::Instagram => 'Instagram',
            self::InstagramPost => 'Post do Instagram',
            self::Testimonial => 'Depoimento',
            self::About => 'Sobre',
            self::FaqGroup => 'Grupo de dúvidas',
            self::Portfolio => 'Projeto do portfólio',
        };
    }

    /**
     * Singleton section types have exactly one row, seeded up front and only
     * ever edited — never created or deleted through the admin.
     */
    public function isSingleton(): bool
    {
        return match ($this) {
            self::HomeHero, self::HomeTrustBar, self::HomeWhatsappBanner, self::Instagram, self::About => true,
            self::InstagramPost, self::Testimonial, self::FaqGroup, self::Portfolio => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function singletons(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type->isSingleton()));
    }

    /**
     * Whether Cae can turn this section on/off from the admin, independent
     * of individual item status. `InstagramPost` piggybacks on `Instagram`'s
     * toggle instead of having its own — hiding the intro also hides the
     * grid of posts under it.
     */
    public function isTogglable(): bool
    {
        return $this !== self::InstagramPost;
    }

    /**
     * @return list<self>
     */
    public static function togglables(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type->isTogglable()));
    }
}
