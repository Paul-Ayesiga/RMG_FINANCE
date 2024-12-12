<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class BreadcrumbHelper
{
    public function generateBreadcrumbs()
    {
        $breadcrumbs = [
            [
                'title' => 'Home',
                'url' => route('customer-dashboard') // Default home route
            ]
        ];

        // Add breadcrumbs for customer routes
        if (Route::currentRouteName() == 'customer-dashboard') {
            $breadcrumbs[] = [
                'title' => 'Customer Dashboard',
                'url' => route('customer-dashboard')
            ];
        }

        if (Route::currentRouteName() == 'my-accounts') {
            $breadcrumbs[] = [
                'title' => 'My Accounts',
                'url' => route('my-accounts')
            ];
        }

        if (Route::currentRouteName() == 'my-loans') {
            $breadcrumbs[] = [
                'title' => 'My Loans',
                'url' => route('my-loans')
            ];
        }

        // For dynamic routes like /customer/my-accounts/{account}/do-something
        if (Route::currentRouteName() == 'visit-account') {
            $accountName = request()->route('account'); // Capture dynamic parameter
            $breadcrumbs[] = [
                'title' => 'My Accounts',
                'url' => route('my-accounts')
            ];
            $breadcrumbs[] = [
                'title' => "Account: $accountName",
                'url' => '#'
            ];
        }

        if (Route::currentRouteName() == 'rmgpay') {
            $breadcrumbs[] = [
                'title' => 'RMGPAY',
                'url' => route('rmgpay')
            ];
        }

        return $breadcrumbs;
    }

}
