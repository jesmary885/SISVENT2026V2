<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];

        // CONSTANTES PARA ESTADOS
     const ESTADO_ACTIVA = 'activa';
     const ESTADO_FINALIZADA = 'finalizada';
     const ESTADO_PAUSADA = 'pausada';

    //Relaion uno a muhos inversa

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function deuda()
    {
        return $this->hasOne(Deuda::class);
    }

     //Relacion uno a muchos
   
    public function producto_ventas(){
        return $this->hasMany(ProductoVenta::class);
    }

    public function pagoVentas(){
        return $this->hasMany(PagoVenta::class);
    }

       // NUEVA RELACIÓN CON CARRO COMPRA
     public function carroCompra()
     {
         return $this->hasMany(CarroCompra::class);
     }

     // SCOPES ÚTILES
     public function scopeActivas($query)
     {
         return $query->where('estado', self::ESTADO_ACTIVA);
     }

     public function scopeFinalizadas($query)
     {
         return $query->where('estado', self::ESTADO_FINALIZADA);
     }

     public function scopePausadas($query)
     {
         return $query->where('estado', self::ESTADO_PAUSADA);
     }


}
