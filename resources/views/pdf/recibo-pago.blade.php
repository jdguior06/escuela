<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de pago</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #1f2937; }
        .encabezado { display: table; width: 100%; margin-bottom: 20px; }
        .encabezado .logo { display: table-cell; width: 70pt; vertical-align: middle; }
        .encabezado .logo img { width: 60pt; }
        .encabezado .empresa { display: table-cell; vertical-align: middle; padding-left: 10pt; }
        .empresa h1 { margin: 0; font-size: 16px; }
        .empresa p { margin: 2px 0 0; font-size: 11px; color: #6b7280; }
        h2 { font-size: 15px; border-bottom: 1px solid #d1d5db; padding-bottom: 6px; }
        table.datos { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.datos td { padding: 6px 4px; border-bottom: 1px solid #e5e7eb; }
        table.datos td.label { font-weight: bold; width: 180px; color: #374151; }
        .monto { font-size: 16px; font-weight: bold; }
        .nota { margin-top: 24px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="encabezado">
        <div class="logo">
            <img src="{{ resource_path('images/logo-club.png') }}" alt="Logo">
        </div>
        <div class="empresa">
            <h1>{{ config('app.name') }}</h1>
            <p>Comprobante de pago</p>
        </div>
    </div>

    <h2>Recibo N.° {{ $pago->nro_pedido ?? $pago->id }}</h2>

    <table class="datos">
        <tr><td class="label">Estudiante</td><td>{{ $pago->inscripcion->estudiante->nombre }} {{ $pago->inscripcion->estudiante->apellido }}</td></tr>
        <tr><td class="label">Curso</td><td>{{ $pago->inscripcion->curso->tipoCurso->nombre }}</td></tr>
        <tr><td class="label">Plan de pago</td><td>{{ $pago->inscripcion->planPago->nombre }}</td></tr>
        <tr><td class="label">Cuota</td><td>N.° {{ $pago->nro_cuota }}</td></tr>
        <tr><td class="label">Método de pago</td><td>{{ $pago->metodoPago->nombre }}</td></tr>
        <tr><td class="label">Fecha</td><td>{{ $pago->fecha }}</td></tr>
        <tr><td class="label">Estado</td><td>{{ ucfirst($pago->estado_pago) }}</td></tr>
        <tr><td class="label">Monto pagado</td><td class="monto">{{ $pago->monto }} Bs.</td></tr>
    </table>

    <p class="nota">
        Este comprobante certifica el pago registrado en el sistema de {{ config('app.name') }}.
        No constituye una factura fiscal.
    </p>
</body>
</html>
