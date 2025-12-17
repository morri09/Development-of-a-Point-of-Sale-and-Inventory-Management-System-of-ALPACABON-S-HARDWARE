@props(['receipt'])

<div class="receipt-container bg-white p-6 max-w-sm mx-auto font-mono text-sm print:p-0 print:max-w-full">
    {{-- Store Header --}}
    <div class="text-center border-b border-dashed border-gray-400 pb-4 mb-4">
        <h1 class="text-lg font-bold uppercase">{{ $receipt['store']['name'] }}</h1>
        @if($receipt['store']['address'])
            <p class="text-xs text-gray-600">{{ $receipt['store']['address'] }}</p>
        @endif
        @if($receipt['store']['contact'])
            <p class="text-xs text-gray-600">Tel: {{ $receipt['store']['contact'] }}</p>
        @endif
        @if($receipt['store']['email'])
            <p class="text-xs text-gray-600">{{ $receipt['store']['email'] }}</p>
        @endif
    </div>

    {{-- Transaction Info --}}
    <div class="border-b border-dashed border-gray-400 pb-4 mb-4">
        <div class="flex justify-between text-xs">
            <span>Receipt #:</span>
            <span class="font-semibold">{{ $receipt['transaction']['number'] }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span>Date:</span>
            <span>{{ $receipt['transaction']['date'] }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span>Time:</span>
            <span>{{ $receipt['transaction']['time'] }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span>Cashier:</span>
            <span>{{ $receipt['transaction']['cashier'] }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span>Payment:</span>
            <span class="uppercase">{{ $receipt['transaction']['payment_method'] }}</span>
        </div>
    </div>

    {{-- Items --}}
    <div class="border-b border-dashed border-gray-400 pb-4 mb-4">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="text-left py-1">Item</th>
                    <th class="text-center py-1">Qty</th>
                    <th class="text-right py-1">Price</th>
                    <th class="text-right py-1">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt['items'] as $item)
                    <tr>
                        <td class="py-1 pr-2 max-w-[120px] truncate">{{ $item['name'] }}</td>
                        <td class="text-center py-1">{{ $item['quantity'] }}</td>
                        <td class="text-right py-1">{{ $item['unit_price'] }}</td>
                        <td class="text-right py-1">{{ $item['subtotal'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="border-b border-dashed border-gray-400 pb-4 mb-4">
        <div class="flex justify-between text-xs">
            <span>Subtotal:</span>
            <span>₱{{ $receipt['totals']['subtotal'] }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span>Tax:</span>
            <span>₱{{ $receipt['totals']['tax'] }}</span>
        </div>
        @if((float) str_replace(',', '', $receipt['totals']['discount']) > 0)
            <div class="flex justify-between text-xs text-green-600">
                <span>Discount:</span>
                <span>-₱{{ $receipt['totals']['discount'] }}</span>
            </div>
        @endif
        <div class="flex justify-between text-sm font-bold mt-2 pt-2 border-t border-gray-300">
            <span>TOTAL:</span>
            <span>₱{{ $receipt['totals']['total'] }}</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-xs text-gray-600">
        <p>{{ $receipt['footer'] }}</p>
        <p class="mt-2 text-[10px]">{{ now()->format('M d, Y h:i A') }}</p>
    </div>
</div>

{{-- Print Styles --}}
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .receipt-container, .receipt-container * {
            visibility: visible;
        }
        .receipt-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
            padding: 5mm;
            font-size: 10px;
        }
    }
</style>
