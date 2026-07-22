<?php

namespace App\Livewire\Admin\Fields;

use App\Actions\Admin\UpdateFieldDefinition;
use App\Models\FieldDefinition;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class FieldManager extends Component
{
    use WithPagination;

    public string $editing = '';
    public array $form = [];

    protected function rules(): array
    {
        return [
            'form.weight' => 'required|numeric|min:0',
            'form.sort_order' => 'required|integer',
            'form.is_active' => 'boolean',
            'form.is_hard_filter' => 'boolean',
            'form.is_required' => 'boolean',
        ];
    }

    public function edit(string $id): void
    {
        $field = FieldDefinition::findOrFail($id);
        $this->editing = $id;
        $this->form = [
            'weight' => $field->weight,
            'sort_order' => $field->sort_order,
            'is_active' => $field->is_active,
            'is_hard_filter' => $field->is_hard_filter,
            'is_required' => $field->is_required,
        ];
    }

    public function cancel(): void
    {
        $this->editing = '';
        $this->form = [];
    }

    public function save(string $id, UpdateFieldDefinition $action): void
    {
        $this->validate();

        try {
            $action->handle(FieldDefinition::findOrFail($id), $this->form);
        } catch (ValidationException $e) {
            $this->addError('form', $e->getMessage());
            return;
        }

        $this->editing = '';
        $this->form = [];
        $this->dispatch('field-saved');
    }

    public function render()
    {
        return view('livewire.admin.fields.field-manager', [
            'fields' => FieldDefinition::orderBy('category')->orderBy('sort_order')->paginate(50),
        ]);
    }
}
