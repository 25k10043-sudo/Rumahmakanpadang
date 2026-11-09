@extends('layouts.app')

@section('title', 'Lacak pesanan '.$order->order_number)

@section('content')
@if(! in_array($order->status, ['completed', 'cancelled'], true))
    <meta http-equiv="refresh" content="15">
@endif
<header class="topbar"><a class="brand" href="{{ route('menu') }}"><span class="brand-mark">RM</span><span>rumah makan<span class="brand-accent"> padang</span><small>RASA MINANG, HANGAT DI HATI</small></span></a><a class="staff-link" href="{{ route('menu') }}">← Kembali ke menu</a></header>
<main class="tracking-page">
    <div class="tracking-card">
        <div class="tracking-check">✓</div><div class="eyebrow">PESANAN BERHASIL DICATAT</div>
        <h1>Terima kasih, {{ $order->customer_name }}!</h1>
        <p class="tracking-subtitle">Pesananmu sudah masuk ke dapur. Simpan nomor ini untuk melihat perkembangannya.</p>
        <div class="order-number"><span>NOMOR PESANAN</span><strong>{{ $order->order_number }}</strong><small>{{ $order->created_at->format('d M Y · H:i') }}</small></div>
        @php
            $steps = ['pending' => 'Pesanan masuk', 'accepted' => 'Diterima staf', 'preparing' => 'Sedang disiapkan', 'served' => 'Siap disajikan', 'completed' => 'Selesai'];
            $currentStep = array_search($order->status, array_keys($steps), true);
        @endphp
        <div class="tracking-steps">
            @foreach($steps as $key => $label)
                <div class="tracking-step {{ $currentStep !== false && array_search($key, array_keys($steps), true) <= $currentStep ? 'is-done' : '' }} {{ $order->status === $key ? 'is-current' : '' }}"><span>{{ $currentStep !== false && array_search($key, array_keys($steps), true) < $currentStep ? '✓' : $loop->iteration }}</span><small>{{ $label }}</small></div>
            @endforeach
        </div>
        @if($order->status === 'cancelled')<div class="form-error">Pesanan ini dibatalkan. Silakan hubungi staf kami.</div>@endif
        <div class="receipt-list">
            @foreach($order->items as $item)
                <div><span>{{ $item->quantity }}× {{ $item->dish_name }} @if($item->note)<small> · {{ $item->note }}</small>@endif</span><strong>Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</strong></div>
            @endforeach
            <div class="receipt-total"><span>Total · Bayar di kasir</span><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong></div>
        </div>
        @if($order->delivery_address)<div class="receipt-note"><strong>Alamat pengantaran</strong><p>{{ $order->delivery_address }}</p></div>@endif
        @if($order->notes)<div class="receipt-note"><strong>Catatan dapur</strong><p>{{ $order->notes }}</p></div>@endif
        <button class="button button-dark print-button" onclick="window.print()" type="button">Cetak / simpan struk <span>↗</span></button>
        <a class="track-again" href="{{ route('menu') }}">Pesan menu lainnya</a>
    </div>
</main>
@endsection
