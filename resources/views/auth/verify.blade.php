@extends('layouts.app')
@section('content')
<div class="app-grid flex min-h-screen items-center justify-center px-6 py-12">
  <div class="glass w-full max-w-md rounded-3xl p-8">
    <a href="{{ route('login') }}" class="mb-8 inline-block text-xs text-zinc-500 hover:text-white">← Change phone number</a>
    <p class="mb-2 font-mono text-xs uppercase tracking-[.2em] text-pink">Secure verification</p>
    <h1 class="mb-3 text-3xl font-extrabold tracking-tight">Check your messages</h1>
    <p class="mb-8 text-sm leading-6 text-zinc-400">Enter the 6-digit code we sent to your Ghana number. It expires in 5 minutes.</p>
    <form method="POST" action="{{ route('otp.verify') }}">
      @csrf
      <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="mb-5 w-full rounded-xl border border-line bg-panel-soft px-4 py-4 text-center font-mono text-2xl tracking-[.5em] outline-none focus:border-pink" placeholder="000000" autofocus>
      @error('code')<p class="mb-4 text-xs text-rose-400">{{ $message }}</p>@enderror
      <button class="w-full rounded-xl bg-pink py-3.5 text-sm font-extrabold transition hover:bg-pink-soft">Verify and continue</button>
    </form>
  </div>
</div>
@endsection
