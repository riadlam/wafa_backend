<?php

return [
    /*
     * Laravel Framework Service Providers...
     */
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    /*
     * Package Service Providers...
     */
    Laravel\Sanctum\SanctumServiceProvider::class,

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BroadcastServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
];
