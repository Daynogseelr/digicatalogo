<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 10px;
            margin-left: 20px;
        }
        body {
            font-family: "Times New Roman", Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            
        }

        th, td {
            
            padding: 2px;
            text-align: left;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .nota {
            font-size: 8px;
        }
        .end{
            text-align: right;
        }
        .puntos{
            padding: 0px;
        }
        .size{
            font-size: 14px;
        }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{$service->clientName}} {{$service->clientLast_name}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>CI/RIF:</b> {{$service->nationality}}-{{$service->ci}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Dir:</b> {{$service->direction}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Tlf:</b> {{$service->phone}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Técnico:</b> {{$service->technicianName}} {{$service->technicianLast_name}}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="center"><b>TICKET</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>Nro:</b></td>
            <td colspan="2" class="end">{{$service->ticker}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$service->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$service->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4"><b>{{$service->category}} {{$service->brand}} {{$service->model}} {{$service->serial}}</b></td>
        </tr>
        <tr>
            <td colspan="4">{{$service->description}}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4"  class="center">GRACIAS POR SU PREFERENCIA</td>
        </tr>
        <tr>
            <td colspan="4" class="center"> <img src="{{ $qrCode }}" alt="Código QR"></td>
        </tr>
        <tr>
            <td colspan="2"><b>Recibido por:</b></td>
            <td colspan="2" class="end"> {{$service->sellersName}} {{$service->sellersLast_name}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="2"><b>Solucción:</b></td>
            <td colspan="2" class="end">{{$service->solution}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Estatus:</b></td>
            <td colspan="2" class="end">{{$service->status}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        @foreach ($serviceDetails as $serviceDetail)
        <tr>
            <td colspan="4"><b>{{ $serviceDetail['quantity'] }} X  {{ $serviceDetail['price'] }}</b></td>
        </tr>
        <tr>
            <td colspan="4">{{ $serviceDetail['procedure'] ?? $serviceDetail['product_name'] }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="end"><b></b></td>
            <td class="end">{{$totalPrice}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
    </table>
</body>
</html>