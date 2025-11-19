<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etiqueta</title>
    <style>
        body {
            font-family: sans-serif;
            margin: -35px;
            padding: 0;
            font-size: 9px;
        }

        .label-container {
            width: 4.8cm;
            max-height: 1cm;
            display: flex;
            flex-direction: column;
           /* border: 1px solid red; */
        }
        .product-info {
            height: 0.8cm; /* Altura fija de 1cm */
            overflow: hidden; /* Oculta el texto que se desborda */
            text-overflow: ellipsis; /* Agrega puntos suspensivos (...) si el texto se desborda */
            /* border: 1px solid blue;*/
        }

        .barcode {
            height: 0.2cm; /* Altura fija de 0.5cm */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            /* border: 1px solid green;*/
        }

        img {
            width: 2cm;/* Ancho máximo de 3cm */
            max-height: 0.5cm; /* Alto máximo de 0.5cm */
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php
        $nombre = $product->name;
        $nombreCorto = substr($nombre, 0, 65); // Corta el texto a 44 caracteres

        if (strlen($nombre) > 65) {
            $nombreCorto .= '...'; // Agrega puntos suspensivos si el texto es más largo
        }
    ?>
    <?php for ($i = 0; $i < $quantity; $i++): ?>
        <div class="label-container">
            <div class="product-info">
                {{ $nombreCorto }} 
            </div>
            <div class="barcode">
                <img src="data:image/png;base64,{{ $barcodePNG }}">  <b>COD. {{ $product->code }} </b> 
            </div>
        </div>
        @if ($quantity != 1)
            <br>
            <br>
        @endif
    <?php endfor; ?>
</body>
</html>