<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.fields') }}" class="mb-6 inline-block text-xs text-zinc-400">&larr; Back to fields</a>
    <h1 class="mb-6 text-2xl font-extrabold">Options for "{{ $field->label }}"</h1>

    <div class="glass overflow-hidden rounded-2xl">
      <table class="w-full text-left text-sm">
        <thead class="bg-panel-soft text-xs uppercase tracking-wider text-zinc-500">
          <tr>
            <th class="px-4 py-3">Value</th>
            <th class="px-4 py-3">Label</th>
            <th class="px-4 py-3">Sort</th>
            <th class="px-4 py-3">Active</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($options as $option)
            <tr class="border-t border-line" wire:key="option-{{ $option->id }}">
              @if($editing === $option->id)
                <td class="px-4 py-3 font-mono text-xs">{{ $option->value }}</td>
                <td class="px-4 py-3"><input wire:model="form.label" class="w-full rounded-lg border border-line bg-panel-soft px-2 py-1"></td>
                <td class="px-4 py-3"><input type="number" wire:model="form.sort_order" class="w-16 rounded-lg border border-line bg-panel-soft px-2 py-1"></td>
                <td class="px-4 py-3"><input type="checkbox" wire:model="form.is_active"></td>
                <td class="px-4 py-3 whitespace-nowrap">
                  <button wire:click="save('{{ $option->id }}')" class="mr-2 rounded-lg bg-pink px-3 py-1 text-xs font-bold">Save</button>
                  <button wire:click="cancel" class="rounded-lg border border-line px-3 py-1 text-xs">Cancel</button>
                </td>
              @else
                <td class="px-4 py-3 font-mono text-xs">{{ $option->value }}</td>
                <td class="px-4 py-3">{{ $option->label }}</td>
                <td class="px-4 py-3">{{ $option->sort_order }}</td>
                <td class="px-4 py-3">{{ $option->is_active ? '✓' : '—' }}</td>
                <td class="px-4 py-3"><button wire:click="edit('{{ $option->id }}')" class="rounded-lg border border-line px-3 py-1 text-xs">Edit</button></td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
