<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Bienvenido</title>
</head>
<body style="font-family: sans-serif;">
    <h1>¡Bienvenido, {{ $usuario->nombre }}!</h1>
    <p>Tu cuenta en {{ config('app.name') }} fue creada correctamente.</p>
    <p>Correo de acceso: {{ $usuario->correo }}</p>
    <p>Ya puedes ingresar a la plataforma con tu correo y contraseña.</p>
</body>
</html>
