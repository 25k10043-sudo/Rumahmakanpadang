@extends('layouts.app')

@section('title', 'Masuk staf')

@section('content')
<header class="topbar"><a class="brand" href="{{ route('menu') }}"><span class="brand-mark">RM</span><span>rumah makan<span class="brand-accent"> padang</span><small>RASA MINANG, HANGAT DI HATI</small></span></a><a class="staff-link" href="{{ route('menu') }}">← Lihat menu pelanggan</a></header>
<main class="login-page">
    <div class="login-art"><span>🍛</span><div class="eyebrow">RUANG KERJA TIM</div><h1>Semua rasa,<br>satu <em>kendali.</em></h1><p>Kelola pesanan, pantau ketersediaan lauk, dan layani pelanggan dengan lebih cepat.</p></div>
    <form class="login-card" action="{{ route('staff.authenticate') }}" method="POST">
        @csrf
        <div class="eyebrow">SELAMAT DATANG KEMBALI</div><h2>Masuk ke panel</h2><p>Gunakan akun staf rumah makan untuk melanjutkan.</p>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" placeholder="nama@rumahmakan.id" required autofocus autocomplete="username"></label>
        <label>Kata sandi<input type="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password"></label>
        @if($errors->any())<div class="form-error">{{ $errors->first() }}</div>@endif
        <button class="button button-primary checkout-button" type="submit">Masuk ke panel <span>→</span></button>
        <a class="track-again" href="{{ route('menu') }}">← Kembali ke halaman menu</a>
    </form>
</main>
@endsection
