<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperturaCierreCaja extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];

    // RELACIONES
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SCOPES ÚTILES
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_apertura', today());
    }

    public function scopeAbiertas($query)
    {
        return $query->whereNull('fecha_cierre');
    }

    // MÉTODOS
    public function estaAbierta()
    {
        return is_null($this->fecha_cierre);
    }

    public function calcularTotales()
    {
        $ventas = $this->caja->ventas()
            ->whereBetween('created_at', [$this->fecha_apertura, now()])
            ->get();

        $this->ventas_bs = $ventas->sum('total_bolivares');
        $this->ventas_dolares = $ventas->sum('total_dolares');
        
        return $this;
    }
}
