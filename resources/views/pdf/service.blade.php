@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Servicios</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; } /* Slightly smaller font for more data */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; } /* Slightly less padding */
        th { background-color: #f2f2f2; }
        .total-row td { font-weight: bold; }
        .report-header { text-align: center; margin-bottom: 15px; }
        .report-header h1 { font-size: 16px; margin-bottom: 5px; }
        .report-details { margin-bottom: 15px; }
        .report-details p { margin: 1px 0; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Reporte de Servicios</h1>
        <p>Generado en: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="report-details">
        <p><strong>Rango de Fechas:</strong> {{ $dateRangeFilterName }}</p>
        <p><strong>Técnico:</strong> {{ $technicianFilterName }}</p>
        @if(!empty($searchQuery))
        <p><strong>Búsqueda:</strong> "{{ $searchQuery }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Técnico</th>
                <th>Estado</th>
                <th>Precio ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td>{{ $service->code ?? 'N/A' }}</td>
                    <td>{{ (new Carbon($service->created_at))->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $service->client_name ?? 'N/A' }}</td>
                    <td>{{ $service->technician_name ?? 'N/A' }}</td>
                    <td>{{ $service->status ?? 'N/A' }}</td>
                    <td>{{ number_format($service->price ?? 0, 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No se encontraron servicios para los filtros seleccionados.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL GENERAL:</td>
                <td>{{ number_format($grandTotal ?? 0, 2, '.', '') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>