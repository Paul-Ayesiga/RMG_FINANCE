<?php
namespace App\Events;

use App\Models\User; // Ensure you import the User model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateNotify implements ShouldBroadcast
{
use Dispatchable, InteractsWithSockets, SerializesModels;

public $user; // This will hold the user data to broadcast to
public $message; // This will hold the message to be broadcasted

/**
* Create a new event instance.
*
* @param User $user
*/
public function __construct(User $user,$message)
{
    $this->user = $user; // Pass the user to be broadcasted
    $this->message = $message;
}

/**
* Get the channels the event should broadcast on.
*
* @return array<int, \Illuminate\Broadcasting\Channel>
    */

    public function broadcastOn()
    {
        return new PrivateChannel('private-notify.' . $this->user->id);
    }

    /**
    * Broadcast the event's data.
    *
    * @return array
    */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
