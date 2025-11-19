<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Factura</title>
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
         {{--<tr>
            <td colspan="4" class="titulo">{{$bill->companyNationality}}-{{$bill->companyCi}}</td>
        </tr>
        <tr>
            <td colspan="4" class="titulo">{{$bill->companyName}}</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$bill->companyDirection}}</td>
        </tr>
        <tr>
            <td colspan="4"  class="center">{{$bill->companyCity}} EDO. {{$bill->companyState}} ZONA POSTAL {{$bill->companyPostal_zone}}</td>
        </tr> --}}
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{$bill->clientName}} {{$bill->clientLast_name}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>CI/RIF:</b> {{$bill->nationality}}-{{$bill->ci}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Dir:</b> {{$bill->direction}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Tlf:</b> {{$bill->phone}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Vendedor:</b> {{$bill->sellerName}} {{$bill->sellerLast_name}}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            @if ($bill->type == 'PRESUPUESTO')
                <td colspan="4" class="center"><b>PRESUPUESTO</b></td>
            @else
                 {{-- <td colspan="4" class="center"><b>CONTROL</b></td>--}}
            @endif
        </tr>
        <tr>
            <td colspan="2"><b>Nro de control:</b></td>
            <td colspan="2" class="end">{{$bill->code}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$bill->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$bill->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        @foreach ($bill_details as $bill_detail)
        <tr>
            <td colspan="2"><b>{{$bill_detail->quantity}} X {{$bill->abbr_official}} {{number_format($bill_detail->priceU * $bill->rate_official,2)}} </b></td>
            <td colspan="2" class="end"><b>{{$bill->abbr_official}} {{number_format($bill_detail->net_amount * $bill->rate_official,2)}}</b></td>
        </tr>
        <tr>
            <td colspan="4">{{$bill_detail->name}}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr>
            <td >BI G16%</td>
            {{--<td >{{$bi}}</td>--}}
            <td >{{number_format($bill->total_amount * $bill->rate_official,2)}}</td>
            <td class="end">I.V.A.G16%</td>
            {{--<td class="end">{{$iva}}</td>--}}
            <td class="end">Exento</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr>
            <td colspan="2">DESCUENTO</td>
            <td colspan="2" class="end">{{number_format($bill->discount * $bill->rate_official,2)}} {{$bill->abbr_official}}</td>
        </tr>
        @if ($bill->id_currency_principal == $bill->id_currency_official)
            <tr>
                <td colspan="2" class="size"><b>TOTAL</b></td>
                <td colspan="2" class="end size"><b>{{number_format($bill->total_amount * $bill->rate_official,2)}}</b></td>
            </tr>
        @else
             <tr>
                <td colspan="2" class="size"><b>TOTAL</b></td>
                <td colspan="2" class="end size"><b>{{number_format($bill->net_amount * $bill->rate_official,2)}} {{$bill->abbr_official}}</b></td>
            </tr>
             <tr>
                <td colspan="2"><b>REF:</b></td>
                <td colspan="2" class="end"><b>{{number_format($bill->net_amount,2)}} {{$bill->abbr_principal}}</b></td>
            </tr>
        @endif
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
        </tr>
    </table>
</body>
</html>