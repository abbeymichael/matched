<div class="app-grid min-h-screen px-6 py-10">
  <div class="mx-auto max-w-2xl">
    <h1 class="mb-6 text-2xl font-extrabold">Messages</h1>

    <div class="space-y-3">
      @forelse($matches as $match)
        @php($other = $match->otherUser(auth()->id()))
        <a href="{{ route('chat.show', $match) }}" class="glass flex items-center gap-4 rounded-2xl p-4">
          <div class="avatar h-12 w-12">{{ substr($other?->profile?->display_name ?? '?', 0, 1) }}</div>
          <div class="flex-1">
            <p class="text-sm font-bold">{{ $other?->profile?->display_name ?? 'Unknown' }}</p>
            <p class="truncate text-xs text-zinc-500">{{ $match->messages->first()?->delivered ? $match->messages->first()?->body : ($match->messages->first() ? 'Message held for review' : 'Say hello 👋') }}</p>
          </div>
        </a>
      @empty
        <p class="text-sm text-zinc-500">No matches yet. Keep browsing to find your people.</p>
      @endforelse
    </div>
  </div>
</div>
