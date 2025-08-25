<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VNPayCallbackLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Log incoming VNPay callback
        if ($request->is('vnpay/return') || stripos($request->path(), 'vnpay') !== false) {
            Log::info('VNPay Callback Received', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
                'all_data' => $request->all(),
                'query_params' => $request->query(),
                'timestamp' => now()->toISOString(),
                'referrer' => $request->header('referer')
            ]);
        }

        $response = $next($request);

        // Log response for VNPay callbacks
        if ($request->is('vnpay/return') || stripos($request->path(), 'vnpay') !== false) {
            Log::info('VNPay Callback Response', [
                'status_code' => $response->getStatusCode(),
                'response_type' => get_class($response),
                'redirect_to' => $response instanceof \Illuminate\Http\RedirectResponse ? $response->getTargetUrl() : null,
                'timestamp' => now()->toISOString()
            ]);
        }

        return $response;
    }
}
