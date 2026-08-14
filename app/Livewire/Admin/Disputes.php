<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\RequiresAdmin;
use App\Models\Complaint;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Disputas — AgroLink MZ')]
class Disputes extends Component
{
    use RequiresAdmin;

    public ?int $resolvingId = null;

    public string $resolution_status = 'procedente';

    public string $resolution = '';

    #[Computed]
    public function complaints()
    {
        return Complaint::with(['order.buyer', 'order.producer.user', 'raisedBy'])
            ->orderByRaw("case when status in ('aberta', 'em_analise') then 0 else 1 end")
            ->latest()
            ->get();
    }

    public function startResolving(int $complaintId): void
    {
        $this->resolvingId = $complaintId;
        $this->reset('resolution_status', 'resolution');
        $this->resolution_status = 'procedente';
    }

    public function cancelResolving(): void
    {
        $this->reset('resolvingId', 'resolution_status', 'resolution');
    }

    /** RN12/RN27 — decisão administrativa sobre a disputa. */
    public function resolve(AuditLogger $audit): void
    {
        $this->validate([
            'resolution_status' => 'required|in:procedente,improcedente,resolvida',
            'resolution' => 'required|string|min:5|max:2000',
        ]);

        $complaint = Complaint::findOrFail($this->resolvingId);

        $audit->log(
            Auth::user(),
            'complaint.resolved',
            $complaint,
            ['status' => $complaint->status],
            ['status' => $this->resolution_status, 'resolution' => $this->resolution],
        );

        $complaint->update([
            'status' => $this->resolution_status,
            'resolution' => $this->resolution,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        unset($this->complaints);
        $this->cancelResolving();
    }

    public function render()
    {
        return view('livewire.admin.disputes');
    }
}