<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

     protected $guarded = ['id','created_at','updated_at'];


     
    public function user(){
        return $this->belongsTo(User::class);
    }

     //Relacion uno a muchos
   
     public function ventas(){
        return $this->hasMany(Venta::class);
    }
}
