<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Landing\ServiceClusterViewModelFactory;
use App\ViewModels\LandingPageViewModel;
use Illuminate\Contracts\View\View;

class ServiceClusterShowController extends Controller
{
    public function __construct(private readonly ServiceClusterViewModelFactory $viewModelFactory) {}

    public function show(Service $service, string $slug): View
    {
        $vm = $this->viewModelFactory->makeForSlug($service, $slug);

        return $vm instanceof LandingPageViewModel
            ? view('pages.landing.show', ['vm' => $vm])
            : view('pages.services.show', ['vm' => $vm]);
    }
}
