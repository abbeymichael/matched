<div class="app-grid flex min-h-screen items-center justify-center px-6 py-12">
  <div class="glass w-full max-w-md rounded-3xl p-8">
    <div class="mb-8 flex items-center gap-3"><div class="avatar h-10 w-10">S</div><span class="font-extrabold tracking-tight">SYNCHRONY</span></div>
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Private matchmaking</p>
    <h1 class="mb-3 text-3xl font-extrabold tracking-tight">Enter your phone</h1>
    <p class="mb-8 text-sm leading-6 text-zinc-400">We’ll send a secure one-time code. No passwords, no social logins.</p>
    <form wire:submit="submit">
      <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-500">Ghana phone number</label>
      <div class="mb-2 flex overflow-hidden rounded-xl border border-line bg-panel-soft">
        <span class="flex items-center border-r border-line px-4 font-mono text-sm text-zinc-400">+233</span>
        <input wire:model="phone" inputmode="tel" autocomplete="tel" required class="w-full bg-transparent px-4 py-3.5 text-sm outline-none" placeholder="24 000 0000">
      </div>
      @error('phone')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      <button type="submit" wire:loading.attr="disabled" class="mt-3 w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold transition hover:bg-pink-soft disabled:opacity-50">
        <span wire:loading.remove>Send verification code</span>
        <span wire:loading>Sending...</span>
      </button>
    </form>
    <p class="mt-6 text-center text-xs leading-5 text-zinc-500">By continuing, you agree to our terms and consent to personal data processing for matchmaking.</p>
  </div>
</div>
