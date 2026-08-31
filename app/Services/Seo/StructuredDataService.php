<?php

namespace App\Services\Seo;

use App\Models\City;
use App\Models\Company;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceClusterLandingPage;
use Illuminate\Support\Facades\Storage;

final class StructuredDataService
{
    public function __construct(private readonly ContentComposer $composer) {}

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $company = Company::current();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->siteName(),
            'url' => route('home'),
            'telephone' => $company?->whatsapp,
            'email' => $company?->email,
            'address' => $this->addressSchema($company),
            'description' => $company?->short_description,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, string>|null
     */
    private function addressSchema(?Company $company): ?array
    {
        if ($company === null) {
            return null;
        }

        $schema = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $company->streetLine(),
            'addressLocality' => $company->address_city,
            'addressRegion' => $company->address_state,
            'postalCode' => $company->address_zip_code,
            'addressCountry' => 'BR',
        ], fn (?string $value): bool => $value !== null);

        // Only @type and addressCountry present means no real address was filled in yet.
        return count($schema) > 2 ? $schema : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function localBusiness(City $city): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => "{$this->siteName()} - {$city->name}",
            'url' => route('cities.show', $city),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $city->name,
                'addressRegion' => $city->state?->uf,
                'addressCountry' => 'BR',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function service(LandingPage $landingPage): array
    {
        return $this->serviceForCity($landingPage->service->title, $landingPage->service->subtitle, $landingPage->city);
    }

    /**
     * @return array<string, mixed>
     */
    public function serviceForClusterCity(ServiceClusterLandingPage $pivot): array
    {
        return $this->serviceForCity($pivot->serviceCluster->title, $pivot->serviceCluster->subtitle, $pivot->city);
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceForCity(?string $name, ?string $description, City $city): array
    {
        $name = (string) $name;
        $hasLocationToken = $this->composer->hasLocationTokens($name);
        $composedName = $this->composer->compose($name, $city);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $hasLocationToken ? $composedName : "{$composedName} em {$city->name}",
            'description' => $description !== null ? $this->composer->compose($description, $city) : null,
            'areaServed' => [
                '@type' => 'City',
                'name' => $city->name,
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'url' => route('home'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serviceGeneric(Service $service, string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'description' => $description,
            'provider' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'url' => route('home'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function product(Product $product): array
    {
        $image = $product->displayImage();

        $offer = array_filter([
            '@type' => 'Offer',
            'url' => route('products.show', $product),
            'priceCurrency' => 'BRL',
            'price' => $product->effectivePrice(),
            'availability' => $product->effectiveStock() === 0 ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
        ], fn (mixed $value): bool => $value !== null);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $product->description !== null ? strip_tags($product->description) : null,
            'image' => $image !== null ? Storage::disk('cloudinary')->url($image) : null,
            'sku' => $product->sku,
            'gtin' => $product->gtin,
            'mpn' => $product->mpn,
            'brand' => $product->brand !== null ? ['@type' => 'Brand', 'name' => $product->brand] : null,
            'offers' => $offer,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faq
     * @return array<string, mixed>|null
     */
    public function faqPage(array $faq): ?array
    {
        if ($faq === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ], $faq),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function blogPosting(Post $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->meta_description ?? strip_tags((string) $post->excerpt),
            'image' => $post->cover_image ? Storage::disk('cloudinary')->url($post->cover_image) : null,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'url' => route('home'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->siteName(),
                'url' => route('home'),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post),
            ],
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>|null  ...$blocks
     * @return array<int, array<string, mixed>>
     */
    public function combine(?array ...$blocks): array
    {
        return array_values(array_filter($blocks));
    }

    /**
     * @param  array<int, array{label: string, url?: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'] ?? null,
            ], fn (mixed $value): bool => $value !== null))->all(),
        ];
    }

    private function siteName(): string
    {
        return Company::siteName();
    }
}
