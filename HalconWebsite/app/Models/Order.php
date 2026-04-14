<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PEDIDO_RECIBIDO = 'Pedido recibido';
    public const STATUS_EN_PROCESO = 'En proceso';
    public const STATUS_EN_RUTA = 'En ruta';
    public const STATUS_ENTREGADO = 'Entregado';

    public const STATUSES = [
        self::STATUS_PEDIDO_RECIBIDO,
        self::STATUS_EN_PROCESO,
        self::STATUS_EN_RUTA,
        self::STATUS_ENTREGADO,
    ];

    protected $fillable = [
        'user_id',
        'invoice_number',
        'customer_name',
        'customer_number',
        'fiscal_data',
        'delivery_address',
        'notes',
        'status',
        'route_photo',
        'delivery_photo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PEDIDO_RECIBIDO => 'badge-ordered',
            self::STATUS_EN_PROCESO => 'badge-in-process',
            self::STATUS_EN_RUTA => 'badge-in-route',
            self::STATUS_ENTREGADO => 'badge-delivered',
            default => 'badge-default',
        };
    }

    public static function evidenceStatuses(): array
    {
        return [
            self::STATUS_EN_RUTA,
            self::STATUS_ENTREGADO,
        ];
    }
}
