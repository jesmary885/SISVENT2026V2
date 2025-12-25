<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{

     use HasFactory;

    // CONSTANTES PARA ESTADOS
    const ESTADO_ABIERTA = 'abierta';
    const ESTADO_CERRADA = 'cerrada';

    protected $guarded = ['id','created_at','updated_at'];

    // RELACIONES
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function aperturasCierre()
    {
        return $this->hasMany(AperturaCierreCaja::class);
    }

    public function aperturaActiva()
    {
        return $this->hasOne(AperturaCierreCaja::class)->abiertas()->latest();
    }

    // SCOPES
    public function scopeAbiertas($query)
    {
        return $query->where('status', self::ESTADO_ABIERTA);
    }

    public function scopeCerradas($query)
    {
        return $query->where('status', self::ESTADO_CERRADA);
    }

    // MÉTODOS ÚTILES
    public function abrir($montoInicialBs = 0, $montoInicialDolares = 0, $userId = null)
    {
        // Actualizar el estado y saldos de la caja
        $this->update([
            'status' => self::ESTADO_ABIERTA,
            'saldo_bolivares' => $montoInicialBs,
            'saldo_dolares' => $montoInicialDolares
        ]);

        \Log::info('Caja actualizada para apertura:', [
            'caja_id' => $this->id,
            'nuevo_status' => $this->status,
            'saldo_bs' => $this->saldo_bolivares,
            'saldo_dolares' => $this->saldo_dolares
        ]);

        // Crear registro de apertura
        $apertura = $this->aperturasCierre()->create([
            'user_id' => $userId ?? auth()->id(),
            'fecha_apertura' => now(),
            'monto_inicial_bs' => $montoInicialBs,
            'monto_inicial_dolares' => $montoInicialDolares
        ]);

        \Log::info('Registro de apertura creado:', ['apertura_id' => $apertura->id]);

        return $apertura;
    }

    public function cerrar($observaciones = null)
    {
        $aperturaActiva = $this->aperturaActiva;

        if ($aperturaActiva) {
            $aperturaActiva->update([
                'fecha_cierre' => now(),
                'monto_final_bs' => $this->saldo_bolivares,
                'monto_final_dolares' => $this->saldo_dolares,
                'observaciones' => $observaciones
            ]);

            $aperturaActiva->calcularTotales()->save();
        }

        return $this->update([
            'status' => self::ESTADO_CERRADA
        ]);
    }

    public function estaAbierta()
    {
        return $this->status === self::ESTADO_ABIERTA;
    }

    public function agregarVenta($venta)
    {
        if ($venta->metodo_pago == 'bs_efec') {
            $this->increment('saldo_bolivares', $venta->total_bolivares);
        } elseif ($venta->metodo_pago == 'dol_efec') {
            $this->increment('saldo_dolares', $venta->total_dolares);
        }
    }

    public function obtenerResumenDia()
    {
        $aperturaActiva = $this->aperturaActiva;
        
        if (!$aperturaActiva) {
            return null;
        }

        $ventasDia = $this->ventas()
            ->whereBetween('created_at', [$aperturaActiva->fecha_apertura, now()])
            ->get();

        return [
            'monto_inicial_bs' => $aperturaActiva->monto_inicial_bs,
            'monto_inicial_dolares' => $aperturaActiva->monto_inicial_dolares,
            'ventas_bs' => $ventasDia->where('metodo_pago', 'bs_efec')->sum('total_bolivares'),
            'ventas_dolares' => $ventasDia->where('metodo_pago', 'dol_efec')->sum('total_dolares'),
            'saldo_actual_bs' => $this->saldo_bolivares,
            'saldo_actual_dolares' => $this->saldo_dolares,
            'total_ventas' => $ventasDia->count()
        ];
    }
}
