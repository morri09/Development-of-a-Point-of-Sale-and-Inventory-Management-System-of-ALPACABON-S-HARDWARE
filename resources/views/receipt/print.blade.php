<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt['transaction']['number'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 5mm;
            }
            .no-print {
                display: none !important;
            }
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
    </style>
</head>
<body class="bg-gray-100 print:bg-white">
    {{-- Print Button (hidden when printing) --}}
    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Print Receipt
        </button>
        <button onclick="window.close()" 
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            Close
        </button>
    </div>

    {{-- Receipt Content --}}
    <div class="min-h-screen flex items-center justify-center py-8 print:py-0 print:min-h-0">
        <div class="receipt-container bg-white p-6 max-w-sm mx-auto font-mono text-sm shadow-lg print:shadow-none print:p-0 print:max-w-full">
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
            </div>
        </div>
    </div>

    <script>
        // Auto-print on load if requested via query param
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
