<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    protected $fillable = [
        'venta_id',
        'cliente_id',
        'monto_dolares',
        'monto_bolivares',
        'fecha_limite',
        'comentario',
        'estado',
        'registrada_por',
        'fecha_pago',
        'comentario_pago'
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'fecha_pago' => 'datetime',
        'monto_dolares' => 'decimal:2',
        'monto_bolivares' => 'decimal:2',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }

    // Scope para deudas pendientes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    // Scope para deudas vencidas
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_limite', '<', now());
    }

    // Método para marcar como pagada
    public function marcarComoPagada($comentario = null)
    {
        $this->update([
            'estado' => 'pagada',
            'fecha_pago' => now(),
            'comentario_pago' => $comentario
        ]);
    }
}
