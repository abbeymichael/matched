<div class="app-grid flex min-h-screen flex-col px-6 py-10" wire:poll.5s>
  <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col">
    <a href="{{ route('chat.index') }}" class="mb-4 inline-block text-xs text-zinc-400">&larr; Back to messages</a>

    <div class="glass mb-4 flex items-center gap-3 rounded-2xl p-4">
      <div class="avatar h-10 w-10">{{ substr($otherUser?->profile?->display_name ?? '?', 0, 1) }}</div>
      <p class="font-bold">{{ $otherUser?->profile?->display_name ?? 'Unknown' }}</p>
    </div>

    <div class="glass mb-4 flex-1 space-y-3 overflow-y-auto rounded-2xl p-4" style="max-height: 55vh;">
      @forelse($messages as $message)
        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
          <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm {{ $message->sender_id === auth()->id() ? 'bg-pink' : 'bg-panel-soft' }}">
            @if($message->delivered)
              {{ $message->body }}
            @else
              <span class="italic text-zinc-400">Message held for review</span>
            @endif
          </div>
        </div>
      @empty
        <p class="text-center text-sm text-zinc-500">No messages yet. Say hello 👋</p>
      @endforelse
    </div>

    @error('body')<p class="mb-2 text-xs text-rose-400">{{ $message }}</p>@enderror
    <form wire:submit="send" class="flex gap-2">
      <input wire:model="body" placeholder="Type a message..." class="flex-1 rounded-xl border border-line bg-panel-soft px-4 py-3 text-sm outline-none">
      <button type="submit" class="rounded-xl bg-pink px-6 py-3 text-sm font-extrabold">Send</button>
    </form>
  </div>
</div>
