@php($options = $currentField ? \App\Models\ProfileFieldOption::where('field_key', $currentField['key'])->where('is_active', true)->orderBy('sort_order')->get() : collect())
<div class="app-grid min-h-screen px-6 py-12">
  <div class="glass mx-auto max-w-lg rounded-3xl p-8">
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Step {{ $step + 1 }} of {{ $totalSteps }}</p>
    <div class="mb-6 h-1.5 w-full overflow-hidden rounded-full bg-panel-soft">
      <div class="h-full rounded-full bg-pink" style="width: {{ (($step + 1) / max(1, $totalSteps)) * 100 }}%"></div>
    </div>

    <form wire:submit="saveStep">
      @if($step === 0)
        <h1 class="mb-6 text-2xl font-extrabold">Tell us about you</h1>
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Display name</label>
        <input wire:model="displayName" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none" placeholder="Ama">
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Date of birth</label>
        <input type="date" wire:model="dateOfBirth" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Gender</label>
        <select wire:model="gender" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
          <option value="">Select...</option>
          @foreach(\App\Models\ProfileFieldOption::where('field_key','gender')->where('is_active',true)->orderBy('sort_order')->get() as $opt)
            <option value="{{ $opt->value }}">{{ $opt->label }}</option>
          @endforeach
        </select>
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">City</label>
        <select wire:model="city" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
          <option value="">Select...</option>
          @foreach($cities as $c)<option value="{{ $c->name }}">{{ $c->name }} ({{ $c->region }})</option>@endforeach
        </select>
      @elseif($currentField)
        <h1 class="mb-2 text-2xl font-extrabold">{{ $currentField['label'] }}</h1>
        @if($currentField['description'])<p class="mb-6 text-sm text-zinc-400">{{ $currentField['description'] }}</p>@endif

        @if($currentField['field_type'] === 'single_select')
          <div class="mb-4 space-y-2">
            @foreach($options as $opt)
              <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm">
                <input type="radio" wire:model="data.{{ $currentField['key'] }}" value="{{ $opt->value }}"> {{ $opt->label }}
              </label>
            @endforeach
          </div>
        @elseif($currentField['field_type'] === 'multi_select')
          <div class="mb-4 flex flex-wrap gap-2">
            @foreach($options as $opt)
              <label class="cursor-pointer rounded-full border border-line bg-panel-soft px-4 py-2 text-xs">
                <input type="checkbox" wire:model="data.{{ $currentField['key'] }}" value="{{ $opt->value }}" class="mr-1"> {{ $opt->label }}
              </label>
            @endforeach
          </div>
        @elseif(in_array($currentField['field_type'], ['scale','range','number']))
          <input type="number" wire:model="data.{{ $currentField['key'] }}" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
        @else
          <input type="text" wire:model="data.{{ $currentField['key'] }}" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
        @endif

        @if(!$currentField['is_required'])
          <p class="mb-4 text-xs text-zinc-500">Optional — you can skip this.</p>
        @endif
      @endif

      @error('step')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      @error('core')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror

      <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold hover:bg-pink-soft disabled:opacity-50">
        {{ $step >= $totalSteps - 1 ? 'Continue to preferences' : 'Next' }}
      </button>
    </form>
  </div>
</div>
