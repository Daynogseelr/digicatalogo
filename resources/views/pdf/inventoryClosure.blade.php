<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px }
        .header { text-align:center; margin-bottom:10px }
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding:6px; text-align:left }
        th { background:#eee }
    </style>
</head>
<body>
    <div class="header">
        <h3>Cierre de Inventario</h3>
        <div><strong>Inventario:</strong> {{ $inventory ? $inventory->name : '-' }}</div>
        <div><strong>Fecha:</strong> {{ $date }}</div>
        <div><strong>Generado por:</strong> {{ $generatedBy }}</div>
        <div><strong>Generado el:</strong> {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Empleado</th>
                <th>Cliente</th>
                <th>Código</th>
                <th>Nombre producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r->bill_code }}</td>
                    <td>{{ $r->seller }}</td>
                    <td>{{ $r->client }}</td>
                    <td>{{ $r->product_code }}</td>
                    <td>{{ $r->product_name }}</td>
                    <td style="text-align:right">{{ $r->quantity }}</td>
                    <td style="text-align:right">{{ number_format($r->net_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center">No se encontraron movimientos para la fecha/inventario seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right"><strong>Total</strong></td>
                <td style="text-align:right"><strong>{{ number_format($rows->sum('net_amount'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>