<?php
namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsDrawer extends Component
{
    public $notifications = [];
    public $unreadNotifications = 0;


    #[On('new-notification')]
    public function boot()
    {
        $this->loadNotifications();
    }

    public function getListeners()
    {
        $userId = Auth::id();
        return [
            "echo-private:private-notify.{$userId},PrivateNotify" => 'handlePrivateNotifications',
        ];
    }

    public function handlePrivateNotifications($data)
    {
        // $data will contain the message sent from the event
        // $this->toast(
        //     type: 'success',
        //     title: 'You have a new notification.',
        //     description: "{$data['message']}", // Set the description to the message from the event
        //     position: 'toast-top toast-right',
        //     icon: 'o-information-circle',
        //     css: 'alert alert-success rounded-lg text-white shadow-lg p-1 flex items-center space-x-3',
        //     timeout: 5000,
        // );

        $this->notification()->send([

            'icon' => 'success',

            'title' => 'new notification!',

            'description' =>  "{$data['message']}",

        ]);
    }


    public function loadNotifications()
    {
        cache()->forget('user_notifications_' . Auth::id());
        cache()->forget('user_unread_count_' . Auth::id());

        $this->notifications = Auth::user()->notifications()
            ->latest()
            ->take(5)
            ->get();

        $this->unreadNotifications = Auth::user()
            ->unreadNotifications
            ->count();
    }


    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    #[On('echo:system-notification,systemNotification')]
    public function notifyNewNotification()
    {
        // $this->notification()->send([

        //     'icon' => 'success',

        //     'title' => 'new notification!',

        //     'description' => 'This is a system notification',

        // ]);
    }

    public function markAsRead($notificationId)
    {
        Auth::user()->notifications()->findOrFail($notificationId)->markAsRead();
        $this->loadNotifications();
    }

    public function delete($notificationId)
    {
        Auth::user()->notifications()->findOrFail($notificationId)->delete();
        $this->loadNotifications();
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        $this->loadNotifications();
    }
    public function render()
    {
        return view('livewire.notifications-drawer');
    }
}
