<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pago confirmado</title>
</head>
<body style="font-family: sans-serif;">
    <h1>¡Tu pago fue confirmado!</h1>
    <p>Se confirmó el pago de la cuota N.° {{ $pago->nro_cuota }} por un monto de {{ $pago->monto }} Bs.</p>
    <p>Número de pedido: {{ $pago->nro_pedido }}</p>
    <p>Fecha: {{ $pago->fecha }}</p>
</body>
</html>
