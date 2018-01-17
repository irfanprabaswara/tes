<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // '405325770:AAG49XI9pWQSpi5OsC0hz_muUFj0QmFjndM/webhook'
        '/webhook'
    ];
}
