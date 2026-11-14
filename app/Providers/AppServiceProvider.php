<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Własny widok paginacji w czystym CSS (domyślny wymaga Tailwinda).
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.default');
    }
}
