<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Enums\SiteModule;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Section;
use App\Models\SectionTypeSetting;
use App\Services\Products\ProductViewModelFactory;
use App\Services\Seo\StructuredDataService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct(
        private readonly StructuredDataService $structuredData,
        private readonly ProductViewModelFactory $productViewModelFactory,
    ) {}

    public function index(): View
    {
        $productsEnabled = SiteModule::Produtos->isEnabled();
        $instagramEnabled = SectionTypeSetting::isEnabled(SectionType::Instagram);
        $whatsappBanner = SectionTypeSetting::isEnabled(SectionType::HomeWhatsappBanner)
            ? Section::block(SectionType::HomeWhatsappBanner)
            : null;

        $categories = $this->sectionData($productsEnabled, fn (): Collection => ProductCategory::query()
            ->whereIn('id', Product::query()->active()->select('product_category_id'))
            ->orderBy('name')
            ->take(6)
            ->get());

        $products = $this->sectionData($productsEnabled, fn (): Collection => Product::query()->active()->with(['company', 'category', 'variants.attributeValues.attribute'])->latest()->take(8)->get()
            ->map(fn (Product $product): array => $this->productViewModelFactory->teaser($product)));

        return view('pages.home', [
            'company' => Company::current(),
            'categories' => $categories,
            'products' => $products,
            'productsEnabled' => $productsEnabled,
            'hero' => SectionTypeSetting::isEnabled(SectionType::HomeHero) ? Section::block(SectionType::HomeHero) : null,
            'trustBar' => SectionTypeSetting::isEnabled(SectionType::HomeTrustBar) ? Section::block(SectionType::HomeTrustBar) : null,
            'instagramBlock' => $instagramEnabled ? Section::block(SectionType::Instagram) : null,
            'instagramPosts' => $instagramEnabled ? $this->instagramPosts() : [],
            'whatsappBanner' => $whatsappBanner,
            'whatsappBannerInitial' => Str::upper(Str::substr($whatsappBanner?->title ?? '', 0, 1)),
            'testimonials' => SectionTypeSetting::isEnabled(SectionType::Testimonial) ? $this->testimonials() : [],
            'jsonLd' => [$this->structuredData->organization()],
        ]);
    }

    /**
     * @template TValue
     *
     * @param  Closure(): Collection<int, TValue>  $builder
     * @return Collection<int, TValue>
     */
    private function sectionData(bool $enabled, Closure $builder): Collection
    {
        return $enabled ? $builder() : collect();
    }

    /**
     * @return list<array{text: ?string, initial: string, name: ?string, detail: ?string, extra_fields: array<int, array{label: string, value: string}>}>
     */
    private function testimonials(): array
    {
        return Section::query()->ofType(SectionType::Testimonial)->active()->ordered()->get()
            ->map(fn (Section $testimonial): array => [
                'text' => $testimonial->content,
                'name' => $testimonial->data['author_name'] ?? null,
                'detail' => $testimonial->data['author_detail'] ?? null,
                'initial' => Str::upper(Str::substr($testimonial->data['author_name'] ?? '', 0, 1)),
                'extra_fields' => $testimonial->extra_fields ?? [],
            ])
            ->all();
    }

    /**
     * @return list<array{imageUrl: string, link: ?string, caption: ?string}>
     */
    private function instagramPosts(): array
    {
        return Section::query()->ofType(SectionType::InstagramPost)->active()->ordered()->get()
            ->filter(fn (Section $post): bool => $post->image !== null)
            ->map(fn (Section $post): array => [
                'imageUrl' => Storage::disk('cloudinary')->url($post->image),
                'link' => $post->data['link'] ?? null,
                'caption' => $post->content,
            ])
            ->values()
            ->all();
    }
}
