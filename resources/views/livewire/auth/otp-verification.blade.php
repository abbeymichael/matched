<div class="app-grid flex min-h-screen items-center justify-center px-6 py-12">
  <div class="glass w-full max-w-md rounded-3xl p-8">
    <div class="mb-8 flex items-center gap-3"><div class="avatar h-10 w-10">S</div><span class="font-extrabold tracking-tight">SYNCHRONY</span></div>
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Private matchmaking</p>
    <h1 class="mb-3 text-3xl font-extrabold tracking-tight">Verify your phone</h1>
    <p class="mb-8 text-sm leading-6 text-zinc-400">Enter the 6-digit code we sent to <span class="text-white font-semibold">{{ $phone }}</span>.</p>
    <form wire:submit="submit">
      <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">6-digit code</label>
      <input wire:model="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required class="mb-2 w-full rounded-xl border border-line bg-panel-soft px-4 py-3.5 text-center text-sm font-mono tracking-[0.5em] outline-none" placeholder="000000">
      @error('code')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      <button type="submit" wire:loading.attr="disabled" class="mt-3 w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold transition hover:bg-pink-soft disabled:opacity-50">
        <span wire:loading.remove>Verify</span>
        <span wire:loading>Verifying...</span>
      </button>
    </form>
    <p class="mt-6 text-center text-xs text-zinc-500">
      Didn’t receive it?
      <button type="button" wire:click="resend" wire:loading.attr="disabled" class="ml-1 text-pink hover:underline">Resend</button>
    </p>
  </div>
</div>
