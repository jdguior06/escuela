<?php

namespace App\Providers;

use App\Models\Bitacora;
use App\Models\Usuario;
use App\Observers\UsuarioObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    }
}
