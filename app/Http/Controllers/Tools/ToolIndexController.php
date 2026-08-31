<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Seo\BreadcrumbBuilder;
use App\Services\Tools\ToolDefinition;
use App\Services\Tools\ToolRegistry;
use Illuminate\Contracts\View\View;

class ToolIndexController extends Controller
{
    public function index(ToolRegistry $registry): View
    {
        $tools = collect($registry->all())
            ->map(fn (ToolDefinition $tool): array => [
                'title' => $tool->title,
                'tagline' => $tool->tagline,
                'icon' => $tool->icon,
                'url' => route('tools.show', $tool->slug),
            ])
            ->values();

        $breadcrumbs = BreadcrumbBuilder::start()->add('Ferramentas')->build();

        return view('pages.tools.index', [
            'tools' => $tools,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
