<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductForm extends Component
{
    public ?int $productId = null;
    public bool $isOpen = false;

    // Form fields
    public string $name = '';
    public ?int $category_id = null;
    public string $sku = '';
    public string $description = '';
    public string $price = '';
    public string $stock_quantity = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];

        // SKU uniqueness check
        if ($this->productId) {
            $rules['sku'] .= '|unique:products,sku,' . $this->productId;
        } else {
            $rules['sku'] .= '|unique:products,sku';
        }

        return $rules;
    }

    protected array $messages = [
        'name.required' => 'Product name is required.',
        'name.max' => 'Product name cannot exceed 255 characters.',
        'category_id.required' => 'Please select a category.',
        'category_id.exists' => 'Selected category does not exist.',
        'price.required' => 'Price is required.',
        'price.numeric' => 'Price must be a valid number.',
        'price.min' => 'Price cannot be negative.',
        'stock_quantity.required' => 'Stock quantity is required.',
        'stock_quantity.integer' => 'Stock quantity must be a whole number.',
        'stock_quantity.min' => 'Stock quantity cannot be negative.',
        'sku.unique' => 'This SKU is already in use.',
    ];


    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;
        $this->isOpen = true;

        if ($productId) {
            $this->loadProduct($productId);
        } else {
            $this->resetForm();
        }
    }

    #[On('open-product-form')]
    public function openForm(?int $productId = null): void
    {
        $this->productId = $productId;
        $this->isOpen = true;

        if ($productId) {
            $this->loadProduct($productId);
        } else {
            $this->resetForm();
        }
    }

    protected function loadProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->sku = $product->sku ?? '';
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->stock_quantity = (string) $product->stock_quantity;
        $this->is_active = $product->is_active;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->category_id = null;
        $this->sku = '';
        $this->description = '';
        $this->price = '';
        $this->stock_quantity = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->dispatch('close-product-form');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'category_id' => $this->category_id ?: null,
            'sku' => $this->sku ?: null,
            'description' => $this->description ?: null,
            'price' => (float) $this->price,
            'stock_quantity' => (int) $this->stock_quantity,
            'is_active' => $this->is_active,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            session()->flash('message', 'Product updated successfully.');
        } else {
            Product::create($data);
            session()->flash('message', 'Product created successfully.');
        }

        $this->dispatch('product-saved');
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        return view('livewire.product-form', [
            'categories' => $categories,
            'isEditing' => (bool) $this->productId,
        ]);
    }
}
