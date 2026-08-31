<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Products\GoogleShoppingFeedBuilder;
use Illuminate\Http\Response;

class ProductFeedController extends Controller
{
    public function __construct(private readonly GoogleShoppingFeedBuilder $feedBuilder) {}

    public function show(Company $company): Response
    {
        return response()
            ->view('feeds.google-shopping', [
                'company' => $company,
                'items' => $this->feedBuilder->itemsForCompany($company),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
