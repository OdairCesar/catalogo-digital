<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Landing\ServiceCityViewModelFactory;
use Illuminate\Contracts\View\View;

class ServiceShowController extends Controller
{
    public function __construct(private readonly ServiceCityViewModelFactory $viewModelFactory) {}

    public function show(Service $service): View
    {
        return view('pages.services.show', [
            'vm' => $this->viewModelFactory->makeForService($service),
        ]);
    }
}
