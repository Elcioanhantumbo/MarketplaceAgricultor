<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination, RequiresAdmin;

    #[Url]
    public string $role = '';

    #[Url]
    public string $search = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function toggleBlocked(int $userId, AuditLogger $audit): void
    {
        $user = User::findOrFail($userId);
        abort_if($user->id === Auth::id(), 400, 'Não pode bloquear a sua própria conta.');

        $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
        $audit->log(Auth::user(), $newStatus === 'blocked' ? 'user.blocked' : 'user.unblocked', $user, ['status' => $user->status], ['status' => $newStatus]);
        $user->update(['status' => $newStatus]);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('phone', 'ilike', "%{$this->search}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users', ['users' => $users]);
    }
}