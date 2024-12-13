<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\isProfileIncomplete as InCompleteProfile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind('isProfileIncomplete', function () {
        //     return new InCompleteProfile();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // view()->composer('*', function ($view) {
        //     $view->with('breadcrumbs', (new BreadcrumbHelper())->generateBreadcrumbs());
        // });

    }
}
