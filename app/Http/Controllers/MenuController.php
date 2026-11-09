<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $dishes = Dish::query()
            ->where('is_available', true)
            ->orderByDesc('is_recommended')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('menu', [
            'dishes' => $dishes,
            'categories' => $dishes->pluck('category')->unique()->values(),
            'tableNumber' => $request->query('meja'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number' => ['nullable', 'string', 'max:12'],
            'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
            'delivery_address' => ['required_if:order_type,delivery', 'nullable', 'string', 'max:300'],
            'dining_mode' => ['required', 'in:pilih_lauk,hidang'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:100'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $dishes = Dish::query()
                ->whereIn('id', collect($data['items'])->pluck('id'))
                ->where('is_available', true)
                ->get()
                ->keyBy('id');

            if ($dishes->count() !== count($data['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'Ada lauk yang baru saja habis. Perbarui pilihan menu Anda.',
                ]);
            }

            $total = collect($data['items'])->sum(
                fn (array $item) => $dishes[$item['id']]->price * $item['quantity']
            );

            $order = Order::create([
                'order_number' => 'RM-'.Str::upper(Str::random(8)),
                'tracking_token' => Str::random(48),
                'customer_name' => $data['customer_name'],
                'table_number' => ($data['table_number'] ?? null) ?: null,
                'delivery_address' => ($data['delivery_address'] ?? null) ?: null,
                'order_type' => $data['order_type'],
                'dining_mode' => $data['dining_mode'],
                'payment_method' => 'kasir',
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'notes' => ($data['notes'] ?? null) ?: null,
                'total' => $total,
                'user_id' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $dish = $dishes[$item['id']];
                OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $dish->id,
                    'dish_name' => $dish->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $dish->price,
                    'note' => ($item['note'] ?? null) ?: null,
                ]);
            }

            return $order;
        });

        return redirect()->route('orders.track', $order->tracking_token);
    }

    public function track(string $token): View
    {
        $order = Order::query()
            ->with('items')
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('tracking', compact('order'));
    }
}
