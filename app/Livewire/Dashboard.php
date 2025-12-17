<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app-with-sidebar')]
class Dashboard extends Component
{
    /**
     * Low stock threshold - products with stock at or below this are considered low.
     */
    protected int $lowStockThreshold = 10;

    /**
     * Number of recent transactions to display.
     */
    protected int $recentTransactionsLimit = 5;

    /**
     * Number of low stock products to display.
     */
    protected int $lowStockLimit = 5;

    public function render()
    {
        $reportService = app(ReportService::class);

        // Today's sales summary
        $todaySummary = $reportService->getDailySummary(Carbon::today());

        // Recent transactions
        $recentTransactions = Transaction::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($this->recentTransactionsLimit)
            ->get();

        // Low stock alerts - products with stock_quantity <= threshold (includes out of stock)
        // Show ALL products with low/no stock, regardless of is_active status
        $lowStockProducts = Product::where('stock_quantity', '<=', $this->lowStockThreshold)
            ->orderBy('stock_quantity', 'asc')
            ->limit($this->lowStockLimit)
            ->get();

        // Quick stats
        $totalProducts = Product::where('is_active', true)->count();
        // Count ALL out of stock products regardless of active status
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        // Weekly sales data for chart (last 7 days)
        $chartData = $this->getWeeklySalesData();

        return view('livewire.dashboard', [
            'todaySummary' => $todaySummary,
            'recentTransactions' => $recentTransactions,
            'lowStockProducts' => $lowStockProducts,
            'totalProducts' => $totalProducts,
            'outOfStockCount' => $outOfStockCount,
            'lowStockThreshold' => $this->lowStockThreshold,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Get sales data for the last 7 days.
     */
    protected function getWeeklySalesData(): array
    {
        $labels = [];
        $sales = [];
        $transactions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            
            $dayData = Transaction::whereDate('created_at', $date)
                ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as count')
                ->first();
            
            $sales[] = (float) ($dayData->total_sales ?? 0);
            $transactions[] = (int) ($dayData->count ?? 0);
        }

        return [
            'labels' => $labels,
            'sales' => $sales,
            'transactions' => $transactions,
        ];
    }
}
