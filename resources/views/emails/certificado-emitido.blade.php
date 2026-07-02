<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado emitido</title>
</head>
<body style="font-family: sans-serif;">
    <h1>¡Felicidades! Tu certificado está listo</h1>
    <p>Nota obtenida: {{ number_format($certificacion->nota, 1) }}</p>
    <p>Fecha de emisión: {{ $certificacion->fecha_emision }}</p>
    <p>Adjuntamos tu certificado en PDF.</p>
</body>
</html>
