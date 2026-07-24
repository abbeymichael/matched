<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-5xl">
    <h1 class="mb-6 text-2xl font-extrabold">Field Library</h1>

    <div class="glass overflow-hidden rounded-2xl">
      <table class="w-full text-left text-sm">
        <thead class="bg-panel-soft text-xs uppercase tracking-wider text-zinc-500">
          <tr>
            <th class="px-4 py-3">Key</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Weight</th>
            <th class="px-4 py-3">Sort</th>
            <th class="px-4 py-3">Active</th>
            <th class="px-4 py-3">Hard Filter</th>
            <th class="px-4 py-3">Required</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($fields as $field)
            <tr class="border-t border-line" wire:key="field-{{ $field->id }}">
              @if($editing === $field->id)
                <td class="px-4 py-3">{{ $field->key }}</td>
                <td class="px-4 py-3">{{ $field->category }}</td>
                <td class="px-4 py-3">{{ $field->field_type->value }}</td>
                <td class="px-4 py-3"><input type="number" step="0.1" wire:model="form.weight" class="w-20 rounded-lg border border-line bg-panel-soft px-2 py-1"></td>
                <td class="px-4 py-3"><input type="number" wire:model="form.sort_order" class="w-16 rounded-lg border border-line bg-panel-soft px-2 py-1"></td>
                <td class="px-4 py-3"><input type="checkbox" wire:model="form.is_active"></td>
                <td class="px-4 py-3"><input type="checkbox" wire:model="form.is_hard_filter"></td>
                <td class="px-4 py-3"><input type="checkbox" wire:model="form.is_required"></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <button wire:click="save('{{ $field->id }}')" class="mr-2 rounded-lg bg-pink px-3 py-1 text-xs font-bold">Save</button>
                  <button wire:click="cancel" class="rounded-lg border border-line px-3 py-1 text-xs">Cancel</button>
                </td>
              @else
                <td class="px-4 py-3 font-mono text-xs">{{ $field->key }}{{ $field->is_core ? ' (core)' : '' }}</td>
                <td class="px-4 py-3">{{ $field->category }}</td>
                <td class="px-4 py-3">{{ $field->field_type->value }}</td>
                <td class="px-4 py-3">{{ $field->weight }}</td>
                <td class="px-4 py-3">{{ $field->sort_order }}</td>
                <td class="px-4 py-3">{{ $field->is_active ? '✓' : '—' }}</td>
                <td class="px-4 py-3">{{ $field->is_hard_filter ? '✓' : '—' }}</td>
                <td class="px-4 py-3">{{ $field->is_required ? '✓' : '—' }}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                  @unless($field->is_core)
                    <button wire:click="edit('{{ $field->id }}')" class="mr-2 rounded-lg border border-line px-3 py-1 text-xs">Edit</button>
                  @endunless
                  <a href="{{ route('admin.fields.options', $field) }}" class="rounded-lg border border-line px-3 py-1 text-xs">Options</a>
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6">{{ $fields->links() }}</div>
  </div>
</div>
