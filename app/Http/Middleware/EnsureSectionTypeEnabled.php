<?php

namespace App\Http\Middleware;

use App\Enums\SectionType;
use App\Models\SectionTypeSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSectionTypeEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        abort_unless(SectionTypeSetting::isEnabled(SectionType::from($type)), 404);

        return $next($request);
    }
}
