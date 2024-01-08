<?php

namespace Gazzoy\LaravelLoggingMore\Middleware;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ActionLogMiddleware
{
    public function handle($request, \Closure $next)
    {
        if ($this->isIgnorableRequst($request))
        {
            return $next($request);
        }

        $logContext = [
            'url' => $request->path(),
            'request' => $this->maskRequest($request->all()),
        ];

        if (app()->isProduction() || config('app.env') === 'development')
        {
            Log::channel('action')->debug($request->method(), $logContext);
        }

        return $next($request);
    }

    public function maskRequest($params)
    {
        $keysToBeMasked = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'new_password_confirmation',
        ];
        array_walk_recursive($params, function (&$val, $key) use ($keysToBeMasked)
        {
            if (in_array($key, $keysToBeMasked, true))
            {
                $val = '********';
            }
        });

        return $params;
    }

    public function isIgnorableRequst($request)
    {
        $requestPath = str($request->path());
        if (
            $requestPath->startsWith('horizon') ||
            $requestPath->startsWith('pulse')
        )
        {
            return true;
        }

        $requestAll = $request->all();
        if (
            Arr::has($requestAll, 'updates.0.type.syncInput')
            && Arr::get($requestAll, 'updates.0.type.syncInput') === 'syncInput'
        )
        {
            return true;
        }
    }
}
