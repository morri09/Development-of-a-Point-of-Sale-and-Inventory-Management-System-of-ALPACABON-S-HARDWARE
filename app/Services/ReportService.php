<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get sales summary for a specific date range.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getSalesSummary(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        return [
            'total_sales' => round($transactions->sum('total'), 2),
            'total_transactions' => $transactions->count(),
            'total_tax' => round($transactions->sum('tax'), 2),
            'total_discount' => round($transactions->sum('discount'), 2),
            'average_transaction' => $transactions->count() > 0 
                ? round($transactions->avg('total'), 2) 
                : 0,
        ];
    }

    /**
     * Get daily sales summary.
     *
     * @param Carbon|null $date Defaults to today
     * @return array
     */
    public function getDailySummary(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        
        return $this->getSalesSummary($date, $date);
    }

    /**
     * Get weekly sales summary.
     *
     * @param Carbon|null $weekStart Defaults to start of current week
     * @return array
     */
    public function getWeeklySummary(?Carbon $weekStart = null): array
    {
        $weekStart = $weekStart ?? Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        return $this->getSalesSummary($weekStart, $weekEnd);
    }

    /**
     * Get monthly sales summary.
     *
     * @param Carbon|null $monthStart Defaults to start of current month
     * @return array
     */
    public function getMonthlySummary(?Carbon $monthStart = null): array
    {
        $monthStart = $monthStart ?? Carbon::now()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return $this->getSalesSummary($monthStart, $monthEnd);
    }


    /**
     * Get top-selling products within a date range.
     *
     * @param int $limit Number of products to return
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     */
    public function getTopSellingProducts(
        int $limit = 10,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $query = TransactionItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product:id,name,sku,price')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit);

        // Filter by transaction date range
        if ($startDate || $endDate) {
            $query->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                }
            });
        }

        return $query->get()->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? 'Unknown Product',
                'product_sku' => $item->product?->sku ?? '',
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => round((float) $item->total_revenue, 2),
            ];
        });
    }

    /**
     * Get sales data grouped by day for a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getSalesByDay(Carbon $startDate, Carbon $endDate): Collection
    {
        return Transaction::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_sales' => round((float) $item->total_sales, 2),
                    'transaction_count' => (int) $item->transaction_count,
                ];
            });
    }

    /**
     * Get transactions within a date range.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     */
    public function getTransactionsInRange(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = Transaction::query()->with(['user', 'items.product']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get comprehensive report data for a date range.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getReportData(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return [
            'summary' => $this->getSalesSummary($startDate, $endDate),
            'top_products' => $this->getTopSellingProducts(10, $startDate, $endDate),
            'daily_sales' => $startDate && $endDate 
                ? $this->getSalesByDay($startDate, $endDate) 
                : collect([]),
        ];
    }
}
