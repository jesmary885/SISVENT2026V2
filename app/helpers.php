<?php

use App\Models\CarroCompra;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use App\Models\ProductoPresentaciones;

 function quantity($registro){

    $registro = ProductoPresentaciones::where('id',$registro->id)->first();

    if($registro->nombre == 'caja') $quantity = $registro->cantidad_de_cajas;
    else{

        $producto = Producto::where('id',$registro->producto_id)->first();
        $quantity = $producto->stock_base;

    }

    return $quantity;
 }

function qty_added($registro){
 
    
    $item = CarroCompra::where('producto_presentacion_id', $registro->producto_id)->first();

    if($item){
        return $item->cantidad;
    }else{
        return 0;
    }

}


function qty_available($registro){

    //$p = Producto::find($producto_id->id);

    $registro = ProductoPresentaciones::where('id',$registro->id)->first();

    if($registro->nombre == 'caja') $quantity = $registro->cantidad_de_cajas;
    else{

        $producto = Producto::where('id',$registro->producto_id)->first();
        $quantity = $producto->stock_base;

    }


    return $quantity - qty_added($registro);
}


function discount($item, $cant)
{
    $presentacion = ProductoPresentaciones::find($item->id);

    $producto = Producto::find($presentacion->producto_id);

    /*
    |--------------------------------------------------------------------------
    | 1️⃣ DESCONTAR EN BASE A PRESENTACIÓN
    |--------------------------------------------------------------------------
    */

    if ($presentacion->nombre === 'unidad' || $presentacion->nombre === 'kg') {

        // descuento directo en unidades
        $producto->stock_base -= $cant;

    } else {

        // descuento en cajas
        $unidadesADescontar = $presentacion->factor_base * $cant;
        $producto->stock_base -= $unidadesADescontar;
    }

    // evitar negativos
    if ($producto->stock_base < 0) {
        $producto->stock_base = 0;
    }

    $producto->save();

    /*
    |--------------------------------------------------------------------------
    | 2️⃣ RECALCULAR CAJAS AUTOMÁTICAMENTE
    |--------------------------------------------------------------------------
    */

    $presentacionCaja = ProductoPresentaciones::where('producto_id', $producto->id)
        ->where('nombre', 'caja')
        ->first();

    if ($presentacionCaja) {

        $factor = $presentacionCaja->factor_base;

        $cajasCompletas = floor($producto->stock_base / $factor);

        $presentacionCaja->update([
            'cantidad_de_cajas' => $cajasCompletas
        ]);
    }
}





function increase($presentacionId, $cant)
{
    DB::transaction(function () use ($presentacionId, $cant) {

        $presentacion = ProductoPresentaciones::findOrFail($presentacionId);

        $producto = Producto::findOrFail($presentacion->producto_id);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ CALCULAR UNIDADES A SUMAR
        |--------------------------------------------------------------------------
        */

        if ($presentacion->nombre === 'unidad' || $presentacion->nombre === 'kg') {

            $unidadesASumar = $cant;

        } else {

            $unidadesASumar = $presentacion->factor_base * $cant;
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ SUMAR A STOCK BASE
        |--------------------------------------------------------------------------
        */

        $producto->stock_base += $unidadesASumar;
        $producto->save();

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ RECALCULAR CAJAS
        |--------------------------------------------------------------------------
        */

        $presentacionCaja = ProductoPresentaciones::where('producto_id', $producto->id)
            ->where('nombre', 'caja')
            ->first();

        if ($presentacionCaja) {

            $factor = $presentacionCaja->factor_base;

            $cajasCompletas = floor($producto->stock_base / $factor);

            $presentacionCaja->update([
                'cantidad_de_cajas' => $cajasCompletas
            ]);
        }

    });
}