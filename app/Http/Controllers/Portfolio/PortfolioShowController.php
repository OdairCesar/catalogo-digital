<?php

namespace App\Http\Controllers\Portfolio;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\Portfolio\PortfolioItemViewModelFactory;
use Illuminate\Contracts\View\View;

class PortfolioShowController extends Controller
{
    public function __construct(private readonly PortfolioItemViewModelFactory $viewModelFactory) {}

    public function show(Section $portfolioItem): View
    {
        return view('pages.portfolio.show', [
            'vm' => $this->viewModelFactory->makeShow($portfolioItem),
        ]);
    }
}
