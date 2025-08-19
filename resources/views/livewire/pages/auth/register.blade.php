<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Livewire\Volt\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use Mary\Traits\Toast;


new #[Layout('layouts.guest')] #[Lazy()] class extends Component
{
    use Toast;
    #[Computed]
    public bool $accepted = false;

    public $currentStep = 1;
    public $totalSteps = 3;
    public $isLoading = false;

    public bool $termsAndAgreements = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $userRole = 'customer';
    public string $avatar = '';
    public string $password_confirmation = '';
    public string $identification_number = '';

    protected $messages = [
        'name.required' => 'The name is mandatory.',
        'name.string' => 'The name must be a valid string.',
        'name.max' => 'The name may not be greater than 255 characters.',
        'email.required' => 'The email is mandatory.',
        'email.string' => 'The email must be a valid string.',
        'email.email' => 'The email must be a valid email address.',
        'email.exists' => 'This email address is not registered in our system.',
        'password.required' => 'The password is mandatory.',
        'password.string' => 'The password must be a valid string.',
        'password.confirmed' => 'The password confirmation does not match.',
        'password.min' => 'The password must be at least 8 characters long.',
        'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'accepted.required' => 'You must accept the terms and agreements.',
        'identification_number.required' => 'The identification number is mandatory.',
    ];


    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'], // Use table name directly for `unique`
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:1024'],
            'identification_number' => ['required', 'unique:customers,identification_number'], // Use table name directly
            'userRole' => ['required'],
            'accepted' => ['required'], // Validate the acceptance of terms
        ];
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable','image','max:1024'],
            'identification_number' => ['required','unique:'.Customer::class],
            'userRole' => ['required'],
            'accepted' => ['required'] // Validate the acceptance of terms
        ]);

        try {

            DB::beginTransaction();
                $validated['password'] = Hash::make($validated['password']);

                event(new Registered($user = User::create($validated)));

                $customer = new Customer();

                $customer->user_id = $user->id;
                $customer->customer_number = $this->generateCustomerNumber();
                $customer->identification_number = $this->identification_number;
                $customer->save();

                $user->assignRole('customer');

            DB::commit();

            // Auth::login($user);

                $status = 'Account created successfully, log in with your credentials';
                $this->redirectRoute('login', navigate: true);
                Session::flash('status', __($status));

        } catch (\Exception $e) {

            DB::rollBack();

            // dd($e->message);

            $this->toast(
                type: 'error',
                title: 'Failed to register, please try again',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );

            // dd('failed');
        }

    }

    private function generateCustomerNumber(): string
    {
        // Get the last customer number, if it exists
        $lastCustomer = Customer::orderBy('id', 'desc')->first();

        // Extract the number part from the last customer number
        $lastNumber = $lastCustomer ? (int)str_replace(['RMG#', '-'], '', $lastCustomer->customer_number) : 0;

        // Generate a new unique number by incrementing the last number
        $nextNumber = $lastNumber + 1;

        // Get the current year for added uniqueness
        $year = date('Y');

        // Generate a random string (e.g., 3 characters) for added complexity
        $randomString = strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));

        // Create the new customer number
        return 'RMG#' . $year . '-' . $nextNumber . '-' . $randomString;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Move to the next step
    public function nextStep()
    {
        $this->validateStep();
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

     // Validate the fields for the current step
    public function validateStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|lowercase|max:255|unique:users,email',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ]);
        } elseif ($this->currentStep === 3) {
            $this->validate([
                'identification_number' => 'required|unique:customers,identification_number',
                'accepted' => 'required',
            ]);
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
            <h4 wire:ignore.self x-data="{
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
            <h1 class="text-2xl font-semibold text-gray-800 mb-3"> Sign Up</h1>
        </div>

        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center space-x-2">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div>
                        <div
                            class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-semibold
                                {{ $currentStep >= $i ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-500' }}"
                        >
                            {{ $i }}
                        </div>
                    </div>
                @endfor
            </div>
            <span class="text-sm text-gray-500">Step {{ $currentStep }} of {{ $totalSteps }}</span>
        </div>

        <!-- Step 1 -->
        @if ($currentStep === 1)
            <div class="space-y-4 relative">
                <h3 class="text-lg font-semibold text-gray-700">Personal Details</h3>
                <!-- Overlay Loader -->
                <div wire:loading wire:target="nextStep" class="absolute bg-transparent bg-opacity-50 flex justify-center items-center z-10" style="top: 50%; left: 50%; transform: translate(-50%, -50%);" >
                    <div class="flex justify-center item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                            <path fill="#aca2ee" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity="0.25"/>
                            <path fill="#aca2ee" d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                                <animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Normal Step Content -->
                <div>
                    <div>
                        <label for="name" class="block text-sm font-medium">Name</label>
                        <x-text-input wire:model.defer="name" id="name" type="text" class="w-full h-10 px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400" />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <x-input-label for="email" :value="__('Email')" class="w-full mt-3" />
                    <div class="relative">
                        <x-text-input
                            wire:model.live.debounce.150ms="email"
                            wire:dirty.class="border-red"
                            icon="envelope"
                            id="email"
                            class="w-full h-10 px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400"
                            type="email"
                            name="email"
                            autofocus autocomplete="username"
                        />

                        <!-- Inline loader for live email validation -->
                        <div wire:loading wire:target="email" class="absolute inset-y-0 right-2 flex items-center mt-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 0116 0" />
                            </svg>
                        </div>
                    </div>
                    @error('email')
                        <x-input-error :messages="[$message]" class="mt-2" />
                    @enderror
                </div>
            </div>
        @endif

        <!-- Step 2 -->
        @if ($currentStep === 2)
            <div class="space-y-4 relative">
                <h3 class="text-lg font-semibold text-gray-700">Security Setup</h3>
                 <!-- Overlay Loader -->
                <div wire:loading wire:target="nextStep" class="absolute bg-transparent bg-opacity-50 flex justify-center items-center z-10" style="top: 50%; left: 50%; transform: translate(-50%, -50%);" >
                    <div class="flex justify-center item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                            <path fill="#aca2ee" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity="0.25"/>
                            <path fill="#aca2ee" d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                                <animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
                            </path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="mt-4">
                            <x-input-label for="password" :value="__('Password')" />
                            <x-wireui-password wire:model="password"  icon="key"   id="password" class="block mt-1 w-full py-2 px-2" type="password" name="password" autocomplete="new-password" errorless/>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-wireui-password wire:model="password_confirmation"  icon="key"   id="password_confirmation" class="block mt-1 w-full py-2 px-2" type="password" name="password_confirmation" autocomplete="new-password" errorless/>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3 -->
        @if ($currentStep === 3)
            <div class="space-y-4 relative">
                <h3 class="text-lg font-semibold text-gray-700">Verification stage</h3>
                 <!-- Overlay Loader -->
                <div wire:loading wire:target="nextStep" class="absolute bg-transparent bg-opacity-50 flex justify-center items-center z-10" style="top: 50%; left: 50%; transform: translate(-50%, -50%);" >
                    <div class="flex justify-center item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                            <path fill="#aca2ee" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity="0.25"/>
                            <path fill="#aca2ee" d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z">
                                <animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
                            </path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="mt-4">
                            <x-input-label for="identification" :value="__('Identification Number')" />
                            <x-wireui-input wire:model="identification_number" icon="hand-raised"  id="identification_number" class="block mt-1 w-full py-2 px-2" type="text" name="identification_number" autocomplete="" errorless/>
                            <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model.live="accepted" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-sm text-gray-600">I agree to the terms and conditions</span>
                        </label>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            @if ($currentStep > 1)
                <button type="button" wire:click="previousStep" class="px-4 py-2 text-sm font-medium text-white bg-zinc-600 rounded-md flex items-center justify-center">
                    <span class="mr-2">Previous</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21"><g fill="none" fill-rule="evenodd" stroke="#aca2ee" stroke-linecap="round" stroke-linejoin="round"><path d="M17.5 14.5v-2a3 3 0 0 0-3-3h-8m0 3l-3.001-3l3.001-3"/><path d="m9.5 12.5l-3.001-3l3.001-3"/></g></svg>
                </button>
            @endif

            @if ($currentStep < 3)
                <button type="button" wire:click="nextStep" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md flex items-center justify-center">
                    <span class="mr-2">Next</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="#aca2ee" stroke-linecap="round" stroke-width="1.5"><path stroke-linejoin="round" d="m14.5 7l5 5l-5 5"/><path d="M19.5 12h-10c-1.667 0-5 1-5 5" opacity="0.5"/></g></svg>
                </button>
            @else
                <button type="submit"  wire:click="register" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md" wire:attr.disabled="!$accepted">
                     <!-- Button text visible only when not loading -->
                    <span wire:loading.remove class="flex items-center justify-center" wire:target="register">
                        Create account
                    </span>

                    <!-- Spinner when loading -->
                    <div wire:loading class="flex items-center justify-center" wire:target="register">
                        <h1 class="text-lg font-bold flex items-center bg-gradient-to-r from-blue-400 via-blue-600 to-gray-500 bg-clip-text text-transparent">
                            RMG<svg stroke="currentColor" fill="white" stroke-width="0"
                            viewBox="0 0 24 24" class="animate-spin" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <path
                            d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2ZM13.6695 15.9999H10.3295L8.95053 17.8969L9.5044 19.6031C10.2897 19.8607 11.1286 20 12 20C12.8714 20 13.7103 19.8607 14.4956 19.6031L15.0485 17.8969L13.6695 15.9999ZM5.29354 10.8719L4.00222 11.8095L4 12C4 13.7297 4.54894 15.3312 5.4821 16.6397L7.39254 16.6399L8.71453 14.8199L7.68654 11.6499L5.29354 10.8719ZM18.7055 10.8719L16.3125 11.6499L15.2845 14.8199L16.6065 16.6399L18.5179 16.6397C19.4511 15.3312 20 13.7297 20 12L19.997 11.81L18.7055 10.8719ZM12 9.536L9.656 11.238L10.552 14H13.447L14.343 11.238L12 9.536ZM14.2914 4.33299L12.9995 5.27293V7.78993L15.6935 9.74693L17.9325 9.01993L18.4867 7.3168C17.467 5.90685 15.9988 4.84254 14.2914 4.33299ZM9.70757 4.33329C8.00021 4.84307 6.53216 5.90762 5.51261 7.31778L6.06653 9.01993L8.30554 9.74693L10.9995 7.78993V5.27293L9.70757 4.33329Z">
                            </path>
                        </svg>Finance</h1>
                    </div>
                </button>
            @endif
        </div>

        <p class="text-sm text-gray-500 flex items-center justify-center mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="mr-2">
                <g fill="none" stroke="#aca2ee" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                    <path stroke-dasharray="20" stroke-dashoffset="20" d="M5 21v-1c0 -2.21 1.79 -4 4 -4h4c2.21 0 4 1.79 4 4v1"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="20;0"/></path>
                    <path stroke-dasharray="20" stroke-dashoffset="20" d="M11 13c-1.66 0 -3 -1.34 -3 -3c0 -1.66 1.34 -3 3 -3c1.66 0 3 1.34 3 3c0 1.66 -1.34 3 -3 3Z"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="20;0"/></path>
                    <path stroke-dasharray="6" stroke-dashoffset="6" d="M20 3v4"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="6;0"/><animate attributeName="stroke-width" begin="1s" dur="3s" keyTimes="0;0.1;0.2;0.3;1" repeatCount="indefinite" values="2;3;3;2;2"/></path>
                    <path stroke-dasharray="2" stroke-dashoffset="2" d="M20 11v0.01"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" values="2;0"/><animate attributeName="stroke-width" begin="1.3s" dur="3s" keyTimes="0;0.1;0.2;0.3;1" repeatCount="indefinite" values="2;3;3;2;2"/></path>
                </g>
            </svg>
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>
        </p>

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

        <div class="mt-5">
            <p class="mt-2 text-sm text-center text-gray-500">By continuing, you agree to our <a class="underline text-blue-600" @click="$wire.termsAndAgreements = true">Terms</a> and <a class="underline text-blue-600" @click="$wire.termsAndAgreements = true">Privacy Policy</a>.</p>
             <x-mary-modal wire:model="termsAndAgreements" title="Terms and Conditions" subtitle="Please review the terms and conditions" separator>
                <div class="text-sm text-gray-700">
                    <p>Welcome to RMG Finance Microfinance. The following terms and conditions ("Terms") govern the provision of financial services, including loans, savings, and other related services ("Services") provided by RMG Finance ("we," "us," "our") to our customers ("you," "your"). By accessing or using our Services, you agree to comply with these Terms.</p>

                    <h3 class="font-bold mt-4">1. Eligibility</h3>
                    <p>- To be eligible for our Services, you must be at least 18 years old and meet our specific eligibility criteria.</p>
                    <p>- Proper identification and documentation may be required to access certain Services.</p>

                    <h3 class="font-bold mt-4">2. Loan Products</h3>
                    <p>- We offer various loan products such as personal loans, business loans, and emergency loans, each subject to approval based on your financial status and credit history.</p>
                    <p>- The loan amount, interest rates, and repayment terms will be determined on a case-by-case basis.</p>

                    <h3 class="font-bold mt-4">3. Savings Accounts</h3>
                    <p>- We offer savings accounts to help you save money and earn interest. The minimum balance, interest rates, and account features may vary based on the type of savings account.</p>
                    <p>- Withdrawals and deposits must comply with our procedures and any applicable limits.</p>

                    <h3 class="font-bold mt-4">4. Interest Rates and Fees</h3>
                    <p>- Interest rates for loans are set according to our current policies and will be communicated to you at the time of loan approval.</p>
                    <p>- Additional fees, such as processing fees, late payment fees, or early repayment penalties, may apply depending on the Services you choose.</p>

                    <h3 class="font-bold mt-4">5. Repayment Terms</h3>
                    <p>- Loan repayments must be made according to the agreed-upon schedule. Failure to make timely repayments may result in penalties, higher interest rates, or other corrective measures.</p>
                    <p>- We reserve the right to initiate collection procedures if you default on your loan repayments.</p>

                    <h3 class="font-bold mt-4">6. Default and Recovery</h3>
                    <p>- If you fail to make repayments as agreed, we may classify your account as "in default." In such cases, we may pursue legal action or engage third-party collection agencies.</p>
                    <p>- We may also report defaults to credit bureaus, which may negatively affect your credit rating.</p>

                    <h3 class="font-bold mt-4">7. Customer Obligations</h3>
                    <p>- You are responsible for providing accurate and complete information during the application process and for notifying us of any changes to your personal or financial circumstances.</p>
                    <p>- You must use the Services in compliance with applicable laws and regulations.</p>

                    <h3 class="font-bold mt-4">8. Confidentiality and Data Protection</h3>
                    <p>- We will handle your personal and financial information in accordance with our Privacy Policy.</p>
                    <p>- We may use your data to perform credit checks, manage your account, and comply with legal obligations.</p>

                    <h3 class="font-bold mt-4">9. Amendments to Terms</h3>
                    <p>- We reserve the right to amend these Terms at any time. We will notify you of any significant changes through our website, email, or other appropriate means.</p>
                    <p>- Continued use of our Services following any amendments signifies your acceptance of the revised Terms.</p>

                    <h3 class="font-bold mt-4">10. Termination of Services</h3>
                    <p>- We may terminate or suspend your access to our Services at any time if you violate these Terms or any other agreements with us.</p>
                    <p>- You may also terminate your account by providing written notice to us.</p>

                    <h3 class="font-bold mt-4">11. Dispute Resolution</h3>
                    <p>- Any disputes arising from these Terms shall be resolved amicably. If a resolution cannot be reached, the dispute shall be settled in accordance with the laws of Bank of Uganda.</p>
                    <p>- Both parties agree to use mediation or arbitration before resorting to legal action.</p>

                    <h3 class="font-bold mt-4">12. Liability Limitation</h3>
                    <p>- We shall not be liable for any losses or damages resulting from your use of the Services, except in cases of gross negligence or intentional misconduct on our part.</p>
                    <p>- You agree to indemnify us for any losses, damages, or expenses resulting from your breach of these Terms.</p>

                    <h3 class="font-bold mt-4">13. Governing Law</h3>
                    <p>- These Terms are governed by and construed in accordance with the laws of Bank of Uganda.</p>
                </div>

                <x-slot:actions>
                    <div class="space-x-4">
                    <x-wireui-button label="Cancel" @click="$wire.termsAndAgreements = false"  class="bg-gray-600" />
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" wire:model.live="accepted" @click="$wire.termsAndAgreements = false" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">I Agree </span>
                    </label>
                    </div>
                </x-slot:actions>
            </x-mary-modal>
        </div>
    </div>
</div>





