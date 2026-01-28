<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSelected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->current_workspace_id) {
            return redirect()->route('workspaces.index')
                ->with('warning', __('messages.select_workspace'));
        }

        // Share workspace with all views
        $workspace = $request->user()->currentWorkspace;
        view()->share('currentWorkspace', $workspace);

        return $next($request);
    }
}
