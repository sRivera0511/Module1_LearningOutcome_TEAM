<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('invoice_number')->unique();
            $table->string('customer_name');
            $table->integer('customer_number');
            $table->text('fiscal_data');
            $table->text('delivery_address');
            $table->text('notes')->nullable();
            $table->enum('status', ['Pedido recibido', 'En proceso', 'En ruta', 'Entregado'])->default('Pedido recibido');
            $table->string('route_photo')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
