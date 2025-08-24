<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(file_exists(storage_path() . "/installed"))
        {
            \App::setLocale(getActiveLanguage());
        }

        // $input = $request->all();

        // array_walk_recursive($input, function(&$input) {
        //     $input = strip_tags($input);
        // });

        // $request->merge($input);

        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars_decode($value);
                $value = preg_replace('/<\s*script\b[^>]*>(.*?)<\s*\/\s*script\s*>/is', '', $value);
                $value = str_replace(['&lt;', '&gt;', 'javascript','alert'], '', $value);
            }
        });
        $request->merge($input);

        return $next($request);
    }
}
