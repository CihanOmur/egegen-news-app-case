<?php

namespace App\Http\Middleware;

use App\Models\Logs;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Keep the request logging simple and efficient
        Logs::create([
            'ip'            => $request->ip(),                  // İsteği gönderen IP adresi
            'method'        => $request->method(),              // HTTP metodu (GET, POST, vs.)
            'path'          => $request->path(),                // İstek yapılan URL path'i
            'request_body'  => json_encode(
                $request->all(),
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT     // Türkçe karakterleri koru, okunabilir biçimde sakla
            ),
        ]);

        // next middleware or controller
        return $next($request);
    }
}
