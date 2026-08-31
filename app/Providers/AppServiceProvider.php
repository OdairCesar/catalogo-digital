<?php

namespace App\Providers;

use App\Enums\SectionType;
use App\Enums\SiteModule;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\LandingPage;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Section;
use App\Models\SectionTypeSetting;
use App\Models\Service;
use App\Models\ServiceCluster;
use App\Models\ServiceClusterLandingPage;
use App\Models\State;
use App\Observers\CategoryObserver;
use App\Observers\CityObserver;
use App\Observers\CompanyObserver;
use App\Observers\LandingPageObserver;
use App\Observers\PostObserver;
use App\Observers\SectionObserver;
use App\Observers\SectionTypeSettingObserver;
use App\Observers\ServiceClusterLandingPageObserver;
use App\Observers\ServiceClusterObserver;
use App\Observers\ServiceObserver;
use App\Services\Tools\ToolDefinition;
use App\Services\Tools\ToolRegistry;
use App\Support\ClientIp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('telescope.enabled')) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Service::observe(ServiceObserver::class);
        City::observe(CityObserver::class);
        Company::observe(CompanyObserver::class);
        LandingPage::observe(LandingPageObserver::class);
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        ServiceCluster::observe(ServiceClusterObserver::class);
        ServiceClusterLandingPage::observe(ServiceClusterLandingPageObserver::class);
        Section::observe(SectionObserver::class);
        SectionTypeSetting::observe(SectionTypeSettingObserver::class);

        $this->bindRouteParameters();
        $this->configureRateLimiting();
        $this->composeViews();
    }

    /**
     * Shares the current company with the layout components that render its
     * institutional data, so each one doesn't have to fetch it itself.
     */
    private function composeViews(): void
    {
        View::composer(
            ['components.layout.header', 'components.layout.footer', 'components.layout.head', 'components.layout.chat'],
            fn (ViewContract $view): ViewContract => $view->with('company', Company::current()),
        );

        View::composer(
            'components.layout.header',
            fn (ViewContract $view): ViewContract => $view->with('navCategories', $this->navCategories()),
        );

        View::composer(
            'components.layout.screen-switcher',
            fn (ViewContract $view): ViewContract => $view->with('latestProductUrl', $this->latestProductUrl()),
        );
    }

    /**
     * URL of the newest published product, used as the "Produto" shortcut in
     * the floating screen-switcher when the visitor isn't already viewing one.
     */
    private function latestProductUrl(): ?string
    {
        if (! SiteModule::Produtos->isEnabled()) {
            return null;
        }

        $product = Product::query()->active()->latest()->first();

        return $product ? route('products.show', $product) : null;
    }

    /**
     * Product categories with at least one active product, used for the
     * "Coleção" navigation in the header.
     *
     * @return Collection<int, ProductCategory>
     */
    private function navCategories(): Collection
    {
        if (! SiteModule::Produtos->isEnabled()) {
            return collect();
        }

        return ProductCategory::query()
            ->whereIn('id', Product::query()->active()->select('product_category_id'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Keyed on the real client IP (App\Support\ClientIp) rather than the
     * spoofable forwarded IP, so a client rotating `X-Forwarded-For` cannot
     * bypass the limit.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)->by(ClientIp::resolve($request)));
    }

    /**
     * Registered here (rather than in routes/web.php) so these bindings still run when
     * `php artisan route:cache` is active — a cached route file is loaded without ever
     * executing routes/web.php again, so any Route::bind() calls placed there would
     * silently stop working in production the moment routes are cached.
     *
     * Scoped to the "published"/"active" state so draft records 404 on the public site.
     * These bindings only apply to routes using the matching {service}/{city}/{landingPage}/
     * {product}/{company}/{productCategory} parameter names — the Filament admin panel
     * resolves its own {record} bindings independently and is unaffected.
     */
    private function bindRouteParameters(): void
    {
        Route::bind('service', fn (string $value): Service => Service::query()->active()->where('slug', $value)->firstOrFail());
        Route::bind('city', fn (string $value): City => City::query()->active()->where('slug', $value)->firstOrFail());
        Route::bind('landingPage', fn (string $value): LandingPage => LandingPage::query()->published()->where('slug', $value)->firstOrFail());
        Route::bind('post', fn (string $value): Post => Post::query()->published()->where('slug', $value)->firstOrFail());
        Route::bind('state', fn (string $value): State => State::query()->active()->where('slug', $value)->firstOrFail());
        Route::bind('portfolioItem', fn (string $value): Section => Section::query()->ofType(SectionType::Portfolio)->active()->where('slug', $value)->firstOrFail());
        Route::bind('category', fn (string $value): Category => Category::query()->where('slug', $value)->firstOrFail());
        Route::bind('cluster', fn (string $value): ServiceCluster => ServiceCluster::query()->published()->where('slug', $value)->firstOrFail());
        Route::bind('tool', fn (string $value): ToolDefinition => app(ToolRegistry::class)->find($value) ?? abort(404));
        Route::bind('product', fn (string $value): Product => Product::query()->active()->where('slug', $value)->firstOrFail());
        Route::bind('company', fn (string $value): Company => Company::query()->active()->where('slug', $value)->firstOrFail());
        Route::bind('productCategory', fn (string $value): ProductCategory => ProductCategory::query()->where('slug', $value)->firstOrFail());
    }
}
