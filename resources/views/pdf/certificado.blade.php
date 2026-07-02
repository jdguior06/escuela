<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado</title>
    <style>
        @page { margin: 0; size: letter; }
        body { margin: 0; padding: 0; }

        .membrete {
            position: absolute;
            top: 0;
            left: 0;
            width: 612pt;
            height: 792pt;
            z-index: -1;
        }

        .contenido {
            position: absolute;
            left: 90pt;
            top: 147pt;
            width: 432pt;
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }

        .titulo {
            font-size: 18pt;
            font-weight: bold;
            color: rgb(26, 92, 42);
            margin-bottom: 4pt;
        }

        .linea {
            border-top: 1.5pt solid rgb(26, 92, 42);
            width: 65%;
            margin: 6pt auto 12pt auto;
        }

        .normal {
            font-size: 12pt;
            margin: 4pt 0;
        }

        .destacado {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 4pt 0;
        }

        .meta {
            font-size: 10pt;
            font-style: italic;
            color: rgb(80, 80, 80);
            margin: 2pt 0;
        }
    </style>
</head>
<body>
    <img src="{{ $membretePath }}" class="membrete">

    <div class="contenido">
        <div class="titulo">CERTIFICADO DE APROBACIÓN</div>
        <div class="linea"></div>

        <div class="normal">Se certifica que el/la estudiante:</div>
        <div class="destacado">{{ $certificacion->inscripcion->estudiante->nombre }} {{ $certificacion->inscripcion->estudiante->apellido }}</div>
        <div class="normal">C.I.: {{ $certificacion->inscripcion->estudiante->nro_documento }}</div>

        <div class="normal">ha completado el curso de:</div>
        <div class="destacado">{{ $certificacion->inscripcion->curso->tipoCurso->nombre }}</div>

        <div class="normal">con una calificación de {{ number_format($certificacion->nota, 1) }}</div>

        @if ($certificacion->inscripcion->curso->fecha_inicio && $certificacion->inscripcion->curso->fecha_fin)
            <div class="meta">Período del curso: {{ $certificacion->inscripcion->curso->fecha_inicio }} – {{ $certificacion->inscripcion->curso->fecha_fin }}</div>
        @endif

        @if ($certificacion->inscripcion->curso->instructor)
            <div class="meta">Instructor: {{ $certificacion->inscripcion->curso->instructor->nombre }} {{ $certificacion->inscripcion->curso->instructor->apellido }}</div>
        @endif

        <div class="meta">Santa Cruz, Bolivia — {{ $certificacion->fecha_emision }}</div>
        <div class="meta">Ref. Certificado N.° {{ $certificacion->id }}</div>
    </div>
</body>
</html>
