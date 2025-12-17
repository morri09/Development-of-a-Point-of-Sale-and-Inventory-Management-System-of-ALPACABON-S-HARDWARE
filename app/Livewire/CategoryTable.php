<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app-with-sidebar')]
class CategoryTable extends Component
{
    use WithPagination;

    public string $search = '';
    
    // Modal states
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    
    // Form fields
    public ?int $editingCategoryId = null;
    public string $name = '';
    public string $description = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        if ($this->editingCategoryId) {
            $rules['name'] .= '|unique:categories,name,' . $this->editingCategoryId;
        } else {
            $rules['name'] .= '|unique:categories,name';
        }

        return $rules;
    }

    protected array $messages = [
        'name.required' => 'Category name is required.',
        'name.unique' => 'This category name already exists.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->showEditModal = true;
    }

    public function openDeleteModal(int $categoryId): void
    {
        $this->editingCategoryId = $categoryId;
        $this->showDeleteModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingCategoryId = null;
        $this->name = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
        ];

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update($data);
            session()->flash('message', 'Category updated successfully.');
        } else {
            Category::create($data);
            session()->flash('message', 'Category created successfully.');
        }

        $this->closeModals();
    }

    public function deleteCategory(): void
    {
        $category = Category::findOrFail($this->editingCategoryId);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            session()->flash('error', 'Cannot delete category with associated products.');
            $this->closeModals();
            return;
        }

        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
        $this->closeModals();
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.category-table', [
            'categories' => $categories,
        ]);
    }
}
