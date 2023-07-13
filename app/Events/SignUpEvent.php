<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 注册事件
 */
class SignUpEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;

    public array $params;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, array $params)
    {
        $this->user = $user;
        $this->params = $params;
    }
}
