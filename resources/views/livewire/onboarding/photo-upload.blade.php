<div class="app-grid min-h-screen px-6 py-12">
  <div class="glass mx-auto max-w-lg rounded-3xl p-8">
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Step 1</p>
    <h1 class="mb-6 text-2xl font-extrabold">Upload your photos</h1>
    <div class="mb-6 grid grid-cols-2 gap-3">
      @foreach($photos as $photo)
        <div class="relative overflow-hidden rounded-xl border border-line">
          <img src="{{ $photo->url() }}" loading="lazy" class="h-32 w-full object-cover">
          @if($photo->is_primary)<span class="absolute left-1 top-1 rounded bg-pink px-2 py-0.5 text-[10px] font-bold">PRIMARY</span>@endif
          <button wire:click="deletePhoto('{{ $photo->id }}')" class="absolute right-1 top-1 rounded bg-black/60 px-2 py-0.5 text-[10px]">Remove</button>
        </div>
      @endforeach
    </div>
    <input type="file" wire:model="photo" accept="image/*" class="mb-2 block w-full text-sm">
    @error('photo')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
    <button wire:click="continue" class="mt-4 w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold hover:bg-pink-soft">Continue</button>
  </div>
</div>
