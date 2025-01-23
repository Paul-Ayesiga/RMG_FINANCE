<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialLogin;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function toProvider($driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle the callback from the provider.
     */
    public function handleCallback($driver)
    {
        try {
            $socialUser = Socialite::driver($driver)->user();

            // Check if Social Login already exists
            $socialAccount = SocialLogin::where('provider', $driver)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // Login the user associated with this social account
                Auth::login($socialAccount->user);

                Session::regenerate();

                return $this->redirectBasedOnRole();
            }

            // Check if a user exists with the same email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link the existing user with the social account
                SocialLogin::updateOrCreate([
                    'provider' => $driver,
                    'provider_id' => $socialUser->getId(),
                    'user_id' => $user->id,
                ]);
            } else {
                // Create a new user and assign default role
                DB::beginTransaction();

                $user = User::create([
                    'name' => $socialUser->getName() ?? 'Guest User',
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt('rand(1000,9999)'),
                ]);

                // Assign 'customer' role
                $user->assignRole('customer');

                // Create a new customer record
                $customer = new Customer();
                $customer->user_id = $user->id;
                $customer->customer_number = $this->generateCustomerNumber();
                $customer->identification_number = $socialUser->getId(); // Optional: Adjust based on your logic
                $customer->save();

                // Link the social account
                SocialLogin::create([
                    'provider' => $driver,
                    'provider_id' => $socialUser->getId(),
                    'user_id' => $user->id,
                ]);

                DB::commit();
            }

            // Authenticate the user
            Auth::login($user);

            Session::regenerate();

            return $this->redirectBasedOnRole();
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle exceptions
            return redirect()->route('login')->withErrors([
                'error' => 'Failed to login using ' . ucfirst($driver) . '. Please try again.',
            ]);
        }
    }

    /**
     * Redirect user based on role.
     */
    private function redirectBasedOnRole()
    {
        if (Auth::user()->hasRole(['staff', 'super-admin', 'manager'])) {
            session(['url.intended' => '/dashboard']);
            return redirect('/dashboard');
        } elseif (Auth::user()->hasRole('customer')) {
            session(['url.intended' => '/customer-dashboard']);
            return redirect('/customer-dashboard');
        } else {
            return redirect('/');
        }
    }

    /**
     * Generate a unique customer number.
     */
    private function generateCustomerNumber(): string
    {
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $lastNumber = $lastCustomer ? (int)str_replace(['RMG#', '-'], '', $lastCustomer->customer_number) : 0;
        $nextNumber = $lastNumber + 1;
        $year = date('Y');
        $randomString = strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));

        return 'RMG#' . $year . '-' . $nextNumber . '-' . $randomString;
    }
}
