<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\Profile;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Verificações — AgroLink MZ')]
class Verifications extends Component
{
    use RequiresAdmin;

    /** RN16 — perfis com dados mínimos preenchidos, ainda por verificar. */
    #[Computed]
    public function pending()
    {
        return Profile::with('user')
            ->whereNull('verified_at')
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->latest()
            ->get();
    }

    public function verify(int $profileId, AuditLogger $audit): void
    {
        $profile = Profile::findOrFail($profileId);

        $audit->log(Auth::user(), 'profile.verified', $profile, ['verified_at' => null], ['verified_at' => now()->toDateTimeString()]);
        $profile->update(['verified_at' => now()]);

        unset($this->pending);
    }

    public function render()
    {
        return view('livewire.admin.verifications');
    }
}