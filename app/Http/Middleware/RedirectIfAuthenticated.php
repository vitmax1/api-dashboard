<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated as Middleware;
use Illuminate\Http\Request;

class RedirectIfAuthenticated extends Middleware
{
    /**
     * The URIs that should be reachable while authenticated.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
