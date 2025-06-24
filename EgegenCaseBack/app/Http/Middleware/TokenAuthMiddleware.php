<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $authToken = $request->bearerToken();

        $failureCacheKey = "auth_failures:{$clientIp}";
        $blacklistCacheKey = "ip_blacklist:{$clientIp}";
        $validToken = '2BH52wAHrAymR7wP3CASt';
        $maxFailures = 10;
        $blacklistDuration = now()->addMinutes(10);
        // Eğer IP kara listede ise erişimi engelle
        if (Cache::has($blacklistCacheKey)) {
            return response()->json([
                'message' => 'Bu IP adresi 10 dakika boyunca engellenmiştir.'
            ], 403);
        }

        // Token doğrulaması
        if ($authToken !== $validToken) {
            if (!Cache::has($failureCacheKey)) {
                Cache::put($failureCacheKey, 1, now()->addMinutes(10));
                $failureCount = 1;
            } else {
                $failureCount = Cache::increment($failureCacheKey);
            }

            // Eğer başarısız giriş sayısı sınırı aşarsa IP'yi kara listeye al
            if ($failureCount >= $maxFailures) {
                Cache::put($blacklistCacheKey, true, $blacklistDuration);
                Cache::forget($failureCacheKey);
            } else {
                // Bir sonraki middleware veya controller'a devam et
                return $next($request);
            }
        }

        // Başarılı giriş: başarısız denemeleri sıfırla
        Cache::forget($failureCacheKey);

        // Bir sonraki middleware veya controller'a devam et
        return $next($request);
    }
}
