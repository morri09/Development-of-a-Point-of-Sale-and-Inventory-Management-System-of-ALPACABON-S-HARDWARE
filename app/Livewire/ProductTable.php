<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class ProductTable extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $categoryFilter = '';
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingProductId = null;
    public ?int $deletingProductId = null;

    // Form fields
    public string $name = '';
    public ?int $category_id = null;
    public string $sku = '';
    public string $description = '';
    public $image = null;
    public ?string $existingImage = null;
    public string $price = '';
    public string $stock_quantity = '';
    public bool $is_active = true;

    protected $queryString = ['search', 'categoryFilter'];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];

        if ($this->editingProductId) {
            $rules['sku'] .= '|unique:products,sku,' . $this->editingProductId;
        } else {
            $rules['sku'] .= '|unique:products,sku';
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $productId): void
    {
        $this->resetForm();
        $this->editingProductId = $productId;
        $this->loadProduct($productId);
        $this->showEditModal = true;
    }

    protected function loadProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->sku = $product->sku ?? '';
        $this->description = $product->description ?? '';
        $this->existingImage = $product->image;
        $this->price = (string) $product->price;
        $this->stock_quantity = (string) $product->stock_quantity;
        $this->is_active = $product->is_active;
    }

    protected function resetForm(): void
    {
        $this->editingProductId = null;
        $this->name = '';
        $this->category_id = null;
        $this->sku = '';
        $this->description = '';
        $this->image = null;
        $this->existingImage = null;
        $this->price = '';
        $this->stock_quantity = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function openDeleteModal(int $productId): void
    {
        $this->deletingProductId = $productId;
        $this->showDeleteModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->editingProductId = null;
        $this->deletingProductId = null;
        $this->resetForm();
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->existingImage = null;
    }

    public function saveProduct(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'sku' => $this->sku ?: null,
            'description' => $this->description ?: null,
            'price' => (float) $this->price,
            'stock_quantity' => (int) $this->stock_quantity,
            'is_active' => $this->is_active,
        ];

        // Handle image upload
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
            $data['image'] = $imagePath;
        } elseif ($this->existingImage === null && $this->editingProductId) {
            // Image was removed
            $data['image'] = null;
        }

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update($data);
            session()->flash('message', 'Product updated successfully.');
        } else {
            Product::create($data);
            session()->flash('message', 'Product created successfully.');
        }

        $this->closeModals();
    }

    public function deleteProduct(): void
    {
        $product = Product::findOrFail($this->deletingProductId);
        $product->delete();

        $this->closeModals();
        session()->flash('message', 'Product deleted successfully.');
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
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = Category::orderBy('name')->get();

        return view('livewire.product-table', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
