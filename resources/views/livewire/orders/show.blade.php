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

    <a href="{{ $backRoute }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-medium text-green-700 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Voltar aos pedidos
    </a>

    <div class="mt-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-stone-900">Pedido #{{ $order->id }}</h1>
        <x-order-status-badge :status="$order->status" class="text-sm" />
    </div>

    @error('action')
        <x-ui.alert type="error" class="mt-3">{{ $message }}</x-ui.alert>
    @enderror

    <x-ui.card class="mt-4">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-stone-500">Produto</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $item->productListing->product->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Quantidade</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $item->quantity }} {{ $item->productListing->unit }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Comprador</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $order->buyer->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Produtor</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ $order->producer->business_name ?: $order->producer->user->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Entrega</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ match ($order->delivery_method) {
                    'comprador_levanta' => 'Comprador levanta',
                    'produtor_entrega' => 'Produtor entrega',
                    'transporte_intermediado' => 'Transporte pela plataforma',
                } }}</dd>
            </div>
            <div>
                <dt class="text-stone-500">Total</dt>
                <dd class="mt-0.5 font-medium text-stone-900">{{ number_format((float) $order->total_amount, 2) }} MZN</dd>
            </div>
        </dl>
    </x-ui.card>

    @if ($order->delivery)
        @php
            $deliveryLabels = [
                'solicitada' => 'Solicitada', 'atribuida' => 'Atribuída', 'em_recolha' => 'Em recolha',
                'em_transito' => 'Em trânsito', 'entregue' => 'Entregue', 'confirmada' => 'Confirmada',
            ];
        @endphp
        <x-ui.card class="mt-4" title="Entrega (transporte intermediado)">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Estado</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ $deliveryLabels[$order->delivery->status] }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Transportador</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ $order->delivery->transporter?->user->name ?? $order->delivery->transporter_contact ?? 'Por atribuir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Custo do transporte</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ $order->delivery->cost !== null ? number_format((float) $order->delivery->cost, 2) . ' MZN' : 'Por definir' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Recolha prevista</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ $order->delivery->pickup_at?->format('d/m/Y H:i') ?? 'Por agendar' }}</dd>
                </div>
            </dl>
        </x-ui.card>
    @endif

    @php $payment = $order->payments->first(); @endphp
    @if (! in_array($order->status, ['pendente', 'rejeitado', 'cancelado']))
        <x-ui.card class="mt-4" title="Pagamento">
            @if ($payment)
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-stone-500">Estado</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ ucfirst($payment->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Método</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ strtoupper($payment->method) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Valor</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ number_format((float) $payment->amount, 2) }} MZN</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Referência</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ $payment->provider_reference ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-stone-500">
                    Combine o pagamento directamente ({{ number_format((float) $order->total_amount, 2) }} MZN) e registe-o aqui.
                </p>
                <form wire:submit="registerPayment" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <x-ui.field name="payment_method" label="Método">
                        <x-ui.select name="payment_method" wire:model="payment_method" class="text-sm">
                            <option value="mpesa">M-Pesa</option>
                            <option value="emola">e-Mola</option>
                            <option value="mkesh">mKesh</option>
                            <option value="transferencia">Transferência bancária</option>
                            <option value="dinheiro">Dinheiro</option>
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field name="payment_reference" label="Referência" hint="Opcional.">
                        <x-ui.input name="payment_reference" wire:model="payment_reference" type="text" class="text-sm" />
                    </x-ui.field>
                    <div class="flex items-end">
                        <x-ui.button type="submit" class="w-full sm:w-auto">Registar pagamento</x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.card>
    @endif

    @if ($isProducer && $order->transaction)
        <x-ui.card class="mt-4" title="Comissão da plataforma">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-stone-500">Comissão ({{ rtrim(rtrim(number_format($order->transaction->commission_percent, 2), '0'), '.') }}%)</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ number_format((float) $order->transaction->commission_amount, 2) }} MZN</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Valor líquido a receber</dt>
                    <dd class="mt-0.5 font-medium text-stone-900">{{ number_format((float) $order->transaction->amount - (float) $order->transaction->commission_amount, 2) }} MZN</dd>
                </div>
            </dl>
        </x-ui.card>
    @endif

    <div class="mt-5 flex flex-wrap gap-2">
        @if ($isProducer && $order->status === 'pendente')
            <x-ui.button wire:click="accept" wire:confirm="Aceitar este pedido? A quantidade será reservada.">Aceitar</x-ui.button>
            <x-ui.button variant="danger" wire:click="reject" wire:confirm="Rejeitar este pedido?">Rejeitar</x-ui.button>
        @endif

        @if ($isProducer && $order->status === 'aceite')
            <x-ui.button wire:click="advance('em_preparacao')">Iniciar preparação</x-ui.button>
        @endif
        @if ($isProducer && $order->status === 'em_preparacao')
            <x-ui.button wire:click="advance('pronto')">Marcar pronto</x-ui.button>
        @endif
        @if ($isProducer && $order->status === 'pronto')
            <x-ui.button wire:click="advance('em_transporte')">Marcar em transporte</x-ui.button>
        @endif
        @if ($isProducer && $order->status === 'em_transporte')
            <x-ui.button wire:click="advance('entregue')">Marcar entregue</x-ui.button>
        @endif

        @if ($isBuyer && in_array($order->status, ['pendente', 'aceite']))
            <x-ui.button variant="danger" wire:click="cancel" wire:confirm="Cancelar este pedido?">Cancelar pedido</x-ui.button>
        @endif
        @if ($isBuyer && $order->status === 'entregue' && (! $order->delivery || $order->delivery->status === 'entregue'))
            <x-ui.button wire:click="confirmDelivery" wire:confirm="Confirmar que recebeu a encomenda?">Confirmar recepção</x-ui.button>
        @endif
    </div>

    <div class="mt-10">
        <h2 class="mb-3 text-sm font-semibold text-stone-900">Histórico</h2>
        <ul class="space-y-2 border-l-2 border-stone-100 pl-4 text-sm text-stone-600">
            @foreach ($order->statusHistory as $entry)
                <li class="relative before:absolute before:top-1.5 before:-left-[1.1875rem] before:h-2 before:w-2 before:rounded-full before:bg-stone-300">
                    <span class="text-stone-400">{{ $entry->changed_at->format('d/m/Y H:i') }}</span> —
                    {{ $entry->from_status ? ucfirst($entry->from_status) . ' → ' : '' }}{{ ucfirst($entry->to_status) }}
                    <span class="text-stone-400">({{ $entry->changedBy?->name ?? 'sistema' }})</span>
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
            <h2 class="mb-3 text-sm font-semibold text-stone-900">Disputas</h2>

            @foreach ($order->complaints as $complaint)
                <div class="mb-2 rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-stone-900">{{ $complaintLabels[$complaint->status] }}</span>
                        <span class="text-xs text-stone-400">{{ $complaint->created_at->format('d/m/Y') }}</span>
                    </div>
                    <p class="mt-1 text-stone-600">{{ $complaint->description }}</p>
                    @if ($complaint->resolution)
                        <p class="mt-2 border-t border-stone-100 pt-2 text-stone-500">Resolução: {{ $complaint->resolution }}</p>
                    @endif
                </div>
            @endforeach

            @if (($isBuyer || $isProducer) && in_array($order->status, ['entregue', 'concluido']))
                <form wire:submit="reportComplaint" class="mt-3 space-y-2">
                    <x-ui.field name="complaint_description">
                        <x-ui.textarea name="complaint_description" wire:model="complaint_description" rows="3" placeholder="Descreva o problema…" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="danger">Reportar problema</x-ui.button>
                </form>
            @endif
        </div>
    @endif

    @if ($order->status === 'concluido')
        <div class="mt-8">
            <h2 class="mb-3 text-sm font-semibold text-stone-900">Avaliações</h2>

            @forelse ($order->reviews as $review)
                <div class="mb-2 rounded-xl border border-stone-200 bg-white p-4 text-sm shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-stone-900">{{ $review->reviewer->name }}</span>
                        <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    @if ($review->comment)
                        <p class="mt-1 text-stone-600">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <x-ui.empty-state title="Ainda sem avaliações" />
            @endforelse

            @if (($isBuyer || $isProducer) && ! $order->reviews->contains('reviewer_id', auth()->id()))
                <x-ui.card class="mt-3" :title="'A sua avaliação de '.($isBuyer ? ($order->producer->business_name ?: $order->producer->user->name) : $order->buyer->name)">
                    <form wire:submit="submitReview" class="space-y-3">
                        <x-ui.select name="review_rating" wire:model="review_rating" class="text-sm">
                            <option value="5">★★★★★ Excelente</option>
                            <option value="4">★★★★☆ Muito bom</option>
                            <option value="3">★★★☆☆ Razoável</option>
                            <option value="2">★★☆☆☆ Fraco</option>
                            <option value="1">★☆☆☆☆ Mau</option>
                        </x-ui.select>
                        <x-ui.field name="review_comment">
                            <x-ui.textarea name="review_comment" wire:model="review_comment" rows="2" placeholder="Comentário (opcional)…" />
                        </x-ui.field>
                        <x-ui.button type="submit">Enviar avaliação</x-ui.button>
                    </form>
                </x-ui.card>
            @endif
        </div>
    @endif
</div>
