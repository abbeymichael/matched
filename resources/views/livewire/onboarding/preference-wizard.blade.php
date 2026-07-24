@php($options = $currentField ? \App\Models\ProfileFieldOption::where('field_key', $currentField['key'])->where('is_active', true)->orderBy('sort_order')->get() : collect())
<div class="app-grid min-h-screen px-6 py-12">
  <div class="glass mx-auto max-w-lg rounded-3xl p-8">
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-violet">Preferences — Step {{ $step + 1 }} of {{ $totalSteps }}</p>
    <div class="mb-6 h-1.5 w-full overflow-hidden rounded-full bg-panel-soft">
      <div class="h-full rounded-full bg-violet" style="width: {{ (($step + 1) / max(1, $totalSteps)) * 100 }}%"></div>
    </div>

    <form wire:submit="saveStep">
      @if($step === 0)
        <h1 class="mb-6 text-2xl font-extrabold">Who are you looking for?</h1>
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Age range</label>
        <div class="mb-4 flex gap-3">
          <input type="number" wire:model="ageMin" min="18" max="100" class="w-1/2 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
          <input type="number" wire:model="ageMax" min="18" max="100" class="w-1/2 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
        </div>
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Accepted genders</label>
        <div class="mb-4 flex flex-wrap gap-2">
          @foreach(\App\Models\ProfileFieldOption::where('field_key','gender')->where('is_active',true)->orderBy('sort_order')->get() as $opt)
            <label class="cursor-pointer rounded-full border border-line bg-panel-soft px-4 py-2 text-xs">
              <input type="checkbox" wire:model="acceptedGenders" value="{{ $opt->value }}" class="mr-1"> {{ $opt->label }}
            </label>
          @endforeach
        </div>
        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Max distance (km)</label>
        <input type="number" wire:model="maxDistanceKm" min="1" max="1000" class="mb-4 w-full rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
      @elseif($currentField)
        <h1 class="mb-2 text-2xl font-extrabold">{{ $currentField['label'] }}</h1>
        <p class="mb-6 text-sm text-zinc-400">Which do you accept? Leave blank for no preference.</p>

        @if($currentField['field_type'] === 'single_select')
          <div class="mb-4 flex flex-wrap gap-2">
            @foreach($options as $opt)
              <label class="cursor-pointer rounded-full border border-line bg-panel-soft px-4 py-2 text-xs">
                <input type="checkbox" wire:model="data.{{ $currentField['key'] }}" value="{{ $opt->value }}" class="mr-1"> {{ $opt->label }}
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
          <div class="mb-4 flex gap-3">
            <input type="number" wire:model="data.{{ $currentField['key'] }}.min" placeholder="Min" class="w-1/2 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
            <input type="number" wire:model="data.{{ $currentField['key'] }}.max" placeholder="Max" class="w-1/2 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
          </div>
        @endif
      @endif

      @error('step')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      @error('core')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror

      <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded-xl bg-violet py-3.5 text-sm font-extrabold hover:opacity-90 disabled:opacity-50">
        {{ $step >= $totalSteps - 1 ? 'Review & lock in' : 'Next' }}
      </button>
    </form>
  </div>
</div>
