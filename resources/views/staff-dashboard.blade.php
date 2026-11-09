@extends('layouts.app')

@section('title', 'Panel staf')

@section('content')
<header class="topbar dashboard-topbar"><a class="brand" href="{{ route('staff.dashboard') }}"><span class="brand-mark">RM</span><span>rumah makan<span class="brand-accent"> padang</span><small>PANEL OPERASIONAL</small></span></a><div class="staff-user"><span>Halo, {{ auth()->user()->name }} <small>{{ $isAdmin ? 'ADMIN' : 'STAF' }}</small></span><form action="{{ route('staff.logout') }}" method="POST">@csrf<button class="logout-button">Keluar</button></form></div></header>
<main class="dashboard-page">
    <div class="dashboard-heading"><div><div class="eyebrow">RUANG KENDALI · {{ now()->translatedFormat('l, d F Y') }}</div><h1>Selamat bertugas, <em>{{ explode(' ', auth()->user()->name)[0] }}.</em></h1><p>Pantau pesanan dan ketersediaan menu hari ini.</p></div><a class="button button-dark" href="{{ route('menu') }}" target="_blank">Buka menu pelanggan ↗</a></div>
    @if(session('success'))<div class="success-banner">✓ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="form-error">{{ $errors->first() }}</div>@endif
    <div class="stats-grid">
        @if($isAdmin)<div class="stat-card stat-highlight"><span>OMZET HARI INI</span><strong>Rp {{ number_format($paidTotal, 0, ',', '.') }}</strong><small>Transaksi yang sudah dibayar</small></div>@endif
        <div class="stat-card"><span>ANTREAN BARU</span><strong>{{ str_pad($pendingCount, 2, '0', STR_PAD_LEFT) }}</strong><small>Pesanan menunggu konfirmasi</small></div>
        <div class="stat-card"><span>PESANAN SELESAI</span><strong>{{ str_pad($completedCount, 2, '0', STR_PAD_LEFT) }}</strong><small>Transaksi selesai hari ini</small></div>
        <div class="stat-card"><span>MENU TERSEDIA</span><strong>{{ $dishes->where('is_available', true)->count() }}<small> / {{ $dishes->count() }} lauk</small></strong><small>Atur ketersediaan etalase</small></div>
    </div>
    <div class="dashboard-columns">
        <section class="dashboard-panel orders-panel">
            <div class="panel-heading"><div><div class="eyebrow">PESANAN MASUK</div><h2>Antrean hari ini</h2></div><span class="live-pill"><i></i> DIPERBARUI SAAT DIMUAT</span></div>
            @forelse($orders as $order)
                <article class="order-card">
                    <div class="order-card-top"><div><span class="order-code">{{ $order->order_number }}</span><span class="status-pill status-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></div><small>{{ $order->created_at->format('H:i') }}</small></div>
                    <div class="order-customer"><strong>{{ $order->customer_name }}</strong><span>{{ ['dine_in' => 'Makan di tempat', 'takeaway' => 'Bungkus', 'delivery' => 'Pesan antar'][$order->order_type] }}{{ $order->table_number ? ' · Meja '.$order->table_number : '' }}</span></div>
                    @if($order->delivery_address)<div class="order-note">Alamat antar: {{ $order->delivery_address }}</div>@endif
                    <div class="order-items">@foreach($order->items as $item)<span>{{ $item->quantity }}× {{ $item->dish_name }}</span>@endforeach</div>
                    @if($order->notes)<div class="order-note">Catatan: {{ $order->notes }}</div>@endif
                    <div class="order-footer"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong><span class="{{ $order->payment_status === 'paid' ? 'paid-label' : 'unpaid-label' }}">{{ $order->payment_status === 'paid' ? '✓ LUNAS · '.strtoupper($order->payment_method) : 'BELUM DIBAYAR' }}</span></div>
                    <div class="order-actions">
                        @php
                            $nextAction = ['pending' => ['accept', 'Terima pesanan'], 'accepted' => ['prepare', 'Mulai siapkan'], 'preparing' => ['serve', 'Siap dihidangkan'], 'served' => ['complete', 'Tandai selesai']];
                        @endphp
                        @if(isset($nextAction[$order->status]))
                            <form action="{{ route('staff.orders.update', $order) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="action" value="{{ $nextAction[$order->status][0] }}"><button class="button button-primary small-button">{{ $nextAction[$order->status][1] }} →</button></form>
                        @endif
                        @if($order->payment_status !== 'paid')
                            <form action="{{ route('staff.orders.update', $order) }}" method="POST" class="pay-form">@csrf @method('PATCH')<input type="hidden" name="action" value="pay"><select name="payment_method" aria-label="Metode pembayaran"><option value="tunai">Tunai</option><option value="qris">QRIS</option><option value="debit">Debit</option></select><button class="pay-button">Catat bayar</button></form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="dashboard-empty"><span>🧾</span><strong>Belum ada pesanan hari ini</strong><small>Pesanan pelanggan akan muncul di sini.</small></div>
            @endforelse
        </section>
        <section class="dashboard-panel inventory-panel">
            <div class="panel-heading"><div><div class="eyebrow">ETALASE HARI INI</div><h2>Ketersediaan lauk</h2></div><span class="stock-count">{{ $dishes->where('is_available', true)->count() }} tersedia</span></div>
            <p class="inventory-hint">Lauk habis akan langsung hilang dari menu pelanggan.</p>
            @foreach($dishes as $dish)
                <div class="inventory-item"><span class="inventory-emoji">{{ $dish->emoji }}</span><div class="inventory-name"><strong>{{ $dish->name }}</strong><small>{{ $dish->category }}</small></div><span class="availability {{ $dish->is_available ? 'available' : 'unavailable' }}">{{ $dish->is_available ? 'Ada' : 'Habis' }}</span><form action="{{ route('staff.dishes.toggle', $dish) }}" method="POST">@csrf @method('PATCH')<button class="toggle-button {{ $dish->is_available ? 'toggle-on' : '' }}" type="submit" aria-label="Ubah ketersediaan {{ $dish->name }}"><i></i></button></form></div>
            @endforeach
        </section>
    </div>
    <p class="dashboard-footnote">Untuk menjaga keamanan data, tutup sesi setelah selesai bertugas.</p>
</main>
@endsection
