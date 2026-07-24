<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-4xl">
    <h1 class="mb-6 text-2xl font-extrabold">Verification Queue</h1>

    <div class="space-y-4">
      @forelse($users as $u)
        <div class="glass flex items-center gap-4 rounded-2xl p-4" wire:key="verif-{{ $u->id }}">
          <div class="flex gap-1">
            @foreach($u->photos->take(3) as $photo)
              <img src="{{ $photo->url() }}" class="h-16 w-16 rounded-xl object-cover">
            @endforeach
          </div>
          <div class="flex-1">
            <p class="text-sm font-bold">{{ $u->profile?->display_name ?? $u->phone }}</p>
            <p class="text-xs text-zinc-500">{{ $u->phone }} · submitted {{ $u->created_at?->diffForHumans() }}</p>
          </div>
          <div class="flex gap-2">
            <button wire:click="approve('{{ $u->id }}')" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold">Approve</button>
            <button wire:click="reject('{{ $u->id }}')" class="rounded-lg bg-rose-700 px-3 py-2 text-xs font-bold">Reject</button>
          </div>
        </div>
      @empty
        <p class="text-sm text-zinc-500">No pending verifications.</p>
      @endforelse
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
  </div>
</div>
