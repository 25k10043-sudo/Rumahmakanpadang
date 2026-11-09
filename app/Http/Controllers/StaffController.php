<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function login(): View
    {
        return view('staff-login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! in_array($request->user()->role, ['admin', 'staff'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Akun ini tidak memiliki akses staf.']);
        }

        return redirect()->route('staff.dashboard');
    }

    public function dashboard(): View
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'staff'], true), 403);

        $orders = Order::query()
            ->with('items')
            ->whereDate('created_at', today())
            ->latest()
            ->limit(40)
            ->get();

        $dishes = Dish::query()->orderBy('category')->orderBy('name')->get();
        $isAdmin = auth()->user()->role === 'admin';

        return view('staff-dashboard', [
            'orders' => $orders,
            'dishes' => $dishes,
            'isAdmin' => $isAdmin,
            'pendingCount' => Order::whereDate('created_at', today())->where('status', 'pending')->count(),
            'paidTotal' => $isAdmin
                ? Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total')
                : null,
            'completedCount' => Order::whereDate('created_at', today())->where('status', 'completed')->count(),
        ]);
    }

    public function toggleDish(Dish $dish): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'staff'], true), 403);

        $dish->update(['is_available' => ! $dish->is_available]);

        return back()->with('success', $dish->name.($dish->is_available ? ' tersedia kembali.' : ' ditandai habis.'));
    }

    public function updateOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'staff'], true), 403);

        $data = $request->validate([
            'action' => ['required', 'in:accept,prepare,serve,complete,pay'],
            'payment_method' => ['nullable', 'in:tunai,qris,debit'],
        ]);

        $transitions = [
            'accept' => ['pending', 'accepted'],
            'prepare' => ['accepted', 'preparing'],
            'serve' => ['preparing', 'served'],
            'complete' => ['served', 'completed'],
        ];

        if ($data['action'] === 'pay') {
            $order->update([
                'payment_method' => $data['payment_method'] ?? 'tunai',
                'payment_status' => 'paid',
            ]);
        } else {
            [$from, $to] = $transitions[$data['action']];
            abort_unless($order->status === $from, 422, 'Status pesanan sudah berubah. Muat ulang halaman.');
            $order->update(['status' => $to]);
        }

        return back()->with('success', 'Pesanan '.$order->order_number.' berhasil diperbarui.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
