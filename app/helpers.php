<?php

use App\Models\CarroCompra;
use App\Models\Producto;
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


function discount($item,$cant){

    // $producto = Producto::find($item->id);

       $busqueda_compra = ProductoPresentaciones::where('id',$item->id)->first();

            if($busqueda_compra->nombre == 'unidad' || $busqueda_compra->nombre == 'kg'){

                $producto = Producto::where('id', $busqueda_compra->producto_id)->first();
                $cantidad_new = $producto->stock_base - $cant;

                $producto->update([
                    'stock_base' => $cantidad_new
                ]);

            }else{

                $cantidad_new_caja = $busqueda_compra->cantidad_de_cajas - $cant;

                $busqueda_compra->update([
                    'cantidad_de_cajas' => $cantidad_new_caja
                ]);

                $cantidad_total_unidad = $busqueda_compra->factor_base * $cant;

                $producto = Producto::where('id', $busqueda_compra->producto_id)->first();
                $cantidad_new = $producto->stock_base - $cantidad_total_unidad;

                $producto->update([
                    'stock_base' => $cantidad_new
                ]);

            }
}

function increase($item,$cant){

     $busqueda_compra = ProductoPresentaciones::where('id',$item)->first();

            if($busqueda_compra->nombre == 'unidad' || $busqueda_compra->nombre == 'kg'){

                $producto = Producto::where('id', $busqueda_compra->producto_id)->first();
                $cantidad_new = $producto->stock_base + $cant;

                $producto->update([
                    'stock_base' => $cantidad_new
                ]);

            }else{

                $cantidad_new_caja = $busqueda_compra->cantidad_de_cajas + $cant;

                $busqueda_compra->update([
                    'cantidad_de_cajas' => $cantidad_new_caja
                ]);

                $cantidad_total_unidad = $busqueda_compra->factor_base * $cant;

                $producto = Producto::where('id', $busqueda_compra->producto_id)->first();
                $cantidad_new = $producto->stock_base + $cantidad_total_unidad;

                $producto->update([
                    'stock_base' => $cantidad_new
                ]);

            }

    

}