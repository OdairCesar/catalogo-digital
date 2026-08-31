<?php

namespace App\Http\Middleware;

use App\Enums\SiteModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(SiteModule::from($module)->isEnabled(), 404);

        return $next($request);
    }
}
