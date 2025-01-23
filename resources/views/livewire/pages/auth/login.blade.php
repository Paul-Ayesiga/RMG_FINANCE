<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.guest')]  class extends Component
{
    public LoginForm $form;

    public bool $emailValidated = false;


    public function boot(){
        // $this->validateEmail();
    }

      // In your Livewire component
    public function validateEmail()
    {
        $this->validate([
            'form.email' => 'required|email|exists:users,email',
        ],[
            'form.email.required' => 'The email is mandatory.',
            'form.email.string' => 'The email must be a valid string.',
            'form.email.email' => 'The email must be a valid email address.',
            'form.email.exists' => 'This email address is not registered in our system.',
        ]);

        // If the email is valid, set emailValidated to true
        $this->emailValidated = true;
    }

    public function emailInValidate()
    {
         $this->emailValidated = false;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // dd('User roles: ' . auth()->user()->getRoleNames());
        // dd(Auth::guard('admin')->check());

        // if(Auth::user()->role === 'admin'){
        //     $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
        // }else{
        //     $this->redirectIntended(route('customer-dashboard', absolute: false), navigate: true);
        // }
        auth()->user()->load('roles');

        // if (auth()->user()->hasRole(['staff', 'super-admin', 'manager'])) {
        //     // $this->redirect('/dashboard',navigate: true);
        //     $this->redirectIntended('/dashboard', navigate: true);
        // } elseif (auth()->user()->hasRole('customer')) {
        //     // $this->redirect('/customer-dashboard', navigate: true);
        //     $this->redirectIntended('/customer-dashboard', navigate: true);
        // } else {
        //     // Fallback or default redirect
        //     $this->redirect('/',navigate:true);
        // }

        if (auth()->user()->hasRole(['staff', 'super-admin', 'manager'])) {
            session(['url.intended' => '/dashboard']);
            $this->redirectIntended('/dashboard', navigate: true);
        } elseif (auth()->user()->hasRole('customer')) {
            session(['url.intended' => '/customer-dashboard']);
            $this->redirectIntended('/customer-dashboard', navigate: true);
        } else {
            $this->redirect('/', navigate: true);
        }

    }
}; ?>

<div class="flex items-center justify-center min-h-screen">
    <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-5">
                <a href="/" wire:navigate>
                    <x-app-brand />
                </a>
            </div>
            <h4 x-data="{
                startingAnimation: { opacity: 0 },
                endingAnimation: { opacity: 1, stagger: 0.08, duration: 2.7, ease: 'power4.easeOut' },
                addCNDScript: true,
                splitCharactersIntoSpans(element) {
                    text = element.innerHTML;
                    modifiedHTML = [];
                    for (var i = 0; i < text.length; i++) {
                        attributes = '';
                        if(text[i].trim()){ attributes = 'class=\'inline-block\''; }
                        modifiedHTML.push('<span ' + attributes + '>' + text[i] + '</span>');
                    }
                    element.innerHTML = modifiedHTML.join('');
                },

                addScriptToHead(url) {
                    script = document.createElement('script');
                    script.src = url;
                    document.head.appendChild(script);
                },
                animateText() {
                    $el.classList.remove('invisible');
                    gsap.fromTo($el.children, this.startingAnimation, this.endingAnimation);
                }
                }"
                x-init="
                splitCharactersIntoSpans($el);
                if(addCNDScript){
                    addScriptToHead('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js');
                }
                gsapInterval3 = setInterval(function(){
                    if(typeof gsap !== 'undefined'){
                        animateText();
                        clearInterval(gsapInterval3);
                    }
                }, 5);
                "
                class="invisible bg-gradient-to-r from-blue-400 via-blue-600 to-gray-500 bg-clip-text text-transparent font-bold tracking-tight leading-tight custom-font mb-4"
                >
                Your Financial Partner
            </h4>
            <h1 class="text-2xl font-semibold text-gray-800 mb-3"> Sign In</h1>
            <p class="text-sm text-gray-500">Enter your email below to sign into your account</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-4">
            <!-- Email input -->
            <div class="relative">
                <div class="flex items-center justify-end mt-4 mb-2">
                    <x-input-label for="email" :value="__('Email')" class="w-full" />

                    @if($emailValidated)
                    <button wire:click="emailInValidate" type="button"  class="w-full text-end bg-inherit text-blue-500 "  spinner="emailInValidate" >
                        edit
                    </button>
                    @endif
                </div>

                <div class="relative">
                    <x-text-input
                        wire:model.live.debounce.150ms="form.email"
                        wire:dirty.class="border-red"
                        icon="envelope"
                        id="email"
                        :disabled="$emailValidated"
                        class="w-full h-10 px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400"
                        type="email"
                        name="email"
                        autofocus autocomplete="username"
                    />

                    <div wire:loading wire:target="form.email" class="absolute inset-y-0 right-2 flex items-center mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 0116 0" />
                        </svg>
                    </div>
                </div>

                @error('form.email')
                    <x-input-error :messages="[$message]" class="mt-2" />
                @enderror
            </div>

            <!-- Add a button to validate the email -->
            <div x-show="!$wire.emailValidated">
                <button
                    type="button"
                    wire:click="validateEmail"
                    class="w-full h-10 px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none relative flex items-center justify-center"
                    :disabled="$wire.emailValidated"
                >
                    <!-- Button text visible only when not loading -->
                    <span wire:loading.remove class="flex items-center justify-center" wire:target="validateEmail">
                        Continue to Password
                    </span>

                    <!-- Spinner when loading -->
                    <div wire:loading class="flex items-center justify-center" wire:target="validateEmail">
                        <i class="text-sm flex items-center bg-clip-text text-white">
                            verifying email <svg stroke="currentColor" fill="white" stroke-width="0"
                            viewBox="0 0 24 24" class="animate-spin ml-2" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                            d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2ZM13.6695 15.9999H10.3295L8.95053 17.8969L9.5044 19.6031C10.2897 19.8607 11.1286 20 12 20C12.8714 20 13.7103 19.8607 14.4956 19.6031L15.0485 17.8969L13.6695 15.9999ZM5.29354 10.8719L4.00222 11.8095L4 12C4 13.7297 4.54894 15.3312 5.4821 16.6397L7.39254 16.6399L8.71453 14.8199L7.68654 11.6499L5.29354 10.8719ZM18.7055 10.8719L16.3125 11.6499L15.2845 14.8199L16.6065 16.6399L18.5179 16.6397C19.4511 15.3312 20 13.7297 20 12L19.997 11.81L18.7055 10.8719ZM12 9.536L9.656 11.238L10.552 14H13.447L14.343 11.238L12 9.536ZM14.2914 4.33299L12.9995 5.27293V7.78993L15.6935 9.74693L17.9325 9.01993L18.4867 7.3168C17.467 5.90685 15.9988 4.84254 14.2914 4.33299ZM9.70757 4.33329C8.00021 4.84307 6.53216 5.90762 5.51261 7.31778L6.06653 9.01993L8.30554 9.74693L10.9995 7.78993V5.27293L9.70757 4.33329Z">
                            </path>
                        </svg>
                        </i>
                    </div>
                </button>
            </div>


            <!-- Conditional Password Field (slides in after email validation) -->
            @if($emailValidated)
                <div x-show="$wire.emailValidated"
                    x-transition:enter="transition transform duration-500 ease-in-out"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition transform duration-500 ease-in-out"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full">

                    <div class="relative">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-mary-password wire:model="form.password" id="password" class="w-full h-10 px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400" type="password" name="password" autocomplete="current-password" password-icon="o-lock-closed" password-visible-icon="o-lock-open" />
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>

                    <div class="mt-5 flex items-center justify-center">
                        <!-- Login Button -->
                        <button type="submit" wire:click="login" class="relative flex items-center justify-center w-3/4 h-10 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-300 via-blue-600 to-blue-900 rounded-lg shadow-md hover:from-gray-700 hover:via-zinc-600 hover:to-blue-800 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-300 ease-in-out">
                            <!-- Text -->
                            <span wire:loading.remove wire:target="login" class="transition-opacity duration-200">
                                Login
                            </span>
                            <!-- Loader -->
                            <svg wire:loading wire:target="login" xmlns="http://www.w3.org/2000/svg" class="absolute h-6 w-6 text-white animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>
                    </div>

                     <!-- Remember Me -->
                    <div class="block mt-4">
                        <label for="remember" class="inline-flex items-center">
                            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        @if (Route::has('password.request'))
                            <a class=" w-full underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                </div>
            @endif
        </form>

        <!-- Or Divider -->
        <div class="flex items-center my-6">
            <div class="border-t border-gray-300 w-full"></div>
            <span class="mx-4 text-sm text-gray-500">OR</span>
            <div class="border-t border-gray-300 w-full"></div>
        </div>

        <!-- Social Login Buttons -->
        <div class="space-y-4">
            {{-- <button type="button" class="w-full flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M22.675 0h-21.35C.595 0 0 .588 0 1.31v21.378C0 23.412.595 24 1.325 24H12v-9.294H9.412V11.294H12V9.163c0-2.571 1.515-4.034 3.828-4.034 1.11 0 2.27.197 2.27.197v2.492h-1.28c-1.264 0-1.66.786-1.66 1.593v1.883h2.828l-.453 2.412h-2.375V24h4.847c.729 0 1.324-.588 1.324-1.312V1.31c0-.722-.595-1.31-1.325-1.31z"/>
                </svg>
                Sign in with Facebook
            </button> --}}

            <a  href="{{route('socialite.redirect', ['driver' => 'google'])}}"  class="w-full flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:outline-none">
                <svg class="h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="800px" height="800px" viewBox="-0.5 0 48 48" version="1.1">
                    <title>Google-color</title>
                    <g id="Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Color-" transform="translate(-401.000000, -860.000000)">
                            <g id="Google" transform="translate(401.000000, 860.000000)">
                                <path d="M9.82727273,24 C9.82727273,22.4757333 10.0804318,21.0144 10.5322727,19.6437333 L2.62345455,13.6042667 C1.08206818,16.7338667 0.213636364,20.2602667 0.213636364,24 C0.213636364,27.7365333 1.081,31.2608 2.62025,34.3882667 L10.5247955,28.3370667 C10.0772273,26.9728 9.82727273,25.5168 9.82727273,24" id="Fill-1" fill="#FBBC05"> </path>
                                <path d="M23.7136364,10.1333333 C27.025,10.1333333 30.0159091,11.3066667 32.3659091,13.2266667 L39.2022727,6.4 C35.0363636,2.77333333 29.6954545,0.533333333 23.7136364,0.533333333 C14.4268636,0.533333333 6.44540909,5.84426667 2.62345455,13.6042667 L10.5322727,19.6437333 C12.3545909,14.112 17.5491591,10.1333333 23.7136364,10.1333333" id="Fill-2" fill="#EB4335"> </path>
                                <path d="M23.7136364,37.8666667 C17.5491591,37.8666667 12.3545909,33.888 10.5322727,28.3562667 L2.62345455,34.3946667 C6.44540909,42.1557333 14.4268636,47.4666667 23.7136364,47.4666667 C29.4455,47.4666667 34.9177955,45.4314667 39.0249545,41.6181333 L31.5177727,35.8144 C29.3995682,37.1488 26.7323182,37.8666667 23.7136364,37.8666667" id="Fill-3" fill="#34A853"> </path>
                                <path d="M46.1454545,24 C46.1454545,22.6133333 45.9318182,21.12 45.6113636,19.7333333 L23.7136364,19.7333333 L23.7136364,28.8 L36.3181818,28.8 C35.6879545,31.8912 33.9724545,34.2677333 31.5177727,35.8144 L39.0249545,41.6181333 C43.3393409,37.6138667 46.1454545,31.6490667 46.1454545,24" id="Fill-4" fill="#4285F4"> </path>
                            </g>
                        </g>
                    </g>
                </svg>
                Sign in with Google
            </a>
        </div>

        <!-- Sign in link -->
        <p class="mt-6 text-md text-center text-gray-500">Don't have an account?
            <a wire:navigate href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                {{ __('Sign Up') }}
            </a>
        </p>
    </div>
</div>




