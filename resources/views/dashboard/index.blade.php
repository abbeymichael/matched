@extends('layouts.app')
@section('content')
<div class="app-grid min-h-screen">
  <header class="sticky top-0 z-20 flex h-[72px] items-center justify-between border-b border-white/5 bg-[#09090b]/95 px-5 backdrop-blur-xl lg:px-8">
    <div class="flex items-center gap-3"><div class="avatar h-9 w-9 rounded-lg text-lg">S</div><span class="text-lg font-extrabold tracking-tight">SYNCHRONY</span></div>
    <div class="hidden items-center gap-7 text-[10px] font-mono uppercase tracking-[.16em] sm:flex"><span class="text-zinc-600">UTC: 2026-07-20</span><span class="text-emerald-400">● &nbsp; Status: locked & active</span></div>
    <button class="rounded-lg border border-line px-3 py-2 text-xs text-zinc-400 hover:text-white">Menu</button>
  </header>
  <main class="mx-auto max-w-[1440px] px-4 py-6 lg:px-8">
    <div class="mb-7 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-white/5 bg-[#111116] px-5 py-4 text-xs shadow-2xl">
      <div class="flex items-center gap-3"><span class="text-xl text-pink">✧</span><span class="text-zinc-300">Welcome to Synchrony, <strong class="text-pink">MICHAEL ABBEY</strong>. Your matchmaking queue is active.</span></div>
      <div class="flex gap-5 font-mono text-[10px] uppercase tracking-wider text-zinc-500"><span>Pool: <b class="text-zinc-200">6 candidates</b></span><span><b class="text-violet">●</b> Connections: 3</span><span><b class="text-amber-400">●</b> Requests pending: 0</span></div>
    </div>
    <div class="grid gap-6 lg:grid-cols-[300px_1fr]">
      <aside class="sidebar glass self-start rounded-3xl p-5">
        <div class="mb-5 flex items-center justify-between"><div class="flex items-center gap-3"><div class="avatar h-11 w-11 text-xl">♛</div><div><h2 class="text-sm font-extrabold">MICHAEL ABBEY</h2><p class="font-mono text-[10px] text-zinc-500">Male · 25 yrs old</p></div></div><span class="rounded-md bg-pink px-2 py-1 font-mono text-[9px] font-bold uppercase">Secured</span></div>
        <div class="mb-4 rounded-xl border border-white/5 bg-panel-soft p-4"><p class="mb-1 font-mono text-[9px] uppercase tracking-widest text-pink">My archetype</p><p class="text-sm font-bold">The Adventurer</p></div>
        <div class="grid grid-cols-2 gap-3"><div class="rounded-xl bg-panel-soft p-3"><p class="font-mono text-[9px] uppercase text-zinc-500">My passion</p><p class="mt-1 text-xs font-semibold">Coding</p></div><div class="rounded-xl bg-panel-soft p-3"><p class="font-mono text-[9px] uppercase text-zinc-500">Core value</p><p class="mt-1 text-xs font-semibold">Loyalty</p></div><div class="rounded-xl bg-panel-soft p-3"><p class="font-mono text-[9px] uppercase text-zinc-500">Love language</p><p class="mt-1 text-xs font-semibold">Quality Time</p></div><div class="rounded-xl bg-panel-soft p-3"><p class="font-mono text-[9px] uppercase text-zinc-500">Ideal date</p><p class="mt-1 text-xs font-semibold">Cozy coffee shop chat</p></div></div>
        <div class="mt-4 rounded-xl border border-pink/10 bg-pink/10 p-4"><p class="font-mono text-[9px] uppercase tracking-widest text-pink">Absolute priority</p><p class="mt-2 text-xs font-bold">Must share my Core Value</p></div>
        <div class="mt-10 text-center"><p class="mb-2 font-mono text-[9px] text-zinc-600">Want to calibrate your parameters?</p><button class="w-full rounded-xl bg-panel-soft py-3 text-xs font-semibold text-zinc-400 hover:text-white">↻ &nbsp; Unlock & rebuild profile</button></div>
      </aside>
      <section class="min-w-0">
        <nav class="mb-5 grid grid-cols-3 rounded-2xl bg-[#111116] p-1.5"><a class="rounded-xl bg-pink px-3 py-3 text-center text-xs font-bold shadow-lg shadow-pink/20">◎ &nbsp; Discover Matches</a><a class="rounded-xl px-3 py-3 text-center text-xs text-zinc-500 hover:text-white">♡ &nbsp; Match Requests</a><a class="rounded-xl px-3 py-3 text-center text-xs text-zinc-500 hover:text-white">▣ &nbsp; Your Contacts</a></nav>
        <div class="mb-4 flex items-center justify-between rounded-2xl border border-white/5 bg-[#111116] px-4 py-3"><h3 class="font-mono text-xs font-bold uppercase tracking-wider"><span class="mr-2 text-pink">⌁</span> Connection pool</h3><div class="flex gap-1 rounded-xl bg-panel-soft p-1 text-[10px] font-bold"><button class="rounded-lg bg-pink px-3 py-2">All (3)</button><button class="rounded-lg px-3 py-2 text-zinc-500">Mutual (3)</button><button class="rounded-lg px-3 py-2 text-zinc-500">Pending (0)</button></div></div>
        <div class="grid gap-5 xl:grid-cols-2">
          @foreach ([['Sophia Vance','27','The Adventurer','Always packing a duffel bag for the next flight out. I gravitate toward mountain tops, getting lost in crowded markets, and testing local sauces. Life is too short to stick to the same map. Let’s explore together!','SV'],['Elena Rostova','27','The Zen Master','Ceramicist and yoga practitioner. I find harmony in clay and morning meditation. I believe relationships should be built like clay vessels—patiently, with attention, finding balance in the asymmetry. Let’s start slow as friends first.','ER'],['Lily Owusu','26','The Connector','Curious about people, places, and the small rituals that make a life feel full. I love a thoughtful conversation and a plan that leaves room for surprise.','LO']] as $match)
          <article class="rounded-3xl border border-white/5 bg-[#111116] p-5 transition hover:-translate-y-1 hover:border-pink/30"><div class="mb-4 flex items-start justify-between"><div class="flex items-center gap-3"><div class="avatar h-11 w-11 text-xs">{{ $match[4] }}</div><div><h4 class="text-sm font-extrabold">{{ $match[0] }}</h4><p class="font-mono text-[10px] text-zinc-500">Female · {{ $match[1] }} · {{ $match[2] }}</p></div></div><div class="flex items-center gap-2"><span class="font-mono text-[10px] uppercase text-pink">Match<br><em class="not-italic text-zinc-500">breakdown</em></span><span class="grid h-10 w-10 place-items-center rounded-full border-2 border-pink font-mono text-xs font-bold text-pink">{{ $loop->index === 1 ? '99' : '99' }}%</span></div></div><p class="mb-4 text-xs italic leading-5 text-zinc-400">“{{ $match[3] }}”</p><div class="mb-4 flex flex-wrap gap-2"><span class="pill rounded-md px-2 py-1 text-[10px] text-zinc-500">💗 Seeking: Long-Term Connection</span><span class="rounded-md bg-violet/20 px-2 py-1 text-[10px] text-violet-300">⌁ Shared Passion!</span><span class="rounded-md bg-pink/15 px-2 py-1 text-[10px] text-pink-300">● Mutual Values!</span></div><button class="w-full rounded-xl border border-teal/30 bg-teal/15 py-3 text-xs font-bold text-emerald-400 transition hover:bg-teal/25">▢ &nbsp; Double Opt-In Active · Chat ●</button></article>
          @endforeach
        </div>
      </section>
    </div>
  </main>
</div>
@endsection
