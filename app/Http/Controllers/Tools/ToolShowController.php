<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Tools\ToolDefinition;
use Illuminate\Contracts\View\View;

class ToolShowController extends Controller
{
    public function show(ToolDefinition $tool): View
    {
        return view()->make($tool->landingView ?? 'pages.tools.show', ['tool' => $tool]);
    }
}
