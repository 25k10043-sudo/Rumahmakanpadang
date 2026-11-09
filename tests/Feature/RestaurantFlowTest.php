<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_and_track_an_order(): void
    {
        $dish = Dish::create([
            'name' => 'Rendang Sapi',
            'category' => 'Lauk utama',
            'price' => 28000,
            'unit' => 'porsi',
            'emoji' => '🍛',
            'is_available' => true,
        ]);

        $response = $this->post(route('orders.store'), [
            'customer_name' => 'Sari',
            'order_type' => 'dine_in',
            'dining_mode' => 'pilih_lauk',
            'items' => [['id' => $dish->id, 'quantity' => 2]],
        ]);

        $response->assertRedirect();
        $order = Order::with('items')->firstOrFail();
        $this->assertSame(56000, $order->total);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame(2, $order->items->first()->quantity);

        $this->get(route('orders.track', $order->tracking_token))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Rendang Sapi');
    }

    public function test_customer_cannot_order_a_dish_marked_unavailable(): void
    {
        $dish = Dish::create([
            'name' => 'Ayam Pop',
            'category' => 'Lauk utama',
            'price' => 22000,
            'is_available' => false,
        ]);

        $this->from(route('menu'))->post(route('orders.store'), [
            'customer_name' => 'Sari',
            'order_type' => 'dine_in',
            'dining_mode' => 'pilih_lauk',
            'items' => [['id' => $dish->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_delivery_orders_require_a_delivery_address(): void
    {
        $dish = Dish::create([
            'name' => 'Nasi Putih',
            'category' => 'Nasi',
            'price' => 7000,
            'is_available' => true,
        ]);

        $this->from(route('menu'))->post(route('orders.store'), [
            'customer_name' => 'Sari',
            'order_type' => 'delivery',
            'dining_mode' => 'pilih_lauk',
            'items' => [['id' => $dish->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('delivery_address');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_staff_can_update_availability_and_order_status(): void
    {
        $staff = User::create([
            'name' => 'Staf',
            'email' => 'staf@example.test',
            'password' => 'secret-password',
            'role' => 'staff',
        ]);
        $dish = Dish::create([
            'name' => 'Telur Balado',
            'category' => 'Pelengkap',
            'price' => 8000,
            'is_available' => true,
        ]);
        $order = Order::create([
            'order_number' => 'RM-TEST1234',
            'tracking_token' => str_repeat('a', 48),
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'dining_mode' => 'pilih_lauk',
            'payment_method' => 'kasir',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 8000,
        ]);

        $this->actingAs($staff)
            ->patch(route('staff.dishes.toggle', $dish))
            ->assertRedirect();
        $this->assertDatabaseHas('dishes', ['id' => $dish->id, 'is_available' => false]);

        $this->patch(route('staff.orders.update', $order), ['action' => 'accept'])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'accepted']);
    }

    public function test_staff_cannot_access_the_staff_dashboard(): void
    {
        $this->get(route('staff.dashboard'))->assertRedirect(route('staff.login'));
    }

    public function test_production_http_requests_are_redirected_to_https(): void
    {
        $this->app['env'] = 'production';

        $this->get('http://padang.test/')->assertRedirect('https://padang.test');
    }

    public function test_out_of_order_status_transition_is_rejected(): void
    {
        $staff = User::create([
            'name' => 'Staf',
            'email' => 'staf@example.test',
            'password' => 'secret-password',
            'role' => 'staff',
        ]);
        $order = Order::create([
            'order_number' => 'RM-TEST5678',
            'tracking_token' => str_repeat('b', 48),
            'customer_name' => 'Budi',
            'order_type' => 'dine_in',
            'dining_mode' => 'pilih_lauk',
            'payment_method' => 'kasir',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 8000,
        ]);

        $this->actingAs($staff)
            ->patch(route('staff.orders.update', $order), ['action' => 'serve'])
            ->assertStatus(422);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }
}
