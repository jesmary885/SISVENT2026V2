<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];

    public function producto_ventas(){
        return $this->hasMany(ProductoVenta::class);
    }


    public function compras(){
        return $this->hasMany(Compra::class);
    }

     public function carro_compras(){
        return $this->hasMany(CarroCompra::class);
    }

    public function marca(){
        return $this->belongsTo(Marca::class);
    }
}
