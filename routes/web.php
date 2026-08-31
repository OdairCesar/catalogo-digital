<?php

use App\Http\Controllers\Blog\BlogIndexController;
use App\Http\Controllers\Blog\BlogShowController;
use App\Http\Controllers\Cities\CityIndexController;
use App\Http\Controllers\Cities\CityShowController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqIndexController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Portfolio\PortfolioIndexController;
use App\Http\Controllers\Portfolio\PortfolioShowController;
use App\Http\Controllers\Products\ProductFeedController;
use App\Http\Controllers\Products\ProductIndexController;
use App\Http\Controllers\Products\ProductShowController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\Services\ServiceClusterShowController;
use App\Http\Controllers\Services\ServiceIndexController;
use App\Http\Controllers\Services\ServiceShowController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\States\StateIndexController;
use App\Http\Controllers\States\StateShowController;
use App\Http\Controllers\Tools\ToolIndexController;
use App\Http\Controllers\Tools\ToolShowController;
use App\Http\Controllers\Tools\ToolSubmissionPdfController;
use App\Models\City;
use App\Models\Service;
use App\Models\ServiceCluster;
use Illuminate\Support\Facades\Route;

// Route model bindings (service/city/landingPage/post/state/portfolioItem/category/cluster/tool/
// product/company/productCategory) are registered in App\Providers\AppServiceProvider::boot()
// instead of here — see that method's docblock for why they can't live in this file.

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sobre', [PageController::class, 'about'])->middleware('section:about')->name('about');

Route::get('/contato', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contato', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::middleware('module:ferramentas')->group(function (): void {
    Route::get('/ferramentas', [ToolIndexController::class, 'index'])->name('tools.index');

    // Formato antigo — mantido só para redirecionar permanentemente para a URL
    // atual (agora dentro de /ferramentas) e não perder indexação de links antigos.
    Route::get('/consultor-ia', fn () => redirect()->route('tools.show', 'consultor-ia', 301));

    Route::get('/ferramentas/{tool}', [ToolShowController::class, 'show'])->name('tools.show');
    Route::get('/ferramentas/{tool}/resultado/{submission}/pdf', ToolSubmissionPdfController::class)
        ->middleware('signed')
        ->name('tools.submission.pdf');
});

Route::middleware('module:servicos')->group(function (): void {
    Route::get('/servicos', [ServiceIndexController::class, 'index'])->name('services.index');
    Route::get('/servicos/{service}', [ServiceShowController::class, 'show'])->name('services.show');
    Route::get('/servicos/{service}/{slug}', [ServiceClusterShowController::class, 'show'])->name('services.clusters.show');

    // Formato antigo (cluster e cidade em segmentos separados) — mantido só para redirecionar
    // permanentemente para a URL atual ({cluster}-em-{cidade} em um único segmento) e não
    // perder indexação de links que já apontem para o formato anterior.
    Route::get('/servicos/{service}/{cluster}/{city}', fn (Service $service, ServiceCluster $cluster, City $city) => redirect()->route(
        'services.clusters.show',
        [$service, "{$cluster->slug}-em-{$city->slug}"],
        301,
    ));

    // Cidades, estados e landing pages de SEO programático existem só para
    // apresentar os serviços oferecidos em cada localidade — não fazem
    // sentido sem o módulo de Serviços ativo.
    Route::get('/cidades', [CityIndexController::class, 'index'])->name('cities.index');
    Route::get('/cidades/{city}', [CityShowController::class, 'show'])->name('cities.show');

    Route::get('/estados', [StateIndexController::class, 'index'])->name('states.index');
    Route::get('/estados/{state}', [StateShowController::class, 'show'])->name('states.show');
});

Route::middleware('module:blog')->group(function (): void {
    Route::get('/blog', [BlogIndexController::class, 'index'])->name('blog.index');
    Route::get('/blog/categoria/{category}', [BlogIndexController::class, 'index'])->name('blog.category');
    Route::get('/blog/{post}', [BlogShowController::class, 'show'])->name('blog.show');
});

Route::get('/perguntas-frequentes', [FaqIndexController::class, 'index'])->middleware('section:faq_group')->name('faq.index');

Route::middleware('section:portfolio')->group(function (): void {
    Route::get('/portfolio', [PortfolioIndexController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/servico/{service}', [PortfolioIndexController::class, 'index'])->name('portfolio.service');
    Route::get('/portfolio/{portfolioItem}', [PortfolioShowController::class, 'show'])->name('portfolio.show');
});

Route::middleware('module:produtos')->group(function (): void {
    Route::get('/produtos', [ProductIndexController::class, 'index'])->name('products.index');
    Route::get('/produtos/categoria/{productCategory}', [ProductIndexController::class, 'index'])->name('products.category');
    Route::get('/produtos/feed/{company}.xml', [ProductFeedController::class, 'show'])
        ->where('company', '[^.]+')
        ->name('products.feed');
    Route::get('/produtos/{product}', [ProductShowController::class, 'show'])->name('products.show');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// Rota curinga de SEO programático ({service}-em-{city}) — deve ficar por último.
Route::middleware('module:servicos')->group(function (): void {
    Route::get('/{landingPage}', [LandingPageController::class, 'show'])->name('landing.show');
});
