<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPresentaciones extends Model
{
    use HasFactory;

      protected $guarded = ['id','created_at','updated_at'];

    //Relaion uno a muhos inversa
 
    public function producto(){
        return $this->belongsTo(Producto::class);
    }

    public function carro_compras(){
        return $this->hasMany(CarroCompra::class);
    }

    public function producto_ventas(){
        return $this->hasMany(ProductoVenta::class);
    }
}
