{{-- filepath: /Users/amisha/www/loanTracker/resources/views/filament/infolists/loan-details.blade.php --}}
<div x-data="{ selectedPayment: null,
formatAmount(amount) {
        return amount ? Number(amount).toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }) : '$ 0.00';
    },
    formatDate(dateString) {
        return dateString ? new Date(dateString).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }) : 'N/A';
    }
 }
" class="space-y-6">
    <div class="flex justify-between items-center space-x-1">
       <div class="text-xl font-semibold">Payment Schedule for {{ $getRecord()->user->name }}</div>
       {{-- <pre x-text="JSON.stringify(selectedPayment, null, 2)"></pre> --}}

    </div>
    <div class="grid grid-cols-12 gap-6">
        {{-- Timeline Section (Left) --}}
        <div class="col-span-7">
            <div class="relative">
                @php
                    $schedule = $getRecord()->payment_schedule ?? [];
                    $today = now();
                @endphp

                {{-- Timeline Line --}}
                <div class="absolute left-9 top-0 h-full w-0.5 bg-gray-200"></div>

                {{-- Timeline Items --}}
                @foreach ($schedule as $index => $payment)
                    @php
                        $dueDate = \Carbon\Carbon::parse($payment['due_date']);
                        $status = \App\Enums\PaymentStatus::from($payment['status']);
                        
                        // Define colors based on status
                        $colors = match($status) {
                            \App\Enums\PaymentStatus::COMPLETED => [
                                'bg' => 'bg-green-100',
                                'border' => 'border-green-500',
                                'text' => 'text-green-700',
                                'icon' => 'bg-green-500',
                            ],
                            \App\Enums\PaymentStatus::PENDING => $dueDate->isPast() ? [
                                'bg' => 'bg-red-100',
                                'border' => 'border-red-500',
                                'text' => 'text-red-700',
                                'icon' => 'bg-red-500',
                            ] : [
                                'bg' => 'bg-yellow-100',
                                'border' => 'border-yellow-500',
                                'text' => 'text-yellow-700',
                                'icon' => 'bg-yellow-500',
                            ],
                            default => [
                                'bg' => 'bg-gray-100',
                                'border' => 'border-gray-500',
                                'text' => 'text-gray-700',
                                'icon' => 'bg-gray-500',
                            ]
                        };
                    @endphp

                    <div class="relative mb-8 flex items-center">
                        {{-- Timeline Dot --}}
                        <div class="absolute left-9 -ml-1.5 h-3 w-3 rounded-full {{ $colors['icon'] }}"></div>

                        {{-- Payment Card --}}
                        <div class="ml-16 w-full">
                            <div 
                                @click="selectedPayment = JSON.parse($event.target.closest('[data-payment]').dataset.payment);
                                "
                                data-payment="{{ json_encode($payment) }}"
                                class="cursor-pointer rounded-lg border {{ $colors['border'] }} {{ $colors['bg'] }} p-4 transition-all hover:shadow-md"
                                :class="{
                                    'ring-2': selectedPayment && selectedPayment.month === '{{ $payment['month'] }}',
                                    '{{ str_replace('border-', 'ring-', $colors['border']) }}': selectedPayment && selectedPayment.month === '{{ $payment['month'] }}'
                                }"
                            >
                                <div class="flex justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold {{ $colors['text'] }}">
                                            {{ $payment['month'] }}
                                        </h3>
                                        <p class="text-sm {{ $colors['text'] }}">
                                            Due: {{ $dueDate->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold {{ $colors['text'] }}">
                                            $ {{ number_format($payment['amount'], 2) }}
                                        </p>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colors['text'] }}">
                                            {{ $status->value }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Details Section (Right) --}}
        <div class="col-span-5">
            <div 
                x-show="selectedPayment" 
                x-transition
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
            >
                <template x-if="selectedPayment">
                    <div class="space-y-4">
                        <h2 class="text-xl font-bold">
                            <span x-text="selectedPayment.month || 'Unknown'"></span>
                            <span> Payment Details</span>
                        </h2>
                        
                        <div class="grid gap-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-sm text-gray-600">Amount Due</p>
                                    <p class="text-lg font-bold" x-text="formatAmount(selectedPayment.amount)"></p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-sm text-gray-600">Amount Paid</p>
                                    <p class="text-lg font-bold" x-text="formatAmount(selectedPayment.amount)"></p>
                                </div>
                            </div>
    
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-sm text-gray-600">Due Date</p>
                                    <p class="font-medium" x-text="formatDate(selectedPayment.due_date)"></p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-sm text-gray-600">Status</p>
                                    <p class="font-medium" x-text="selectedPayment.status || 'Unknown'"></p>
                                </div>
                            </div>
    
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-sm text-gray-600">Notes</p>
                                <p class="font-medium" x-text="selectedPayment.notes || 'No notes available'"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>