<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Garantia</title>
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
            <td colspan="4" class="titulo">{{$guarantee->companyNationality}}-{{$guarantee->companyCi}}</td>
        </tr>
        <tr>
            <td colspan="4" class="titulo">{{$guarantee->companyName}}</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$guarantee->companyDirection}}</td>
        </tr>
        <tr>
            <td colspan="4"  class="center">{{$guarantee->companyCity}} EDO. {{$guarantee->companyState}} ZONA POSTAL {{$guarantee->companyPostal_zone}}</td>
        </tr> --}}
        <tr>
            <td colspan="4" class="center"><b>GARANTIA</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$guarantee->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$guarantee->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4">{{$guarantee->code}} {{$guarantee->name}} - {{$guarantee->serial}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4"><b>DESCRIPCIÓN:</b> {{$guarantee->description}}</td>
        </tr>
        <br>
        <tr>
            <td colspan="2"><b>PRODUCTO PARA:</b> </td>
            <td colspan="2" class="end"><b>{{$guarantee->status}}</b></td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <br><br><br>
    </table>
</body>
</html>