<?php

namespace App\Providers;

use App\Repositories\Pdf\PdfRepository;
use App\Repositories\Pdf\PdfRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PdfRepositoryInterface::class, PdfRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
