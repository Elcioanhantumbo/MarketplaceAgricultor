<?php

namespace App\Livewire\Orders;

use App\Exceptions\OrderWorkflowException;
use App\Models\Order;
use App\Services\DeliveryWorkflowService;
use App\Services\OrderWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        Gate::authorize('view', $order);

        $this->order = $order->load([
            'items.productListing.product',
            'buyer',
            'producer.user',
            'statusHistory.changedBy',
            'delivery.transporter',
        ]);
    }

    private function refresh(): void
    {
        $this->order->refresh();
        $this->order->load('statusHistory.changedBy', 'delivery.transporter');
    }

    private function act(callable $action): void
    {
        try {
            $action();
        } catch (OrderWorkflowException $e) {
            $this->addError('action', $e->getMessage());

            return;
        }

        $this->refresh();
    }

    public function accept(OrderWorkflowService $workflow): void
    {
        Gate::authorize('manage', $this->order);
        $this->act(fn () => $workflow->accept($this->order, Auth::user()));
    }

    public function reject(OrderWorkflowService $workflow): void
    {
        Gate::authorize('manage', $this->order);
        $this->act(fn () => $workflow->reject($this->order, Auth::user()));
    }

    public function cancel(OrderWorkflowService $workflow): void
    {
        Gate::authorize('cancel', $this->order);
        $this->act(fn () => $workflow->cancel($this->order, Auth::user()));
    }

    public function advance(OrderWorkflowService $workflow, string $toStatus): void
    {
        Gate::authorize('manage', $this->order);
        $this->act(fn () => $workflow->advance($this->order, Auth::user(), $toStatus));
    }

    public function confirmDelivery(OrderWorkflowService $workflow, DeliveryWorkflowService $deliveryWorkflow): void
    {
        Gate::authorize('confirmDelivery', $this->order);

        if ($this->order->delivery) {
            $this->act(fn () => $deliveryWorkflow->confirm($this->order->delivery, Auth::user(), $workflow));
        } else {
            $this->act(fn () => $workflow->advance($this->order, Auth::user(), 'concluido'));
        }
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}