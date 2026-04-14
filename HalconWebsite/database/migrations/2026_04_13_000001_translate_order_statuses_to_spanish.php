<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->translateStatusesToSpanish();
    }

    public function down(): void
    {
        $this->translateStatusesToEnglish();
    }

    private function translateStatusesToSpanish(): void
    {
        $this->updateStatuses([
            'Ordered' => 'Pedido recibido',
            'In process' => 'En proceso',
            'In route' => 'En ruta',
            'Delivered' => 'Entregado',
        ]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('Pedido recibido', 'En proceso', 'En ruta', 'Entregado') NOT NULL DEFAULT 'Pedido recibido'");
        }
    }

    private function translateStatusesToEnglish(): void
    {
        $this->updateStatuses([
            'Pedido recibido' => 'Ordered',
            'En proceso' => 'In process',
            'En ruta' => 'In route',
            'Entregado' => 'Delivered',
        ]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('Ordered', 'In process', 'In route', 'Delivered') NOT NULL DEFAULT 'Ordered'");
        }
    }

    private function updateStatuses(array $translations): void
    {
        foreach ($translations as $from => $to) {
            DB::table('orders')->where('status', $from)->update(['status' => $to]);
        }
    }
};
