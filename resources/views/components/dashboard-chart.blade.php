@props(['chartData'])

<div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Sales Overview</h3>
        <span class="text-sm text-gray-500">Last 7 days</span>
    </div>
    
    @php
        $maxSales = max($chartData['sales']) ?: 1;
        $labels = $chartData['labels'];
        $sales = $chartData['sales'];
    @endphp

    <div class="chart-container">
        <div class="base"></div>
        
        <!-- Left Side Meter -->
        <ul class="meter">
            @for ($i = 5; $i >= 1; $i--)
                <li>
                    <div>₱{{ number_format(($maxSales / 5) * $i, 0) }}</div>
                </li>
            @endfor
        </ul>
        
        <!-- Background Grid -->
        <table>
            @for ($row = 0; $row < 5; $row++)
                <tr>
                    @for ($col = 0; $col < 7; $col++)
                        <td></td>
                    @endfor
                </tr>
            @endfor
        </table>
        
        <!-- Bars -->
        @foreach ($sales as $index => $sale)
            @php
                $height = $maxSales > 0 ? ($sale / $maxSales) * 100 : 0;
                $left = ($index * 14.28);
            @endphp
            <div class="bar" style="left: {{ $left }}%; height: {{ max($height, 2) }}%;">
                <span class="bar-label">{{ $labels[$index] }}</span>
                <span class="bar-value">₱{{ number_format($sale, 0) }}</span>
            </div>
        @endforeach
    </div>
</div>

<style>
.chart-container {
    position: relative;
    z-index: 0;
    margin: 20px auto 0;
    width: 100%;
    height: 350px;
    background-color: rgba(37, 99, 235, 0.03);
    border-radius: 8px;
}

.chart-container:after {
    content: "";
    position: absolute;
    top: -12px;
    left: -6px;
    height: 12px;
    width: 100%;
    background-color: rgba(37, 99, 235, 0.05);
    transform: skew(45deg);
    border-radius: 4px 4px 0 0;
}

.chart-container > table {
    position: absolute;
    top: 0;
    left: 0;
    z-index: -999;
    width: 100%;
    height: 100%;
}

.chart-container table tr td {
    width: 14.28%;
    height: 20%;
    border: solid 1px rgba(37, 99, 235, 0.08);
}

.base {
    position: absolute;
    bottom: 0;
    left: -25px;
    width: calc(100% + 50px);
    height: 16px;
    background-color: rgba(37, 99, 235, 0.08);
    transform: skew(45deg);
    border-radius: 0 0 4px 4px;
}

.meter {
    position: absolute;
    top: 0;
    left: -24px;
    height: 100%;
    width: 24px;
    background-color: rgba(37, 99, 235, 0.15);
    border-left: solid 1px rgba(37, 99, 235, 0.3);
    list-style: none;
    margin: 0;
    padding: 0;
}

.meter:before {
    content: "";
    position: absolute;
    top: -6px;
    left: -12px;
    width: 12px;
    height: 100%;
    background-color: rgba(30, 64, 175, 0.25);
    transform: skewY(45deg);
}

.meter:after {
    content: "";
    position: absolute;
    top: -12px;
    left: -6px;
    width: 24px;
    height: 12px;
    background-color: rgba(59, 130, 246, 0.35);
    transform: skewX(45deg);
}

.meter li {
    position: relative;
    list-style-type: none;
    height: 20%;
    border-bottom: solid 1px rgba(37, 99, 235, 0.3);
    box-sizing: border-box;
}

.meter li:before {
    content: "";
    position: absolute;
    top: -12px;
    left: -24px;
    width: 24px;
    height: 100%;
    border-bottom: solid 1px rgba(37, 99, 235, 0.3);
    transform: skewY(45deg);
}

.meter li:last-child { border-bottom: none; }
.meter li:last-child:before { border: 0; }

.meter li div {
    position: absolute;
    left: -90px;
    top: 50%;
    transform: translateY(-50%);
    width: 80px;
    color: #64748b;
    text-align: right;
    font-weight: 500;
    font-size: 11px;
    line-height: 1;
}

/* Vertical Bars - Blue matching sidebar (blue-600 #2563eb to blue-800 #1e40af) */
.bar {
    position: absolute;
    bottom: 0;
    z-index: 99;
    width: 10%;
    margin: 0 2%;
    background: linear-gradient(180deg, rgba(37, 99, 235, 0.85) 0%, rgba(30, 64, 175, 0.7) 100%);
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
}

.bar:hover {
    background: linear-gradient(180deg, rgba(37, 99, 235, 1) 0%, rgba(30, 64, 175, 0.9) 100%);
}

.bar:before {
    content: "";
    position: absolute;
    left: -10px;
    bottom: 5px;
    height: 100%;
    width: 10px;
    background: linear-gradient(180deg, rgba(30, 64, 175, 0.7) 0%, rgba(30, 58, 138, 0.5) 100%);
    transform: skewY(45deg);
    border-radius: 4px 0 0 0;
}

.bar:after {
    content: "";
    position: absolute;
    top: -10px;
    left: -5px;
    width: calc(100% + 1px);
    height: 10px;
    background-color: rgba(59, 130, 246, 0.7);
    transform: skewX(45deg);
    border-radius: 4px 4px 0 0;
}

.bar-label {
    position: absolute;
    bottom: -28px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 500;
    color: #64748b;
    white-space: nowrap;
}

.bar-value {
    position: absolute;
    top: -32px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: 600;
    color: #2563eb;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.bar:hover .bar-value {
    opacity: 1;
}
</style>
