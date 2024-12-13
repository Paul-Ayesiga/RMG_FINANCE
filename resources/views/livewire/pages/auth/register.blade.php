<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Livewire\Volt\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use Mary\Traits\Toast;

new #[Layout('layouts.guest')] class extends Component
{
    use Toast;
    #[Computed]
    public bool $accepted = false;

    public bool $termsAndAgreements = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $userRole = 'customer';
    public string $avatar = '';
    public string $password_confirmation = '';
    public string $identification_number = '';

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

}; ?>

<div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
        <div
            class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-sky-500 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl">
        </div>
        <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">

            <div class="max-w-md mx-auto">
                <div class="mb-4">
                    <a href="/" wire:navigate>
                        <x-app-brand />
                    </a>
                </div>
                <div class="divide-y divide-gray-200">
                    <form wire:submit="register">
                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-wireui-input wire:model="name"  icon="user"   id="name" class="block mt-1 w-full py-2 px-2" type="text" name="name" autofocus autocomplete="name" errorless/>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-wireui-input wire:model="email"  icon="envelope"   id="email" class="block mt-1 w-full py-2 px-2" type="email" name="email" autocomplete="username" errorless/>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
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

                        <div class="mt-4">
                            <x-input-label for="identification" :value="__('Identification Number')" />
                            <x-wireui-input wire:model="identification_number" icon="hand-raised"  id="identification_number" class="block mt-1 w-full py-2 px-2" type="text" name="identification_number" autocomplete="" errorless/>
                            <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
                        </div>

                        <!-- Terms and Conditions Checkbox -->
                        <div class="mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model.live="accepted" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-600">I agree to the terms and conditions</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                                {{ __('Already registered?') }}
                            </a>

                            <x-mary-button class="ms-4 bg-blue-400 text-white"
                                        type="submit"
                                        icon="o-paper-airplane"
                                        spinner="register"
                                        :disabled="!$accepted">
                                {{ __('Register') }}
                            </x-mary-button>
                        </div>
                    </form>
                </div>
                <div class="mt-5">
                    <a class="ms-2 text-sm text-blue-600 underline cursor-pointer" @click="$wire.termsAndAgreements = true">Terms and conditions</a>
                    <x-mary-modal wire:model="termsAndAgreements" title="Terms and Conditions" subtitle="Please review the terms and conditions" separator>
                        <div class="text-sm text-gray-700">
                            <p>Welcome to [Your Microfinance Institution Name]. The following terms and conditions ("Terms") govern the provision of financial services, including loans, savings, and other related services ("Services") provided by [Your Microfinance Institution Name] ("we," "us," "our") to our customers ("you," "your"). By accessing or using our Services, you agree to comply with these Terms.</p>

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
                            <p>- Any disputes arising from these Terms shall be resolved amicably. If a resolution cannot be reached, the dispute shall be settled in accordance with the laws of [Your Jurisdiction].</p>
                            <p>- Both parties agree to use mediation or arbitration before resorting to legal action.</p>

                            <h3 class="font-bold mt-4">12. Liability Limitation</h3>
                            <p>- We shall not be liable for any losses or damages resulting from your use of the Services, except in cases of gross negligence or intentional misconduct on our part.</p>
                            <p>- You agree to indemnify us for any losses, damages, or expenses resulting from your breach of these Terms.</p>

                            <h3 class="font-bold mt-4">13. Governing Law</h3>
                            <p>- These Terms are governed by and construed in accordance with the laws of [Your Jurisdiction].</p>
                        </div>

                        <x-slot:actions>
                            <x-mary-button label="Cancel" @click="$wire.termsAndAgreements = false" />
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model.live="accepted" @click="$wire.termsAndAgreements = false" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-600">I Agree </span>
                            </label>
                        </x-slot:actions>
                    </x-mary-modal>

                </div>
            </div>
        </div>
    </div>
</div>


