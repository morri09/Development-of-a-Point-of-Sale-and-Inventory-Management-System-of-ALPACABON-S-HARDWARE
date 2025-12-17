<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class TransactionHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public bool $showDetailsModal = false;
    public ?int $viewingTransactionId = null;
    public ?Transaction $viewingTransaction = null;

    protected $queryString = ['search', 'dateFrom', 'dateTo'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    /**
     * Open transaction details modal.
     */
    public function viewDetails(int $transactionId): void
    {
        $this->viewingTransactionId = $transactionId;
        $this->viewingTransaction = Transaction::with(['items.product', 'user'])->find($transactionId);
        $this->showDetailsModal = true;
    }

    /**
     * Close transaction details modal.
     */
    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->viewingTransactionId = null;
        $this->viewingTransaction = null;
    }


    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with(['user', 'items'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
        ]);
    }
}
