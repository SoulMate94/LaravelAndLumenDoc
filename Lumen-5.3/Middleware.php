<?php

// Create Middleware
namespace App\Http\Middleware;

use Closure;

class OldMiddleware
{
    /**
     * 运行请求过滤器
     * $param \Illuminate\Http\Request $request
     * $param \Closure $next
     * $return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input('age') <= 200) {
            return redirect('home');
        }
        return $next($request);
    }
}

# BeforeMiddleware
class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // Perform action

        return $next($request);
    }
}

class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Perform action
        return $response;
    }
}