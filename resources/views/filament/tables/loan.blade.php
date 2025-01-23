<div class="flex flex-col max-w-full p-2 space-y-4 dark:bg-gray-800 dark:text-gray-100"
>
    <!-- Header Section -->
    <div class="flex items-center justify-between space-x-2">
        <div class="flex items-center space-x-3">
        {{-- <div class="flex-shrink-0">
            @if($getRecord()->user?->profile_photo_url)
                <img class="h-10 w-10 rounded-full object-cover" 
                     src="{{ $getRecord()->user->profile_photo_url }}" 
                     alt="{{ $getRecord()->user?->name }}" />
            @else
                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-lg font-medium text-gray-600 dark:text-gray-300">
                        {{ substr($getRecord()->user?->name ?? '', 0, 1) }}
                    </span>
                </div>
            @endif
        </div> --}}
        <div class="text-lg font-semibold text-gray-700 dark:text-gray-200">
            {{ $getRecord()->user?->name }}
        </div>
    </div>
        <span class="text-sm text-gray-500 dark:text-gray-300">
            #{{ $getRecord()->user?->country }}
        </span>
    </div>

    <!-- Loan Amount and Payment Section -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs text-gray-400 dark:text-gray-200">{{ __('Loan Amount') }}</span>
            <div class="text-2xl font-semibold text-orange-500 dark:text-orange-400">{{ \Number::currency($getRecord()->amount, 'OMR') }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-200">{{__('Purpose')}}: {{ $getRecord()->purpose }}</div>
            @php
                $status = $getRecord()->status;
                $colors = $status->getColorClasses();
            @endphp
            
            <div class="inline-flex items-center">
                <div @class([
                    'text-xs px-2 py-1 rounded-full font-medium',
                    $colors['text'],
                    $colors['bg'],
                    $colors['border']
                ])>
                    {{ $status->getLabel() }}
                </div>
            </div>
        </div>
        <div class="text-right space-y-1 mt-6">
            <span class="text-xs text-gray-400 dark:text-gray-200">
                {{__('Amount Paid')}}</span>
            <div class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                {{ \Number::currency($getRecord()->amount_paid,'OMR') }}
            </div>
            <div class="text-xs {{ $getRecord()->outstanding_balance > 0 ? 'text-red-500 dark:text-red-400' : 'text-green-500 dark:text-green-400' }}">
                {{__('Balance')}}: {{ \Number::currency($getRecord()->outstanding_balance, 'OMR') }}
            </div>
        </div>
    </div>

    <!-- Payments Section -->
    <div class="space-y-2">
        <div class="flex justify-between items-center space-x-1">
            <a class="flex space-x-1 text-xs text-gray-500 dark:text-gray-300" onclick="togglePayments({{ $getRecord()->id }})">
                <span>{{ $getRecord()->payments()->count() }}</span>
                <span>{{__('Payments')}}</span>
            </a>
            <div id="bar-{{ $getRecord()->id }}" class="border-l-2 border-gray-300 h-12 hidden dark:border-gray-700"></div>
            <div id="payments-{{ $getRecord()->id }}" class="hidden mx-1">
                @foreach ($getRecord()->payments as $payment)
                <div class="flex justify-between space-x-2 text-xs text-gray-500 dark:text-gray-300">
                    <div>{{ $payment->month }}</div>
                    <div>{{__('Amount')}}: {{ \Number::currency($payment->amount, 'OMR') }}</div>
                    <div>{{ $payment->status }}</div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="relative w-full h-2 bg-gray-200 rounded dark:bg-gray-700">
            <div class="absolute top-0 left-0 h-2 bg-purple-500 rounded dark:bg-purple-400" 
                 style="width: {{ $getRecord()->completion_percentage }}%;"></div>
        </div>
        <div class="text-sm text-amber-500 mb-4 dark:text-amber-400">
           {{__(' Monthly')}}: {{ \Number::currency($getRecord()->monthly_installment, 'OMR') }} | {{__('Duration')}}: {{ $getRecord()->duration }} {{__('months')}}
        </div>
    </div>

    <!-- Due Date Section -->
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400 mr-1" fill="teal" viewBox="0 0 20 20">
                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 11h2V7H8v4zm0 2h2v-2H8v2z" />
            </svg>
            <span class="text-sm text-gray-500 dark:text-gray-300">
                {{__('Next Due')}}: {{ $getRecord()->next_payment_date?->format('Y-m-d') }}</span>
        </div>
        <div class="text-gray-600 dark:text-gray-300">
            {{__('Due Date')}}: {{ $getRecord()->due_date->format('Y-m-d') }}
        </div>
    </div>

    <!-- Edit Button -->
    <div class="flex justify-between items-end">
        <div class="flex-shrink-0">
            @if($getRecord()->user?->profile_photo_url)
                <img class="h-10 w-10 rounded-full object-cover" 
                     src="{{ $getRecord()->user->profile_photo_url }}" 
                     alt="{{ $getRecord()->user?->name }}" />
            @else
                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-lg font-medium text-gray-600 dark:text-gray-300">
                        {{ substr($getRecord()->user?->name ?? '', 0, 1) }}
                    </span>
                </div>
            @endif
        </div>
        <a href="{{ route('filament.admin.finances.resources.loans.edit', $getRecord()->id) }}" 
           class="flex items-center text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 dark:focus:ring-purple-800 font-medium rounded-lg text-sm px-2 py-1 text-center me-0 mb-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            <span class="ms-2">{{__('Edit')}}</span>
        </a>
     
    </div>
</div>

<script>
    function togglePayments(recordId) {
        var element = document.getElementById('payments-' + recordId);
        var bar = document.getElementById('bar-' + recordId);
        if (element.classList.contains('hidden')) {
            element.classList.remove('hidden');
            bar.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
            bar.classList.add('hidden');
        }
    }
</script>
