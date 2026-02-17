<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarroCompra extends Model
{
    use HasFactory;

    protected $guarded = ['id','created_at','updated_at'];


    public function producto_presentacion(){
        return $this->belongsTo(ProductoPresentaciones::class);
    }

     public function user(){
        return $this->belongsTo(User::class);
    }
}
