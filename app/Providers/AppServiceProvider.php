<?php

namespace App\Providers;

use App\Models\Bitacora;
use App\Models\Usuario;
use App\Observers\UsuarioObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Usuario::observe(UsuarioObserver::class);

        Event::listen(function (Login $event) {
            Bitacora::create([
                'usuario_id' => $event->user->id,
                'tipo_evento' => 'login_exitoso',
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        Event::listen(function (Failed $event) {
            Bitacora::create([
                'usuario_id' => $event->user?->id,
                'tipo_evento' => 'login_fallido',
                'recurso' => $event->credentials['correo'] ?? null,
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        // mail.tecnoweb.org.bo ofrece STARTTLS pero con un certificado que no
        // valida (self-signed / hostname no coincide). Sin esto, Symfony Mailer
        // aborta la conexión con "certificate verify failed" antes de enviar nada.
        Mail::extend('smtp', function (array $config) {
            $scheme = $config['scheme'] ?? (($config['port'] ?? null) == 465 ? 'smtps' : 'smtp');

            $transport = (new EsmtpTransportFactory)->create(new Dsn(
                $scheme,
                $config['host'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['port'] ?? null,
                $config
            ));

            $stream = $transport->getStream();

            if ($stream instanceof SocketStream) {
                $stream->setStreamOptions(array_merge_recursive($stream->getStreamOptions(), [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]));
            }

            return $transport;
        });
    }
}
