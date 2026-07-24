<div class="app-grid min-h-screen px-6 py-12">
  <div class="glass mx-auto max-w-lg rounded-3xl p-8">
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Verification</p>
    <h1 class="mb-3 text-2xl font-extrabold">Quick selfie check</h1>
    <p class="mb-6 text-sm leading-6 text-zinc-400">A team member manually compares this to your profile photo to reduce fake profiles. It is never shown publicly.</p>
    <form wire:submit="submit">
      <input type="file" wire:model="selfie" accept="image/*" capture="user" class="mb-2 block w-full text-sm">
      @error('selfie')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      <button type="submit" wire:loading.attr="disabled" class="w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold hover:bg-pink-soft disabled:opacity-50">Submit selfie</button>
    </form>
    <button wire:click="skip" class="mt-3 w-full rounded-xl border border-line py-3 text-sm text-zinc-400 hover:text-white">Skip for now</button>
  </div>
</div>
