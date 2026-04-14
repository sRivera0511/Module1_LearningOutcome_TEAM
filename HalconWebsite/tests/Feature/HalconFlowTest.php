<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HalconFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_are_redirected_from_admin_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->get(route('orders.archived'))->assertRedirect(route('login'));
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_public_tracking_shows_spanish_status_and_delivery_evidence(): void
    {
        $order = $this->createOrder([
            'status' => Order::STATUS_ENTREGADO,
            'delivery_photo' => 'evidences/entrega.jpg',
        ]);

        $this->post(route('track'), [
            'customer_number' => $order->customer_number,
            'invoice_number' => $order->invoice_number,
        ])
            ->assertOk()
            ->assertSee('Pedido #' . $order->invoice_number)
            ->assertSee(Order::STATUS_ENTREGADO)
            ->assertSee('storage/evidences/entrega.jpg');
    }

    public function test_public_tracking_shows_error_when_order_is_missing(): void
    {
        $this->followingRedirects()
            ->post(route('track'), [
                'customer_number' => 999999,
                'invoice_number' => 123456,
            ])
            ->assertSee('No se encontro ningun pedido con esos datos.');
    }

    public function test_sales_can_create_archive_and_restore_orders_but_cannot_manage_users(): void
    {
        $sales = $this->createUser('Sales');

        $this->actingAs($sales)
            ->post(route('orders.store'), [
                'invoice_number' => 12001,
                'customer_name' => 'Cliente Ventas',
                'customer_number' => 55001,
                'fiscal_data' => 'RFC DEMO 123',
                'delivery_address' => 'Calle Principal 123',
                'notes' => 'Entrega prioritaria',
            ])
            ->assertRedirect();

        $order = Order::query()->where('invoice_number', 12001)->firstOrFail();

        $this->assertSame(Order::STATUS_PEDIDO_RECIBIDO, $order->status);
        $this->assertSame($sales->id, $order->user_id);

        $this->actingAs($sales)
            ->delete(route('orders.destroy', $order))
            ->assertRedirect();

        $this->assertSoftDeleted('orders', ['id' => $order->id]);

        $this->actingAs($sales)
            ->get(route('orders.archived'))
            ->assertOk()
            ->assertSee('Archivo de pedidos');

        $this->actingAs($sales)
            ->post(route('orders.restore', $order->id))
            ->assertRedirect();

        $this->assertNotSoftDeleted('orders', ['id' => $order->id]);

        $this->actingAs($sales)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_route_role_can_update_order_with_photo_but_cannot_archive_or_restore(): void
    {
        Storage::fake('public');

        $routeUser = $this->createUser('Route');
        $order = $this->createOrder();

        $this->actingAs($routeUser)
            ->put(route('orders.update', $order), [
                'status' => Order::STATUS_EN_RUTA,
                'photo' => UploadedFile::fake()->create('ruta.jpg', 120, 'image/jpeg'),
            ])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(Order::STATUS_EN_RUTA, $order->status);
        $this->assertNotNull($order->route_photo);
        Storage::disk('public')->assertExists($order->route_photo);

        $this->actingAs($routeUser)
            ->delete(route('orders.destroy', $order))
            ->assertForbidden();

        $this->actingAs($routeUser)
            ->get(route('orders.archived'))
            ->assertForbidden();

        $this->actingAs($routeUser)
            ->post(route('orders.restore', $order->id))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_pages_and_create_users_with_username(): void
    {
        $admin = $this->createUser('Admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('PANEL DE');

        $this->actingAs($admin)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('GESTION DE');

        $this->actingAs($admin)
            ->get(route('orders.archived'))
            ->assertOk()
            ->assertSee('Archivo de pedidos');

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Registrar usuario');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Nuevo Usuario',
                'username' => 'nuevo.usuario',
                'email' => 'nuevo@example.com',
                'password' => 'secreto123',
                'role' => 'Warehouse',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'nuevo.usuario',
            'email' => 'nuevo@example.com',
            'role' => 'Warehouse',
            'active' => 1,
        ]);
    }

    public function test_status_translation_migration_updates_legacy_values(): void
    {
        $order = $this->createOrder([
            'status' => Order::STATUS_PEDIDO_RECIBIDO,
        ]);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = 1');
        }

        DB::table('orders')
            ->where('id', $order->id)
            ->update(['status' => 'Ordered']);

        $migration = require database_path('migrations/2026_04_13_000001_translate_order_statuses_to_spanish.php');
        $migration->up();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PEDIDO_RECIBIDO,
        ]);
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'active' => true,
        ]);
    }

    private function createOrder(array $overrides = []): Order
    {
        $salesUser = $overrides['user'] ?? $this->createUser('Sales');
        unset($overrides['user']);

        return Order::create(array_merge([
            'user_id' => $salesUser->id,
            'invoice_number' => fake()->unique()->numberBetween(1000, 9999),
            'customer_name' => 'Cliente Demo',
            'customer_number' => fake()->numberBetween(5000, 5999),
            'fiscal_data' => 'RFC DEMO 123',
            'delivery_address' => 'Avenida Siempre Viva 742',
            'notes' => 'Sin observaciones',
            'status' => Order::STATUS_PEDIDO_RECIBIDO,
            'route_photo' => null,
            'delivery_photo' => null,
        ], $overrides));
    }
}
