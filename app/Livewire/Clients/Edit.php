<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Customer;
use Livewire\Attributes\Validate;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use WireUi\Traits\WireUiActions;

class Edit extends Component
{
    use WireUiActions;
    use WithFileUploads;

    public ?Customer $customer;
    public $user;

    public $activeTab = 'basicInfo';
    public $tabs = ['basicInfo', 'moreDetails', 'profileImage', 'addresses', 'payments'];

    #[Validate('required')]
    public $phone_number;

    #[Validate('required')]
    public $address;

    #[Validate('nullable')]
    public $secondaryAdress;

    #[Validate('required')]
    public $gender;

    #[Validate('required')]
    public $marital_status;

    #[Validate('required')]
    public $date_of_birth;

    #[Validate('required')]
    public $identification_number;

    // #[Validate('required')]
    public $occupation;

    #[Validate('required')]
    public $employer;

    #[Validate('required')]
    public $annual_income;

    // user fields
    #[Validate('required')]
    public $name;

    #[Validate('required')]
    public $email;

    #[Validate('nullable')]
    public $photo;

    public function mount(): void
    {
        $this->name = $this->customer->user->name;
        $this->email = $this->customer->user->email;
        $this->fill($this->customer);
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $user = User::where('id', $this->customer->user_id)->first();

            $this->user = $user;

            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->photo) {
                $url = $this->photo->store('users', 'public');
                $this->user->update(['avatar' => "/storage/$url"]);
            }

            $this->customer->update([
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'marital_status' => $this->marital_status,
                'identification_number' => $this->identification_number,
                'occupation' => $this->occupation,
                'employer' => $this->employer,
                'annual_income' => $this->annual_income,
            ]);

            DB::commit();

            $this->redirect('/clients', navigate: true);

            $this->notification()->send([
                'icon' => 'success',
                'title' => 'Client updated with success',
            ]);




        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error and show an error toast
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Client update failed, try again',
            ]);
        }
    }

    // tab switching
      public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function next($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if ($this->validateCurrentTab($currentTab) && isset($this->tabs[$index + 1])) {
            $this->activeTab = $this->tabs[$index + 1];
        }
    }

    public function previous($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if (isset($this->tabs[$index - 1])) {
            $this->activeTab = $this->tabs[$index - 1];
        }
    }

    private function validateCurrentTab($currentTab)
    {
        switch ($currentTab) {
            case 'basicInfo':
                $this->validate([
                    'name' => 'required|string',
                    'email' => 'required|email',
                    'phone_number' => 'required|string',
                ]);
                break;
            case 'moreDetails':
                $this->validate([
                    'gender' => 'required',
                    'marital_status' => 'required',
                    'date_of_birth' => 'required|date',
                    'identification_number' => 'required|string',
                    'annual_income' => 'required'
                ]);
                break;
            case 'profileImage':
                $this->validate([
                    'photo' => 'nullable|image',
                ]);
                break;
            case 'addresses':
                $this->validate([
                    'address' => 'required',
                ]);
                break;
            case 'payments':
                $this->validate([
                    'paymentMethod' => 'nullable',
                    'cardNumber' => 'nullable',
                ]);
                break;
            // case 'notes':
            // Add more validation rules for other tabs
        }
        return true;
    }
    public function render()
    {
        return view('livewire.clients.edit');
    }

}
