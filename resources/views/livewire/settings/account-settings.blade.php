<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-xl">
    <h1 class="mb-6 text-2xl font-extrabold">Account Settings</h1>

    <div class="glass mb-6 rounded-2xl p-6">
      <h2 class="mb-2 text-sm font-bold">Match Threshold</h2>
      <p class="mb-4 text-xs text-zinc-500">Only show matches scoring above this compatibility percentage.</p>
      <input type="range" min="0" max="100" wire:model="threshold" class="mb-2 w-full">
      <p class="mb-4 text-center text-lg font-extrabold text-pink">{{ $threshold }}%</p>
      <button wire:click="updateThreshold" class="w-full rounded-xl bg-pink py-3 text-sm font-extrabold">Save Threshold</button>
    </div>

    <div class="glass rounded-2xl border border-rose-900/60 p-6">
      <h2 class="mb-2 text-sm font-bold text-rose-400">Reset Profile &amp; Preferences</h2>
      <p class="mb-4 text-xs text-zinc-500">
        This unlocks your profile and preferences so you can redo onboarding. This can only be done once.
      </p>
      @if(auth()->user()->reset_used)
        <p class="text-xs text-zinc-500">You've already used your one-time reset.</p>
      @else
        <label class="mb-3 flex items-center gap-2 text-xs text-zinc-400">
          <input type="checkbox" wire:model="confirmReset">
          I understand this is permanent and I only get one reset.
        </label>
        @error('confirmReset')<p class="mb-3 text-xs text-rose-400">{{ $message }}</p>@enderror
        <button wire:click="resetProfile" class="w-full rounded-xl bg-rose-700 py-3 text-sm font-extrabold">Reset My Profile</button>
      @endif
    </div>
  </div>
</div>
