<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralController extends Controller
{

    public function home(){

        return view('home');
    }

    public function caja(){

        return view('cajas.index');
    }


    public function cambiarCredenciales(){

        return view('auth.cambiar-credenciales');
    }

    
    public function inventario_index(){

        return view('inventario.index');
    }


    public function reportes(){

        return view('reportes.index');
    }


    public function ventas_index(){

        return view('ventas.index');
    }

    public function Punto_venta(){

        return view('Punto_venta.index');
    }

    public function configuracion(){

        return view('configuracion.index');
    }

    public function compras_index(){

        return view('compras.index');
    }

    public function proveedores(){

        return view('proveedores.index');
    }

    public function cajas_index(){

        return view('cajas.index');
    }

     public function administracion(){

        return view('administracion.index');
    }




}
