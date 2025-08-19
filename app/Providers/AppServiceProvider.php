<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Helpers\BreadcrumbHelper;
use App\Helpers\isProfileIncomplete as InCompleteProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

        $this->app->singleton(\App\Services\ExchangeRateService::class, function ($app) {
            return new \App\Services\ExchangeRateService();
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // view()->composer('*', function ($view) {
        //     $view->with('breadcrumbs', (new BreadcrumbHelper())->generateBreadcrumbs());
        // });

        view()->composer('*', function ($view) {
            $user = Auth::id();

            if($user){
                $currentCurrency = User::where('id', $user)->get()->pluck('currency');
                $view->with('currency', $currentCurrency[0]);
            }
        });

    }
}
