<?php

namespace App\Livewire\Deliveries;

use App\Models\Delivery;
use App\Services\DeliveryWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Manage extends Component
{
    public ?int $assigningId = null;

    public ?int $transporter_id = null;

    public string $transporter_contact = '';

    public string $cost = '';

    public string $pickup_at = '';

    public function mount(): void
    {
        Gate::authorize('manage', Delivery::class);
    }

    #[Computed]
    public function deliveries()
    {
        return Delivery::with(['order.buyer', 'order.items.productListing.product', 'transporter.user'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function transporters()
    {
        return \App\Models\Transporter::with('user')->get();
    }

    public function startAssigning(int $deliveryId): void
    {
        $this->assigningId = $deliveryId;
        $this->reset(['transporter_id', 'transporter_contact', 'cost', 'pickup_at']);
    }

    public function cancelAssigning(): void
    {
        $this->reset(['assigningId', 'transporter_id', 'transporter_contact', 'cost', 'pickup_at']);
    }

    public function assign(DeliveryWorkflowService $workflow): void
    {
        $this->validate([
            'cost' => 'required|numeric|min:0',
            'pickup_at' => 'nullable|date',
            'transporter_contact' => 'nullable|string|max:255',
            'transporter_id' => 'nullable|exists:transporters,id',
        ]);

        $delivery = Delivery::findOrFail($this->assigningId);

        $workflow->assign(
            $delivery,
            $this->transporter_id,
            $this->transporter_contact ?: null,
            (float) $this->cost,
            $this->pickup_at ? \Illuminate\Support\Carbon::parse($this->pickup_at) : null,
        );

        unset($this->deliveries);
        $this->cancelAssigning();
    }

    public function advance(DeliveryWorkflowService $workflow, int $deliveryId, string $toStatus): void
    {
        $workflow->advance(Delivery::findOrFail($deliveryId), $toStatus);
        unset($this->deliveries);
    }

    public function render()
    {
        return view('livewire.deliveries.manage');
    }
}