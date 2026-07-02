<?php

namespace App\Services;

use App\Jobs\NotificarCertificadoJob;
use App\Models\ControlCertificacion;
use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CertificacionService
{
    public function registrar(Inscripcion $inscripcion, float $nota): ControlCertificacion
    {
        $estado = $nota >= config('services.certificacion.nota_minima') ? 'aprobado' : 'reprobado';

        return DB::transaction(function () use ($inscripcion, $nota, $estado) {
            $certificacion = ControlCertificacion::create([
                'nota' => $nota,
                'estado_certificacion' => $estado,
                'fecha_emision' => now()->toDateString(),
                'inscripcion_id' => $inscripcion->id,
            ]);

            $inscripcion->curso->update(['estado_curso' => 'disponible']);
            $inscripcion->update(['estado_inscripcion' => 'finalizada']);

            if ($estado === 'aprobado') {
                $certificacion->update(['pdf_path' => $this->generarPdf($certificacion)]);
                NotificarCertificadoJob::dispatch($certificacion->id)->afterCommit();
            }

            return $certificacion;
        });
    }

    private function generarPdf(ControlCertificacion $certificacion): string
    {
        $certificacion->loadMissing('inscripcion.estudiante', 'inscripcion.curso.tipoCurso', 'inscripcion.curso.instructor');

        $pdf = Pdf::loadView('pdf.certificado', [
            'certificacion' => $certificacion,
            'membretePath' => resource_path('images/membrete.jpg'),
        ]);

        $path = 'certificados/certificado-'.$certificacion->id.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
