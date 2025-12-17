<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class InventoryManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $stockFilter = ''; // 'low', 'out', 'all'
    public bool $showAdjustModal = false;
    public ?int $adjustingProductId = null;
    public ?string $adjustingProductName = null;
    public ?int $currentStock = null;
    public int $adjustmentQuantity = 0;
    public string $adjustmentType = 'add'; // 'add' or 'subtract'
    public string $adjustmentReason = '';

    protected $queryString = ['search', 'stockFilter'];

    protected $rules = [
        'adjustmentQuantity' => 'required|integer|min:1',
        'adjustmentReason' => 'required|string|min:3|max:255',
    ];

    protected $messages = [
        'adjustmentQuantity.required' => 'Please enter a quantity.',
        'adjustmentQuantity.min' => 'Quantity must be at least 1.',
        'adjustmentReason.required' => 'Please provide a reason for this adjustment.',
        'adjustmentReason.min' => 'Reason must be at least 3 characters.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStockFilter(): void
    {
        $this->resetPage();
    }

    public function openAdjustModal(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->adjustingProductId = $productId;
        $this->adjustingProductName = $product->name;
        $this->currentStock = $product->stock_quantity;
        $this->adjustmentQuantity = 0;
        $this->adjustmentType = 'add';
        $this->adjustmentReason = '';
        $this->showAdjustModal = true;
    }

    public function closeAdjustModal(): void
    {
        $this->showAdjustModal = false;
        $this->adjustingProductId = null;
        $this->adjustingProductName = null;
        $this->currentStock = null;
        $this->adjustmentQuantity = 0;
        $this->adjustmentType = 'add';
        $this->adjustmentReason = '';
        $this->resetValidation();
    }


    public function saveAdjustment(): void
    {
        $this->validate();

        if (!$this->adjustingProductId) {
            session()->flash('error', 'No product selected for adjustment.');
            return;
        }

        $quantity = $this->adjustmentType === 'add' 
            ? abs($this->adjustmentQuantity) 
            : -abs($this->adjustmentQuantity);

        try {
            $inventoryService = app(InventoryService::class);
            $inventoryService->adjustStock(
                $this->adjustingProductId,
                $quantity,
                $this->adjustmentReason,
                auth()->id()
            );

            $this->closeAdjustModal();
            session()->flash('message', 'Stock adjustment saved successfully.');
        } catch (\InvalidArgumentException $e) {
            $this->addError('adjustmentQuantity', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save stock adjustment: ' . $e->getMessage());
        }
    }

    public function quickAdd(int $productId): void
    {
        try {
            $product = Product::findOrFail($productId);
            $inventoryService = app(InventoryService::class);
            $inventoryService->adjustStock(
                $productId,
                1,
                'Quick add +1',
                auth()->id()
            );
            $this->dispatch('toast', message: "{$product->name}: +1", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Failed to add stock', type: 'error');
        }
    }

    public function quickSubtract(int $productId): void
    {
        try {
            $product = Product::findOrFail($productId);
            if ($product->stock_quantity <= 0) {
                $this->dispatch('toast', message: 'Cannot reduce stock below zero', type: 'error');
                return;
            }
            
            $inventoryService = app(InventoryService::class);
            $inventoryService->adjustStock(
                $productId,
                -1,
                'Quick subtract -1',
                auth()->id()
            );
            $this->dispatch('toast', message: "{$product->name}: -1", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Failed to subtract stock', type: 'error');
        }
    }

    public function getNewStockProperty(): int
    {
        if ($this->currentStock === null) {
            return 0;
        }

        $change = $this->adjustmentType === 'add' 
            ? abs($this->adjustmentQuantity) 
            : -abs($this->adjustmentQuantity);

        return max(0, $this->currentStock + $change);
    }

    public function render()
    {
        $products = Product::query()
            ->with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($categoryQuery) {
                          $categoryQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->stockFilter === 'low', function ($query) {
                $query->where('stock_quantity', '>', 0)
                      ->where('stock_quantity', '<=', 10);
            })
            ->when($this->stockFilter === 'out', function ($query) {
                $query->where('stock_quantity', '<=', 0);
            })
            ->orderBy('stock_quantity', 'asc')
            ->paginate(15);

        return view('livewire.inventory-manager', [
            'products' => $products,
        ]);
    }
}
