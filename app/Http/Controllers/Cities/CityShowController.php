<?php

namespace App\Http\Controllers\Cities;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\Landing\ServiceCityViewModelFactory;
use Illuminate\Contracts\View\View;

class CityShowController extends Controller
{
    public function __construct(private readonly ServiceCityViewModelFactory $viewModelFactory) {}

    public function show(City $city): View
    {
        return view('pages.cities.show', [
            'vm' => $this->viewModelFactory->makeForCity($city),
        ]);
    }
}
