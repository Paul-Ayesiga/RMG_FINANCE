<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Events\systemNotification;
use Livewire\Attributes\On;
use Mary\Traits\Toast;

class SendNotification extends Component
{
    use WithPagination;
    use Toast;

    public $type = 'info';
    public $title = '';
    public $message = '';
    public $notifyAll = false;
    public $selectedUsers = [];
    public $roleFilter = '';
    public $search = '';
    public $roles;

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function updatedNotifyAll($value)
    {
        if ($value) {
            $this->selectedUsers = [];
        }
    }

    public function rules()
    {
        return [
            'type' => 'required|in:info,success,warning,error',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'selectedUsers' => 'required_unless:notifyAll,true|array',
        ];
    }

    public function sendNotification()
    {
        $this->validate();

        $users = $this->notifyAll
            ? User::all()
            : User::whereIn('id', $this->selectedUsers)->get();

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\SystemNotification(
                $this->type,
                $this->title,
                $this->message
            ));
        }

        // Reset form
        $this->reset(['title', 'message', 'selectedUsers', 'notifyAll']);

        // Show success message
        session()->flash('success', 'Notification sent successfully!');
        systemNotification::dispatch();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->role($this->roleFilter);
            })
            ->get();
    }

    #[On('echo:system-notification,systemNotification')]
    // public function notifyNewOrder()
    // {
    //     $this->toast(
    //         type: 'success',
    //         title: 'It is done!',
    //         description: null,
    //         position: 'toast-top-right',
    //         icon: 'o-information-circle',      // Optional icon, similar to Toastr's icon
    //         css: 'alert-info',                  // DaisyUI classes
    //         timeout: 5000,                      // Timeout in ms (same as Toastr's timeOut)
    //     );
    // }

    public function render()
    {
        return view('livewire.admin.send-notification', [
            'users' => $this->users
        ]);
    }
}
