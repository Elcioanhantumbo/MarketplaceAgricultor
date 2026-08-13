<?php

namespace App\Livewire\Orders;

use App\Exceptions\OrderWorkflowException;
use App\Models\Order;
use App\Services\DeliveryWorkflowService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Order $order;

    public string $payment_method = 'mpesa';

    public string $payment_reference = '';

    public string $complaint_description = '';

    public function mount(Order $order): void
    {
        Gate::authorize('view', $order);

        $this->order = $order->load([
            'items.productListing.product',
            'buyer',
            'producer.user',
            'statusHistory.changedBy',
            'delivery.transporter',
            'payments',
            'transaction',
            'complaints',
        ]);
    }

    private function refresh(): void
    {
        $this->order->refresh();
        $this->order->load('statusHistory.changedBy', 'delivery.transporter', 'payments', 'transaction', 'complaints');
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

    public function registerPayment(PaymentService $paymentService): void
    {
        Gate::authorize('managePayment', $this->order);

        $this->validate([
            'payment_method' => 'required|in:mpesa,emola,mkesh,transferencia,dinheiro',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $this->act(fn () => $paymentService->register($this->order, $this->payment_method, $this->payment_reference ?: null));
    }

    /** RN12 — só depois da entrega é que um problema pode ser tratado como disputa. */
    public function reportComplaint(): void
    {
        Gate::authorize('reportComplaint', $this->order);

        if (! in_array($this->order->status, ['entregue', 'concluido'], true)) {
            $this->addError('complaint_description', 'Só é possível reportar um problema depois de o pedido ser entregue.');

            return;
        }

        $this->validate(['complaint_description' => 'required|string|min:10|max:2000']);

        $this->order->complaints()->create([
            'raised_by' => Auth::id(),
            'status' => 'aberta',
            'description' => $this->complaint_description,
        ]);

        $this->complaint_description = '';
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}