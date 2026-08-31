<?php

namespace App\Http\Controllers;

use App\Enums\SectionType;
use App\Models\Company;
use App\Models\Section;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'company' => Company::current(),
            'about' => Section::block(SectionType::About),
        ]);
    }
}
