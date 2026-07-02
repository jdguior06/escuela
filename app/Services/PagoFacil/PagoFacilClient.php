<?php

namespace App\Services\PagoFacil;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PagoFacilClient
{
    public function login(): string
    {
        // El token de PagoFácil vence a las 23:59:59 del día en que se generó
        // (confirmado por PagoFácil), no simplemente 60 minutos después. Si el
        // login ocurre tarde en el día, cachearlo por 60 min fijos lo dejaría
        // "vigente" del lado de Laravel varios minutos después de que el
        // servidor de PagoFácil ya lo invalidó.
        $vencimiento = now()->addMinutes(60)->min(now()->endOfDay());

        return Cache::remember('pagofacil_access_token', $vencimiento, function () {
            $response = Http::withHeaders([
                'tcTokenService' => config('services.pagofacil.token_service'),
                'tcTokenSecret' => config('services.pagofacil.token_secret'),
                'Response-Language' => 'es',
            ])->post(config('services.pagofacil.base_url').'/login');

            $data = $response->json();

            if ((int) ($data['error'] ?? 1) !== 0) {
                throw new RuntimeException('PagoFacil login falló: '.($data['message'] ?? 'error desconocido'));
            }

            return $data['values']['accessToken'];
        });
    }

    public function generarQr(array $payload): array
    {
        $token = $this->login();

        $response = Http::withToken($token)
            ->withHeaders(['Response-Language' => 'es'])
            ->post(config('services.pagofacil.base_url').'/generate-qr', $payload);

        $data = $response->json();
        Log::debug('[pagofacil.generate-qr] respuesta cruda', ['data' => $data]);

        if ((int) ($data['error'] ?? 1) !== 0) {
            throw new RuntimeException('PagoFacil generate-qr falló: '.($data['message'] ?? 'error desconocido'));
        }

        return [
            'transaction_id' => $data['values']['transactionId'] ?? $data['transactionId'],
            'qr_base64' => $data['values']['qrBase64'] ?? $data['qrBase64'],
        ];
    }
}
