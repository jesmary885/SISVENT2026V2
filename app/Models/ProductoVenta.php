<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoVenta extends Model
{
    use HasFactory;

      protected $guarded = ['id','created_at','updated_at'];

    //Relaion uno a muhos inversa
    public function venta(){
        return $this->belongsTo(Venta::class);
    }

    public function producto_presentacion(){
        return $this->belongsTo(ProductoPresentaciones::class);
    }

    public function presentacion_productos(){
        return $this->hasMany(ProductoPresentaciones::class);
    }
}
