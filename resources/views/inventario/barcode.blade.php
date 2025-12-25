<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Generate Barcode in Laravel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
     html, body { 
  height:100%; 
  margin-bottom:30px;

}
           
            }
            #columna1 {
            margin:0;
            padding:0;
            position:absolute;
          
            width:340px;
            margin-top:10px;
            margin-left:10px;
            margin-bottom: 10px;
         
       
            }
            #columna2 {
            margin:0;
            padding:0;
            margin-left:360px;
            margin-right:10px;
            margin-top:10px;
            margin-bottom: 10px;
         
    
            }    </style>
</head>

<body>



@for ($i = 1; $i <= $cantidad; $i++)

@php
    if (($i % 2) == 0) $clase = "columna2";
    else $clase = "columna1";
@endphp


<div id={{$clase}}>
    <div class="barcodeCode">
        <div>{!! DNS1D::getBarcodeHTML($cod_barra, 'C128') !!}</div>
    </div>
    <div>
        {{$cod_barra}}
    </div>
</div>

@endfor
</body>
</html>