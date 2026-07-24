<div class="app-grid min-h-screen px-6 py-12">
  <div class="glass mx-auto max-w-2xl rounded-3xl p-8">
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Final step</p>
    <h1 class="mb-6 text-2xl font-extrabold">Review before you lock in</h1>

    <div class="mb-6 rounded-2xl border border-line bg-panel-soft p-5">
      <h2 class="mb-3 text-sm font-bold text-zinc-300">Your profile</h2>
      <dl class="grid grid-cols-2 gap-3 text-sm">
        <div><dt class="text-zinc-500">Name</dt><dd>{{ $profile?->display_name }}</dd></div>
        <div><dt class="text-zinc-500">Age</dt><dd>{{ $profile?->date_of_birth?->age }}</dd></div>
        <div><dt class="text-zinc-500">Gender</dt><dd>{{ $profile?->gender }}</dd></div>
        <div><dt class="text-zinc-500">City</dt><dd>{{ $profile?->city }}</dd></div>
      </dl>
      @foreach($fields as $field)
        <div class="mt-3 border-t border-line pt-3 text-sm">
          <span class="text-zinc-500">{{ $field->label }}:</span>
          <span>{{ is_array($fieldValues[$field->key]->value ?? null) ? implode(', ', $fieldValues[$field->key]->value) : ($fieldValues[$field->key]->value ?? '—') }}</span>
        </div>
      @endforeach
    </div>

    <div class="mb-6 rounded-2xl border border-line bg-panel-soft p-5">
      <h2 class="mb-3 text-sm font-bold text-zinc-300">Your preferences</h2>
      <dl class="grid grid-cols-2 gap-3 text-sm">
        <div><dt class="text-zinc-500">Age range</dt><dd>{{ $preferences?->age_min }}–{{ $preferences?->age_max }}</dd></div>
        <div><dt class="text-zinc-500">Distance</dt><dd>{{ $preferences?->max_distance_km }} km</dd></div>
        <div class="col-span-2"><dt class="text-zinc-500">Accepted genders</dt><dd>{{ implode(', ', $preferences?->accepted_genders ?? []) }}</dd></div>
      </dl>
    </div>

    <div class="mb-6 rounded-2xl border border-pink/40 bg-pink/5 p-5 text-sm leading-6 text-zinc-300">
      <strong class="text-pink">This is permanent.</strong> Once you lock in, your profile and preferences cannot be edited.
      You get one lifetime reset from Settings if you ever need a full redo.
    </div>

    <label class="mb-6 flex items-start gap-3 text-sm">
      <input type="checkbox" wire:model="confirmed" class="mt-1">
      <span>I understand this action is permanent and I cannot edit my answers afterward.</span>
    </label>
    @error('confirmed')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
    @error('lock')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror

    <button wire:click="lock" wire:loading.attr="disabled" class="w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold hover:bg-pink-soft disabled:opacity-50">Lock In &amp; Find Matches</button>
  </div>
</div>
