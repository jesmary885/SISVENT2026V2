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
         return $this->hasOne(AperturaCierreCaja::class)
        ->whereNull('fecha_cierre') // Solo aperturas sin cerrar
        ->latest();
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
        // Actualizar el estado y saldos de la caja (REINICIAR saldos)
        $this->update([
            'status' => self::ESTADO_ABIERTA,
            'saldo_bolivares' => $montoInicialBs, // Empezar con el monto inicial
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

    // En el modelo Caja.php, método cerrar():
    public function cerrar($observaciones = null)
    {
        $aperturaActiva = $this->aperturaActiva;

        if ($aperturaActiva) {
            // Convertir fecha_apertura a Carbon si es string
            $fechaApertura = $aperturaActiva->fecha_apertura;
            if (is_string($fechaApertura)) {
                $fechaApertura = \Carbon\Carbon::parse($fechaApertura);
            }
            
            // Calcular el monto final basado en el monto inicial + ventas desde la apertura
            $ventasDesdeApertura = $this->ventas()
                ->where('created_at', '>=', $fechaApertura)
                ->get();
                
            $montoFinalBs = $aperturaActiva->monto_inicial_bs;
            $montoFinalDolares = $aperturaActiva->monto_inicial_dolares;
            
            foreach ($ventasDesdeApertura as $venta) {
                if ($venta->metodo_pago == 'bs_efec') {
                    $montoFinalBs += $venta->monto_pagado_bolivares;
                } elseif ($venta->metodo_pago == 'dol_efec') {
                    $montoFinalDolares += $venta->monto_pagado_dolares;
                }
            }

            $aperturaActiva->update([
                'fecha_cierre' => now(),
                'monto_final_bs' => $montoFinalBs,
                'monto_final_dolares' => $montoFinalDolares,
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

    // En el modelo Caja.php:
    public function agregarVenta($venta){
        // Solo incrementar el saldo si es pago en EFECTIVO
        if ($venta->metodo_pago == 'bs_efec') {
            $this->increment('saldo_bolivares', $venta->monto_pagado_bolivares);
            \Log::info('Agregada venta en efectivo Bs a caja:', [
                'caja_id' => $this->id,
                'venta_id' => $venta->id,
                'monto_agregado' => $venta->monto_pagado_bolivares,
                'nuevo_saldo_bs' => $this->saldo_bolivares
            ]);
        } elseif ($venta->metodo_pago == 'dol_efec') {
            $this->increment('saldo_dolares', $venta->monto_pagado_dolares);
            \Log::info('Agregada venta en efectivo $ a caja:', [
                'caja_id' => $this->id,
                'venta_id' => $venta->id,
                'monto_agregado' => $venta->monto_pagado_dolares,
                'nuevo_saldo_dolares' => $this->saldo_dolares
            ]);
        } elseif ($venta->metodo_pago == 'usdt') {
            $this->increment('saldo_dolares', $venta->monto_pagado_dolares);
            \Log::info('Agregada venta en USDT a caja:', [
                'caja_id' => $this->id,
                'venta_id' => $venta->id,
                'monto_agregado' => $venta->monto_pagado_dolares,
                'nuevo_saldo_dolares' => $this->saldo_dolares
            ]);
        } else {
            // Para otros métodos (tarjetas, transferencias) NO se incrementa el saldo
            \Log::info('Venta registrada pero NO se agrega a saldo (método no efectivo):', [
                'caja_id' => $this->id,
                'venta_id' => $venta->id,
                'metodo_pago' => $venta->metodo_pago,
                'monto_bs' => $venta->monto_pagado_bolivares,
                'monto_dolares' => $venta->monto_pagado_dolares
            ]);
        }
    }

        // En el modelo Caja.php, modifica el método obtenerResumenDia:
   public function obtenerResumenDia()
    {
        $aperturaActiva = $this->aperturaActiva;
        
        if (!$aperturaActiva) {
            return [
                'monto_inicial_bs' => 0,
                'monto_inicial_dolares' => 0,
                'ventas_bs' => 0,
                'ventas_dolares' => 0,
                'otros_metodos_bs' => 0,
                'otros_metodos_dolares' => 0,
                'detalle_metodos_pago' => [],
                'saldo_actual_bs' => $this->saldo_bolivares,
                'saldo_actual_dolares' => $this->saldo_dolares,
                'total_ventas' => 0
            ];
        }

        // Convertir fecha_apertura a Carbon si es necesario
        $fechaApertura = $aperturaActiva->fecha_apertura;
        if (is_string($fechaApertura)) {
            $fechaApertura = \Carbon\Carbon::parse($fechaApertura);
        }

        // Obtener ventas SOLO desde esta apertura específica
        $ventasDia = $this->ventas()
            ->where('created_at', '>=', $fechaApertura)
            ->get();

        // Inicializar arrays para detalle de métodos de pago
        $detalleMetodosPago = [];
        
        // Definir categorías de métodos de pago
        $metodosBs = ['bs_efec'];
        $metodosDolares = ['dol_efec', 'usdt'];
        $otrosMetodos = ['debito', 'pago_movil', 'transferencia', 'biopago', 'zelle'];

        // Calcular ventas en efectivo Bs
        $ventasBsEfectivo = $ventasDia->where('metodo_pago', 'bs_efec')->sum('monto_pagado_bolivares');
        
        // Calcular ventas en efectivo $
        $ventasDolaresEfectivo = $ventasDia->where('metodo_pago', 'dol_efec')->sum('monto_pagado_dolares');
        
        // Calcular USDT
        $ventasUsdt = $ventasDia->where('metodo_pago', 'usdt')->sum('monto_pagado_dolares');
        
        // Calcular otros métodos de pago (tarjetas, transferencias, etc.)
        $otrosMetodosBs = $ventasDia->whereIn('metodo_pago', $otrosMetodos)->sum('total_bolivares');
        $otrosMetodosDolares = $ventasDia->whereIn('metodo_pago', $otrosMetodos)->sum('total_dolares');
        
        // Calcular montos pagados en otros métodos
        $otrosMetodosBsPagado = $ventasDia->whereIn('metodo_pago', $otrosMetodos)->sum('monto_pagado_bolivares');
        $otrosMetodosDolaresPagado = $ventasDia->whereIn('metodo_pago', $otrosMetodos)->sum('monto_pagado_dolares');

        // Calcular por método específico
        foreach ($ventasDia as $venta) {
            $metodo = $venta->metodo_pago;
            
            if (!isset($detalleMetodosPago[$metodo])) {
                $detalleMetodosPago[$metodo] = [
                    'total_bs' => 0,
                    'total_dolares' => 0,
                    'cantidad' => 0,
                    'pagado_bs' => 0,
                    'pagado_dolares' => 0
                ];
            }
            
            $detalleMetodosPago[$metodo]['total_bs'] += $venta->total_bolivares;
            $detalleMetodosPago[$metodo]['total_dolares'] += $venta->total_dolares;
            $detalleMetodosPago[$metodo]['pagado_bs'] += $venta->monto_pagado_bolivares;
            $detalleMetodosPago[$metodo]['pagado_dolares'] += $venta->monto_pagado_dolares;
            $detalleMetodosPago[$metodo]['cantidad']++;
        }

        // Traducir nombres de métodos
        $nombresMetodos = [
            'bs_efec' => 'Efectivo Bs',
            'dol_efec' => 'Efectivo $',
            'usdt' => 'USDT',
            'debito' => 'Tarjeta Débito',
            'pago_movil' => 'Pago Móvil',
            'transferencia' => 'Transferencia',
            'biopago' => 'Biopago',
            'zelle' => 'Zelle',
            'pausada' => 'Venta Pausada'
        ];

        // Formatear detalle de métodos
        $detalleFormateado = [];
        foreach ($detalleMetodosPago as $metodo => $datos) {
            $detalleFormateado[$nombresMetodos[$metodo] ?? $metodo] = [
                'total_bs' => $datos['total_bs'],
                'total_dolares' => $datos['total_dolares'],
                'pagado_bs' => $datos['pagado_bs'],
                'pagado_dolares' => $datos['pagado_dolares'],
                'cantidad' => $datos['cantidad']
            ];
        }

        // EL SALDO ACTUAL DEBE SER: Monto inicial + Ventas en efectivo
        // Los otros métodos no suman al saldo de efectivo
        $saldoActualBs = $aperturaActiva->monto_inicial_bs + $ventasBsEfectivo;
        $saldoActualDolares = $aperturaActiva->monto_inicial_dolares + $ventasDolaresEfectivo + $ventasUsdt;

        \Log::info('Resumen calculado:', [
            'monto_inicial_bs' => $aperturaActiva->monto_inicial_bs,
            'monto_inicial_dolares' => $aperturaActiva->monto_inicial_dolares,
            'ventas_bs_efectivo' => $ventasBsEfectivo,
            'ventas_dolares_efectivo' => $ventasDolaresEfectivo,
            'ventas_usdt' => $ventasUsdt,
            'otros_metodos_bs' => $otrosMetodosBs,
            'otros_metodos_dolares' => $otrosMetodosDolares,
            'saldo_calculado_bs' => $saldoActualBs,
            'saldo_calculado_dolares' => $saldoActualDolares,
            'detalle_metodos' => $detalleFormateado
        ]);

        return [
            'monto_inicial_bs' => $aperturaActiva->monto_inicial_bs ?? 0,
            'monto_inicial_dolares' => $aperturaActiva->monto_inicial_dolares ?? 0,
            'ventas_bs' => $ventasBsEfectivo,
            'ventas_dolares' => $ventasDolaresEfectivo + $ventasUsdt,
            'ventas_usdt' => $ventasUsdt,
            'otros_metodos_bs' => $otrosMetodosBs,
            'otros_metodos_dolares' => $otrosMetodosDolares,
            'detalle_metodos_pago' => $detalleFormateado,
            'saldo_actual_bs' => $this->saldo_bolivares, // Esto viene de la base de datos
            'saldo_actual_dolares' => $this->saldo_dolares,
            'saldo_calculado_bs' => $saldoActualBs, // Esto es lo que debería ser
            'saldo_calculado_dolares' => $saldoActualDolares,
            'total_ventas' => $ventasDia->count()
        ];
    }
}
