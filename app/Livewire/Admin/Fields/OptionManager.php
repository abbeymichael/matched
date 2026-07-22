<?php

namespace App\Livewire\Admin\Fields;

use App\Actions\Admin\UpdateFieldOption;
use App\Models\FieldDefinition;
use App\Models\ProfileFieldOption;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class OptionManager extends Component
{
    public FieldDefinition $field;
    public string $editing = '';
    public array $form = [];

    public function mount(string $field): void
    {
        $this->field = FieldDefinition::findOrFail($field);
    }

    public function edit(string $optionId): void
    {
        $option = ProfileFieldOption::findOrFail($optionId);
        $this->editing = $optionId;
        $this->form = [
            'label' => $option->label,
            'sort_order' => $option->sort_order,
            'is_active' => $option->is_active,
        ];
    }

    public function cancel(): void
    {
        $this->editing = '';
        $this->form = [];
    }

    public function save(string $optionId, UpdateFieldOption $action): void
    {
        $this->validate([
            'form.label' => 'required|string|max:255',
            'form.sort_order' => 'required|integer',
            'form.is_active' => 'boolean',
        ]);

        try {
            $action->handle(ProfileFieldOption::findOrFail($optionId), $this->form);
        } catch (ValidationException $e) {
            $this->addError('form', $e->getMessage());
            return;
        }

        $this->editing = '';
        $this->form = [];
    }

    public function render()
    {
        return view('livewire.admin.fields.option-manager', [
            'options' => $this->field->options()->get(),
        ]);
    }
}
