<div>
@php
    $isProducer = auth()->user()->id === $order->producer->user_id;
    $isBuyer = auth()->user()->id === $order->buyer_id;
    $isAdmin = in_array(auth()->user()->role, ['admin', 'operator']);
    $item = $order->items->first();
    $backRoute = $isAdmin && ! $isProducer && ! $isBuyer
        ? route('admin.pedidos')
        : ($isProducer ? route('pedidos-recebidos') : route('meus-pedidos'));
@endphp

    <a href="{{ $backRoute }}" wire:navigate class="text-sm text-green-700 hover:underline">&larr; Voltar aos pedidos</a>

    <div class="mt-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">Pedido #{{ $order->id }}</h1>
        <x-order-status-badge :status="$order->status" class="text-sm" />
    </div>

    @error('action') <p class="mt-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror

    <div class="mt-4 rounded border border-stone-200 p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-stone-500">Produto</dt>
                <dd class="font-medium">{{ $item->productListing->product->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Quantidade</dt>
                <dd class="font-medium">{{ $item->quantity }} {{ $item->productListing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Comprador</dt>
                <dd class="font-medium">{{ $order->buyer->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Produtor</dt>
                <dd class="font-medium">{{ $order->producer->business_name ?: $order->producer->user->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Entrega</dt>
                <dd class="font-medium">{{ match ($order->delivery_method) {
                    'comprador_levanta' => 'Comprador levanta',
                    'produtor_entrega' => 'Produtor entrega',
                    'transporte_intermediado' => 'Transporte pela plataforma',
                } }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Total</dt>
                <dd class="font-medium">{{ number_format((float) $order->total_amount, 2) }} MZN</dd>
            </div>
        </dl>
    </div>

    @if ($order->delivery)
        @php
            $deliveryLabels = [
                'solicitada' => 'Solicitada', 'atribuida' => 'Atribuída', 'em_recolha' => 'Em recolha',
                'em_transito' => 'Em trânsito', 'entregue' => 'Entregue', 'confirmada' => 'Confirmada',
            ];
        @endphp
        <div class="mt-4 rounded border border-stone-200 p-6">
            <h2 class="text-sm font-medium text-stone-500">Entrega (transporte intermediado)</h2>
            <dl class="mt-2 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Estado</dt>
                    <dd class="font-medium">{{ $deliveryLabels[$order->delivery->status] }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Transportador</dt>
                    <dd class="font-medium">{{ $order->delivery->transporter?->user->name ?? $order->delivery->transporter_contact ?? 'Por atribuir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Custo do transporte</dt>
                    <dd class="font-medium">{{ $order->delivery->cost !== null ? number_format((float) $order->delivery->cost, 2) . ' MZN' : 'Por definir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Recolha prevista</dt>
                    <dd class="font-medium">{{ $order->delivery->pickup_at?->format('d/m/Y H:i') ?? 'Por agendar' }}</dd>
                </div>
            </dl>
        </div>
    @endif

    @php $payment = $order->payments->first(); @endphp
    @if (! in_array($order->status, ['pendente', 'rejeitado', 'cancelado']))
        <div class="mt-4 rounded border border-stone-200 p-6">
            <h2 class="text-sm font-medium text-stone-500">Pagamento</h2>

            @if ($payment)
                <dl class="mt-2 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-stone-500">Estado</dt>
                        <dd class="font-medium">{{ ucfirst($payment->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Método</dt>
                        <dd class="font-medium">{{ strtoupper($payment->method) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Valor</dt>
                        <dd class="font-medium">{{ number_format((float) $payment->amount, 2) }} MZN</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Referência</dt>
                        <dd class="font-medium">{{ $payment->provider_reference ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-1 text-sm text-stone-500">
                    Combine o pagamento directamente ({{ number_format((float) $order->total_amount, 2) }} MZN) e registe-o aqui.
                </p>
                <form wire:submit="registerPayment" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-stone-500">Método</label>
                        <select wire:model="payment_method" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                            <option value="mpesa">M-Pesa</option>
                            <option value="emola">e-Mola</option>
                            <option value="mkesh">mKesh</option>
                            <option value="transferencia">Transferência bancária</option>
                            <option value="dinheiro">Dinheiro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500">Referência (opcional)</label>
                        <input wire:model="payment_reference" type="text" class="mt-1 w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                        @error('payment_reference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Registar pagamento</button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    @if ($isProducer && $order->transaction)
        <div class="mt-4 rounded border border-stone-200 p-6">
            <h2 class="text-sm font-medium text-stone-500">Comissão da plataforma</h2>
            <dl class="mt-2 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Comissão ({{ rtrim(rtrim(number_format($order->transaction->commission_percent, 2), '0'), '.') }}%)</dt>
                    <dd class="font-medium">{{ number_format((float) $order->transaction->commission_amount, 2) }} MZN</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Valor líquido a receber</dt>
                    <dd class="font-medium">{{ number_format((float) $order->transaction->amount - (float) $order->transaction->commission_amount, 2) }} MZN</dd>
                </div>
            </dl>
        </div>
    @endif

    <div class="mt-4 flex flex-wrap gap-2">
        @if ($isProducer && $order->status === 'pendente')
            <button wire:click="accept" wire:confirm="Aceitar este pedido? A quantidade será reservada." class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Aceitar</button>
            <button wire:click="reject" wire:confirm="Rejeitar este pedido?" class="rounded border border-stone-300 px-4 py-2 text-sm hover:border-red-500 hover:text-red-600">Rejeitar</button>
        @endif

        @if ($isProducer && $order->status === 'aceite')
            <button wire:click="advance('em_preparacao')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Iniciar preparação</button>
        @endif
        @if ($isProducer && $order->status === 'em_preparacao')
            <button wire:click="advance('pronto')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar pronto</button>
        @endif
        @if ($isProducer && $order->status === 'pronto')
            <button wire:click="advance('em_transporte')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar em transporte</button>
        @endif
        @if ($isProducer && $order->status === 'em_transporte')
            <button wire:click="advance('entregue')" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Marcar entregue</button>
        @endif

        @if ($isBuyer && in_array($order->status, ['pendente', 'aceite']))
            <button wire:click="cancel" wire:confirm="Cancelar este pedido?" class="rounded border border-stone-300 px-4 py-2 text-sm hover:border-red-500 hover:text-red-600">Cancelar pedido</button>
        @endif
        @if ($isBuyer && $order->status === 'entregue' && (! $order->delivery || $order->delivery->status === 'entregue'))
            <button wire:click="confirmDelivery" wire:confirm="Confirmar que recebeu a encomenda?" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Confirmar recepção</button>
        @endif
    </div>

    <div class="mt-8">
        <h2 class="mb-2 text-sm font-medium text-stone-500">Histórico</h2>
        <ul class="space-y-1 text-sm text-stone-600">
            @foreach ($order->statusHistory as $entry)
                <li>
                    {{ $entry->changed_at->format('d/m/Y H:i') }} —
                    {{ $entry->from_status ? ucfirst($entry->from_status) . ' → ' : '' }}{{ ucfirst($entry->to_status) }}
                    ({{ $entry->changedBy?->name ?? 'sistema' }})
                </li>
            @endforeach
        </ul>
    </div>

    @if ($order->complaints->isNotEmpty() || (($isBuyer || $isProducer) && in_array($order->status, ['entregue', 'concluido'])))
        @php
            $complaintLabels = [
                'aberta' => 'Aberta', 'em_analise' => 'Em análise', 'procedente' => 'Procedente',
                'improcedente' => 'Improcedente', 'resolvida' => 'Resolvida',
            ];
        @endphp
        <div class="mt-8">
            <h2 class="mb-2 text-sm font-medium text-stone-500">Disputas</h2>

            @foreach ($order->complaints as $complaint)
                <div class="mb-2 rounded border border-stone-200 p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ $complaintLabels[$complaint->status] }}</span>
                        <span class="text-xs text-stone-400">{{ $complaint->created_at->format('d/m/Y') }}</span>
                    </div>
                    <p class="mt-1 text-stone-600">{{ $complaint->description }}</p>
                    @if ($complaint->resolution)
                        <p class="mt-2 border-t border-stone-100 pt-2 text-stone-500">Resolução: {{ $complaint->resolution }}</p>
                    @endif
                </div>
            @endforeach

            @if (($isBuyer || $isProducer) && in_array($order->status, ['entregue', 'concluido']))
                <form wire:submit="reportComplaint" class="mt-2 space-y-2">
                    <textarea wire:model="complaint_description" rows="3" placeholder="Descreva o problema…" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600"></textarea>
                    @error('complaint_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded border border-stone-300 px-4 py-2 text-sm hover:border-red-500 hover:text-red-600">Reportar problema</button>
                </form>
            @endif
        </div>
    @endif

    @if ($order->status === 'concluido')
        <div class="mt-8">
            <h2 class="mb-2 text-sm font-medium text-stone-500">Avaliações</h2>

            @forelse ($order->reviews as $review)
                <div class="mb-2 rounded border border-stone-200 p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ $review->reviewer->name }}</span>
                        <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    @if ($review->comment)
                        <p class="mt-1 text-stone-600">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-stone-500">Ainda sem avaliações.</p>
            @endforelse

            @if (($isBuyer || $isProducer) && ! $order->reviews->contains('reviewer_id', auth()->id()))
                <form wire:submit="submitReview" class="mt-3 space-y-2 rounded border border-stone-200 p-3">
                    <label class="block text-xs font-medium text-stone-500">
                        A sua avaliação de {{ $isBuyer ? ($order->producer->business_name ?: $order->producer->user->name) : $order->buyer->name }}
                    </label>
                    <select wire:model="review_rating" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600">
                        <option value="5">★★★★★ Excelente</option>
                        <option value="4">★★★★☆ Muito bom</option>
                        <option value="3">★★★☆☆ Razoável</option>
                        <option value="2">★★☆☆☆ Fraco</option>
                        <option value="1">★☆☆☆☆ Mau</option>
                    </select>
                    <textarea wire:model="review_comment" rows="2" placeholder="Comentário (opcional)…" class="w-full rounded border-stone-300 text-sm focus:border-green-600 focus:ring-green-600"></textarea>
                    @error('review_comment') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Enviar avaliação</button>
                </form>
            @endif
        </div>
    @endif
</div>
