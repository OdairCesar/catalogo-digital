<?php

namespace App\ViewModels;

final readonly class LandingPageViewModel
{
    /**
     * @param  array<int, string>  $benefits
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @param  array<int, array{label: string, url?: string}>  $breadcrumbs
     * @param  array<int, array{label: string, url: string}>  $relatedLinks
     * @param  array<int, array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $h1,
        public string $subtitle,
        public string $intro,
        public array $benefits,
        public array $faq,
        public string $ctaLabel,
        public SeoMeta $seo,
        public array $breadcrumbs = [],
        public array $relatedLinks = [],
        public array $jsonLd = [],
    ) {}

    /**
     * @return array{
     *     h1: string,
     *     subtitle: string,
     *     intro: string,
     *     benefits: array<int, string>,
     *     faq: array<int, array{question: string, answer: string}>,
     *     ctaLabel: string,
     *     seo: array{title: string, description: string, canonical: string, robots: string},
     *     breadcrumbs: array<int, array{label: string, url?: string}>,
     *     relatedLinks: array<int, array{label: string, url: string}>,
     *     jsonLd: array<int, array<string, mixed>>,
     * }
     */
    public function toArray(): array
    {
        return [
            'h1' => $this->h1,
            'subtitle' => $this->subtitle,
            'intro' => $this->intro,
            'benefits' => $this->benefits,
            'faq' => $this->faq,
            'ctaLabel' => $this->ctaLabel,
            'seo' => $this->seo->toArray(),
            'breadcrumbs' => $this->breadcrumbs,
            'relatedLinks' => $this->relatedLinks,
            'jsonLd' => $this->jsonLd,
        ];
    }

    /**
     * @param  array{
     *     h1: string,
     *     subtitle: string,
     *     intro: string,
     *     benefits: array<int, string>,
     *     faq: array<int, array{question: string, answer: string}>,
     *     ctaLabel: string,
     *     seo: array{title: string, description: string, canonical: string, robots: string},
     *     breadcrumbs: array<int, array{label: string, url?: string}>,
     *     relatedLinks: array<int, array{label: string, url: string}>,
     *     jsonLd: array<int, array<string, mixed>>,
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            h1: $data['h1'],
            subtitle: $data['subtitle'],
            intro: $data['intro'],
            benefits: $data['benefits'],
            faq: $data['faq'],
            ctaLabel: $data['ctaLabel'],
            seo: SeoMeta::fromArray($data['seo']),
            breadcrumbs: $data['breadcrumbs'],
            relatedLinks: $data['relatedLinks'],
            jsonLd: $data['jsonLd'],
        );
    }
}
