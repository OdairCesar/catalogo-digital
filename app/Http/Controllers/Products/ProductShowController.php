<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Services\Products\ProductViewModelFactory;
use Illuminate\Contracts\View\View;

class ProductShowController extends Controller
{
    public function __construct(private readonly ProductViewModelFactory $viewModelFactory) {}

    public function show(Product $product): View
    {
        return view('pages.products.show', [
            'vm' => $this->viewModelFactory->makeShow($product),
            'company' => Company::current(),
        ]);
    }
}
