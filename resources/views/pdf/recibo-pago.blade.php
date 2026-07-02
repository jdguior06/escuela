<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de pago</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 6px 0; }
        td.label { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <h1>Recibo de pago</h1>
    <table>
        <tr><td class="label">N.° de pedido</td><td>{{ $pago->nro_pedido }}</td></tr>
        <tr><td class="label">Cuota</td><td>{{ $pago->nro_cuota }}</td></tr>
        <tr><td class="label">Monto</td><td>{{ $pago->monto }} Bs.</td></tr>
        <tr><td class="label">Estado</td><td>{{ $pago->estado_pago }}</td></tr>
        <tr><td class="label">Fecha</td><td>{{ $pago->fecha }}</td></tr>
        <tr><td class="label">Estudiante</td><td>{{ $pago->inscripcion->estudiante->nombre }} {{ $pago->inscripcion->estudiante->apellido }}</td></tr>
    </table>
</body>
</html>
