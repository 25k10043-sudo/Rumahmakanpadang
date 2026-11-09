@extends('layouts.app')

@section('title', 'Menu')

@section('content')
<header class="topbar">
    <a class="brand" href="{{ route('menu') }}"><span class="brand-mark">RM</span><span>rumah makan<span class="brand-accent"> padang</span><small>RASA MINANG, HANGAT DI HATI</small></span></a>
    <nav class="top-links"><a href="#menu">Menu</a><a href="#tentang">Cerita kami</a><a class="staff-link" href="{{ route('staff.login') }}">Masuk staf <span>↗</span></a></nav>
</header>

<main class="menu-page">
    <section class="hero">
        <div class="hero-copy">
            <div class="eyebrow"><span class="eyebrow-dot"></span> MASAKAN MINANGAN · SETIAP HARI</div>
            <h1>Sepiring rumah<br>di setiap <em>rasa.</em></h1>
            <p>Rempah pilihan, resep keluarga, dan lauk yang dimasak sepenuh hati. Pilih favoritmu, kami siapkan hangat.</p>
            <a class="button button-dark" href="#menu">Jelajahi menu <span>↓</span></a>
            <div class="hero-footnote"><span>✦</span> Dibuat segar setiap hari, dari dapur kami.</div>
        </div>
        <div class="hero-art" aria-label="Hidangan rumah makan Padang">
            <div class="hero-sun"></div><div class="hero-dish">🍛</div>
            <div class="hero-label"><span>REKOMENDASI HARI INI</span><strong>Rendang Sapi</strong><small>Empuk · kaya rempah · favorit semua</small></div>
            <div class="hero-stamp">ASLI<br>MINANG</div>
            <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
        </div>
    </section>

    <section class="menu-section" id="menu">
        <div class="section-heading">
            <div><div class="eyebrow">DARI DAPUR KAMI</div><h2>Menu <em>hari ini</em></h2><p>Pilih lauk kesukaanmu. Semua disiapkan segar untukmu.</p></div>
            <div class="open-status"><span></span> Buka hari ini <small>09.00 – 21.00</small></div>
        </div>
        @if($tableNumber)
            <div class="table-notice">✦ Pesanan untuk meja <strong>{{ $tableNumber }}</strong> · Makan di tempat</div>
        @endif
        <div class="menu-layout">
            <div class="menu-main">
                <div class="category-list">
                    <button class="category-chip active" type="button" data-category="Semua">Semua menu</button>
                    @foreach($categories as $category)
                        <button class="category-chip" type="button" data-category="{{ $category }}">{{ $category }}</button>
                    @endforeach
                </div>
                <div class="dish-grid">
                    @forelse($dishes as $dish)
                        <article class="dish-card" data-category="{{ $dish->category }}">
                            <div class="dish-visual visual-{{ $loop->index % 5 }}">
                                @if($dish->is_recommended)<span class="dish-tag">✦ Favorit</span>@endif
                                <span class="dish-emoji">{{ $dish->emoji }}</span>
                            </div>
                            <div class="dish-info">
                                <div class="dish-category">{{ $dish->category }}</div>
                                <h3>{{ $dish->name }}</h3>
                                <p>{{ $dish->description }}</p>
                                <div class="dish-bottom"><strong>Rp {{ number_format($dish->price, 0, ',', '.') }}<small> / {{ $dish->unit }}</small></strong><button class="add-button" type="button" aria-label="Tambah {{ $dish->name }}" data-id="{{ $dish->id }}" data-name="{{ $dish->name }}" data-price="{{ $dish->price }}">+</button></div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-menu">Menu sedang disiapkan. Silakan kembali sebentar lagi.</div>
                    @endforelse
                </div>
            </div>

            <aside class="cart-panel">
                <div class="cart-heading"><div><span class="eyebrow">PESANANMU</span><h2>Keranjang <span id="cart-count" class="count-badge">0</span></h2></div><span class="cart-icon">♧</span></div>
                <div id="cart-empty" class="cart-empty"><span>🥢</span><strong>Belum ada pilihan</strong><small>Yuk, pilih lauk favoritmu dulu.</small></div>
                <div id="cart-items" class="cart-items"></div>
                <div class="cart-summary"><span>Subtotal</span><strong id="cart-total">Rp 0</strong></div>
                <form id="checkout-form" class="checkout-form" action="{{ route('orders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="table_number" value="{{ $tableNumber }}">
                    <label>Nama pemesan<input name="customer_name" maxlength="100" placeholder="Nama kamu" required></label>
                    <div class="form-row"><label>Pesanan<select name="order_type" id="order-type"><option value="dine_in">Makan di tempat</option><option value="takeaway">Bungkus</option><option value="delivery">Pesan antar</option></select></label><label>Gaya saji<select name="dining_mode"><option value="pilih_lauk">Pilih lauk</option><option value="hidang">Sistem hidang</option></select></label></div>
                    <label id="delivery-address-field" hidden>Alamat pengantaran<input name="delivery_address" maxlength="300" placeholder="Alamat lengkap dan patokan" disabled></label>
                    <label>Catatan untuk dapur <small class="optional">(opsional)</small><textarea name="notes" rows="2" maxlength="500" placeholder="Contoh: sambal dipisah, kuah sedikit"></textarea></label>
                    @if($errors->any())<div class="form-error">{{ $errors->first() }}</div>@endif
                    <button id="checkout-button" class="button button-primary checkout-button" type="submit" disabled>Pesan sekarang <span>→</span></button>
                    <div class="secure-note">Pembayaran dilakukan di kasir · Tunai / QRIS</div>
                </form>
            </aside>
        </div>
    </section>

    <section class="story-strip" id="tentang"><span>✦</span><p>Rempah asli. Resep turun-temurun. Selalu dibuat dengan hati.</p><span>✦</span></section>
    <footer class="site-footer"><a class="brand" href="{{ route('menu') }}"><span class="brand-mark">RM</span><span>rumah makan<span class="brand-accent"> padang</span><small>RASA MINANG, HANGAT DI HATI</small></span></a><span>© {{ date('Y') }} RM Padang Digital · Selamat menikmati!</span></footer>
</main>
@endsection
