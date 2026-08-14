<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Notificações — AgroLink MZ')]
class Inbox extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.notifications.inbox', [
            'notifications' => Auth::user()->notificationLogs()->latest()->paginate(15),
        ]);
    }
}