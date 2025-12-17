<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app-with-sidebar')]
class SalesReport extends Component
{
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public string $reportType = 'daily'; // daily, weekly, monthly, custom

    protected ReportService $reportService;

    public function boot(ReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    public function mount(): void
    {
        // Default to today's date range
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
    }

    /**
     * Set report type and adjust date range accordingly.
     */
    public function setReportType(string $type): void
    {
        $this->reportType = $type;

        switch ($type) {
            case 'daily':
                $this->dateFrom = Carbon::today()->format('Y-m-d');
                $this->dateTo = Carbon::today()->format('Y-m-d');
                break;
            case 'weekly':
                $this->dateFrom = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                // Keep current dates for custom
                break;
        }
    }

    /**
     * Clear filters and reset to daily.
     */
    public function clearFilters(): void
    {
        $this->setReportType('daily');
    }
    
    /**
     * Export report to CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $startDate = $this->dateFrom ? Carbon::parse($this->dateFrom) : null;
        $endDate = $this->dateTo ? Carbon::parse($this->dateTo) : null;
        
        $reportService = app(ReportService::class);
        $summary = $reportService->getSalesSummary($startDate, $endDate);
        $topProducts = $reportService->getTopSellingProducts(10, $startDate, $endDate);
        
        $transactions = Transaction::query()
            ->with(['items.product', 'user'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'sales_report_' . ($this->dateFrom ?? 'all') . '_to_' . ($this->dateTo ?? 'all') . '.csv';
        
        return response()->streamDownload(function () use ($summary, $topProducts, $transactions) {
            $handle = fopen('php://output', 'w');
            
            // Summary Section
            fputcsv($handle, ['SALES REPORT SUMMARY']);
            fputcsv($handle, ['']);
            fputcsv($handle, ['Total Sales', '₱' . number_format($summary['total_sales'], 2)]);
            fputcsv($handle, ['Total Transactions', $summary['total_transactions']]);
            fputcsv($handle, ['Total Tax', '₱' . number_format($summary['total_tax'], 2)]);
            fputcsv($handle, ['Average Transaction', '₱' . number_format($summary['average_transaction'], 2)]);
            fputcsv($handle, ['']);
            
            // Top Products Section
            fputcsv($handle, ['TOP SELLING PRODUCTS']);
            fputcsv($handle, ['Product', 'Quantity Sold', 'Revenue']);
            foreach ($topProducts as $product) {
                fputcsv($handle, [
                    $product['product_name'],
                    $product['total_quantity'],
                    '₱' . number_format($product['total_revenue'], 2)
                ]);
            }
            fputcsv($handle, ['']);
            
            // Transactions Section
            fputcsv($handle, ['TRANSACTION DETAILS']);
            fputcsv($handle, ['Transaction #', 'Date', 'Cashier', 'Items', 'Subtotal', 'Tax', 'Total', 'Payment Method']);
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->transaction_number,
                    $transaction->created_at->format('Y-m-d H:i'),
                    $transaction->user->name ?? 'N/A',
                    $transaction->items->count(),
                    '₱' . number_format($transaction->subtotal, 2),
                    '₱' . number_format($transaction->tax, 2),
                    '₱' . number_format($transaction->total, 2),
                    $transaction->payment_method->label()
                ]);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        $startDate = $this->dateFrom ? Carbon::parse($this->dateFrom) : null;
        $endDate = $this->dateTo ? Carbon::parse($this->dateTo) : null;

        $reportService = app(ReportService::class);

        $summary = $reportService->getSalesSummary($startDate, $endDate);
        $topProducts = $reportService->getTopSellingProducts(10, $startDate, $endDate);
        $dailySales = ($startDate && $endDate) 
            ? $reportService->getSalesByDay($startDate, $endDate) 
            : collect([]);

        return view('livewire.sales-report', [
            'summary' => $summary,
            'topProducts' => $topProducts,
            'dailySales' => $dailySales,
        ]);
    }
}
