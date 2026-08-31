<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Services\Landing\LandingPageViewModelFactory;
use Illuminate\Contracts\View\View;

class LandingPageController extends Controller
{
    public function __construct(private readonly LandingPageViewModelFactory $viewModelFactory) {}

    public function show(LandingPage $landingPage): View
    {
        return view('pages.landing.show', [
            'vm' => $this->viewModelFactory->make($landingPage),
        ]);
    }
}
