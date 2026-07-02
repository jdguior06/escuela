# Implementación del módulo de Pagos en Laravel + Inertia + Vue

> Reutilizando **la misma base de datos y el mismo esquema** que ya existe en PostgreSQL (`db_grupo13sa`), sin renombrar tablas ni columnas.

---

## 0. Detalle crítico del esquema: nombres reales de las tablas

El SQL original crea las tablas sin comillas (`CREATE TABLE Pago`, `CREATE TABLE MetodoPago`...). PostgreSQL **pliega a minúsculas los identificadores no citados**, y no inserta guiones bajos. Es decir, el nombre físico real en Postgres (confirmado también por las queries del Java: `FROM Pago`, `JOIN MetodoPago`, etc., que Postgres resuelve igual) es:

| Nombre en el script (visual) | Nombre físico real en Postgres |
|---|---|
| `Pago` | `pago` |
| `MetodoPago` | `metodopago` |
| `PlanPago` | `planpago` |
| `Inscripcion` | `inscripcion` |
| `Usuario` | `usuario` |
| `Curso` | `curso` |
| `ControlCertificacion` | `controlcertificacion` |

**Esto es lo primero que hay que configurar bien en Eloquent**, porque por defecto Eloquent buscaría `pagos`, `metodo_pagos`, etc. (plural + snake_case), que no existen.

También importante: las columnas de fecha son `creado_en` / `actualizado_en`, **no** `created_at` / `updated_at` (los nombres por defecto de Laravel).

---

## 1. Configuración de conexión

`.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=www.tecnoweb.org.bo   # o el host real que uses
DB_PORT=5432
DB_DATABASE=db_grupo13sa
DB_USERNAME=grupo13sa
DB_PASSWORD=********
```

`config/database.php` ya trae el driver `pgsql` por defecto en Laravel, no requiere cambios.

---

## 2. Migraciones (para tener el esquema versionado en Laravel)

Aunque la base ya existe, conviene tener migraciones que la repliquen exactamente — sirve para levantar entornos nuevos (testing, otro dev) sin depender del `.sql` suelto.

```php
// database/migrations/2024_01_01_000001_create_metodopago_table.php
Schema::create('metodopago', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 50)->unique();
    $table->string('descripcion', 150)->nullable();
    $table->timestamp('creado_en')->useCurrent();
    $table->timestamp('actualizado_en')->useCurrent();
});
```

```php
// database/migrations/2024_01_01_000002_create_planpago_table.php
Schema::create('planpago', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 50);
    $table->integer('numero_cuotas');
    $table->string('estado', 20);
    $table->timestamp('creado_en')->useCurrent();
    $table->timestamp('actualizado_en')->useCurrent();
});
```

```php
// database/migrations/2024_01_01_000010_create_pago_table.php
Schema::create('pago', function (Blueprint $table) {
    $table->id();
    $table->date('fecha');
    $table->float('monto');
    $table->integer('nro_cuota')->default(1);
    $table->string('id_transaccion', 100)->nullable();
    $table->string('nro_pedido', 50)->nullable();
    $table->string('estado_pago', 20)->default('pendiente');
    $table->string('correo_notificacion', 150)->nullable();
    $table->boolean('notificado')->default(false);
    $table->timestamp('creado_en')->useCurrent();
    $table->timestamp('actualizado_en')->useCurrent();

    $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete()->cascadeOnUpdate();
    $table->foreignId('metodo_id')->constrained('metodopago')->restrictOnDelete()->cascadeOnUpdate();
    $table->foreignId('inscripcion_id')->constrained('inscripcion')->restrictOnDelete()->cascadeOnUpdate();
});
```

> Nota: `inscripcion` y `usuario` deben existir antes (mismo criterio: tablas ya migradas del resto del sistema). Si esas migraciones ya las tiene otro módulo del equipo, solo referencia `constrained('inscripcion')` / `constrained('usuario')`.

Si la base **ya existe en producción y no quieres correr migraciones ahí**, usa `php artisan migrate --pretend` solo para verificar que coincide, o simplemente omite migrar en ese entorno y deja las migraciones como documentación/entorno de test.

---

## 3. Modelos Eloquent

```php
// app/Models/MetodoPago.php
class MetodoPago extends Model
{
    protected $table = 'metodopago';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['nombre', 'descripcion'];
}
```

```php
// app/Models/PlanPago.php
class PlanPago extends Model
{
    protected $table = 'planpago';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['nombre', 'numero_cuotas', 'estado'];
}
```

```php
// app/Models/Pago.php
class Pago extends Model
{
    protected $table = 'pago';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'fecha', 'monto', 'nro_cuota', 'id_transaccion', 'nro_pedido',
        'estado_pago', 'correo_notificacion', 'notificado',
        'usuario_id', 'metodo_id', 'inscripcion_id',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'monto'       => 'float',
        'notificado'  => 'boolean',
    ];

    public function usuario(): BelongsTo      { return $this->belongsTo(Usuario::class, 'usuario_id'); }
    public function metodoPago(): BelongsTo   { return $this->belongsTo(MetodoPago::class, 'metodo_id'); }
    public function inscripcion(): BelongsTo  { return $this->belongsTo(Inscripcion::class, 'inscripcion_id'); }

    public function scopePendientesNoNotificados($query)
    {
        return $query->where('estado_pago', 'pagado')->where('notificado', false);
    }
}
```

```php
// app/Models/Inscripcion.php  (mínimo necesario para el módulo de pagos)
class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = ['fecha_inscripcion', 'estado_inscripcion', 'monto_total',
                            'estudiante_id', 'plan_pago_id', 'curso_id'];

    public function planPago(): BelongsTo { return $this->belongsTo(PlanPago::class, 'plan_pago_id'); }
    public function estudiante(): BelongsTo { return $this->belongsTo(Usuario::class, 'estudiante_id'); }
    public function pagos(): HasMany { return $this->hasMany(Pago::class, 'inscripcion_id'); }

    public function cuotasPagadas(): int
    {
        return $this->pagos()->where('estado_pago', 'pagado')->count();
    }
}
```

Si `Usuario` ya es tu modelo de autenticación, recuerda que también usa `creado_en`/`actualizado_en` en vez de los timestamps por defecto — agrega las mismas dos constantes ahí.

---

## 4. Configuración de PagoFacil (credenciales)

`.env`:
```env
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo/api/services/v2
PAGOFACIL_TOKEN_SERVICE=xxxxx
PAGOFACIL_TOKEN_SECRET=xxxxx
PAGOFACIL_CLIENT_CODE=11001
PAGOFACIL_CALLBACK_URL=https://tu-dominio.com/webhooks/pagofacil
PAGOFACIL_SANDBOX_DIVISOR=1000
```

`config/services.php`:
```php
'pagofacil' => [
    'base_url'        => env('PAGOFACIL_BASE_URL'),
    'token_service'   => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret'    => env('PAGOFACIL_TOKEN_SECRET'),
    'client_code'     => env('PAGOFACIL_CLIENT_CODE'),
    'callback_url'    => env('PAGOFACIL_CALLBACK_URL'),
    'sandbox_divisor' => (float) env('PAGOFACIL_SANDBOX_DIVISOR', 1), // 1000 mientras se prueba, 1 en producción
],
```

**Sobre la división entre 1000**: en el sistema Java el monto que se manda a PagoFacil se dividía entre 1000 como ajuste de pruebas (curso de 500 Bs → 0.50 Bs al API). Ya que ustedes todavía están en fase de pruebas, se mantiene esa lógica por ahora, pero **no hardcodeada** — queda como el valor `sandbox_divisor` en config/`.env` (por defecto `1000` mientras prueban). Cuando pasen a producción, el único cambio necesario es poner `PAGOFACIL_SANDBOX_DIVISOR=1` (o quitar la variable), sin tocar código. Ver el uso en `pagarConQr()` más abajo.

---

## 5. Cliente HTTP de PagoFacil

```php
// app/Services/PagoFacil/PagoFacilClient.php
namespace App\Services\PagoFacil;

use Illuminate\Support\Facades\Http;

class PagoFacilClient
{
    public function login(): string
    {
        $response = Http::withHeaders([
            'tcTokenService' => config('services.pagofacil.token_service'),
            'tcTokenSecret'  => config('services.pagofacil.token_secret'),
            'Response-Language' => 'es',
        ])->post(config('services.pagofacil.base_url') . '/login');

        $data = $response->json();

        if (($data['error'] ?? '1') !== '0') {
            throw new \RuntimeException('PagoFacil login falló: ' . ($data['message'] ?? 'error desconocido'));
        }

        return $data['accessToken'];
    }

    public function generarQr(array $payload): array
    {
        $token = $this->login();

        $response = Http::withToken($token)
            ->withHeaders(['Response-Language' => 'es'])
            ->post(config('services.pagofacil.base_url') . '/generate-qr', $payload);

        $data = $response->json();

        if (($data['error'] ?? '1') !== '0') {
            throw new \RuntimeException('PagoFacil generate-qr falló: ' . ($data['message'] ?? 'error desconocido'));
        }

        return [
            'transaction_id' => $data['transactionId'],
            'qr_base64'      => $data['qrBase64'],
        ];
    }
}
```

---

## 6. Servicio de negocio (equivalente a `NPago.guardar()`)

```php
// app/Services/PagoService.php
namespace App\Services;

use App\Models\Pago;
use App\Models\Inscripcion;
use App\Services\PagoFacil\PagoFacilClient;
use Illuminate\Support\Facades\DB;

class PagoService
{
    public function __construct(private PagoFacilClient $pagoFacil) {}

    public function registrarPago(int $inscripcionId, int $metodoId, string $correoSender): array
    {
        if (!in_array($metodoId, [1, 2], true)) {
            throw new \InvalidArgumentException('Método de pago inválido: use 1 (Efectivo) o 2 (QR).');
        }

        $inscripcion = Inscripcion::with('planPago', 'estudiante')->findOrFail($inscripcionId);

        $totalCuotas   = $inscripcion->planPago->numero_cuotas;
        $cuotasPagadas = $inscripcion->cuotasPagadas();

        if ($cuotasPagadas >= $totalCuotas) {
            throw new \DomainException("La inscripción ya tiene todas sus cuotas pagadas ({$totalCuotas}/{$totalCuotas}).");
        }

        $nroCuota   = $cuotasPagadas + 1;
        $montoCuota = round($inscripcion->monto_total / $totalCuotas, 2);

        return $metodoId === 1
            ? $this->pagarEfectivo($inscripcion, $metodoId, $nroCuota, $montoCuota)
            : $this->pagarConQr($inscripcion, $metodoId, $nroCuota, $montoCuota, $correoSender);
    }

    private function pagarEfectivo(Inscripcion $inscripcion, int $metodoId, int $nroCuota, float $monto): array
    {
        $pago = Pago::create([
            'fecha'          => now()->toDateString(),
            'monto'          => $monto,
            'nro_cuota'      => $nroCuota,
            'estado_pago'    => 'pagado',
            'notificado'     => true,
            'usuario_id'     => $inscripcion->estudiante_id,
            'metodo_id'      => $metodoId,
            'inscripcion_id' => $inscripcion->id,
        ]);

        return ['tipo' => 'efectivo', 'pago' => $pago];
    }

    private function pagarConQr(Inscripcion $inscripcion, int $metodoId, int $nroCuota, float $monto, string $correoSender): array
    {
        $pago = Pago::create([
            'fecha'               => now()->toDateString(),
            'monto'               => $monto,
            'nro_cuota'           => $nroCuota,
            'estado_pago'         => 'pendiente',
            'correo_notificacion' => $correoSender,
            'usuario_id'          => $inscripcion->estudiante_id,
            'metodo_id'           => $metodoId,
            'inscripcion_id'      => $inscripcion->id,
        ]);

        $paymentNumber = now()->format('YmdHis') . str_pad($pago->id, 4, '0', STR_PAD_LEFT);

        // Mientras se prueba contra el sandbox de PagoFacil se divide el monto real
        // por PAGOFACIL_SANDBOX_DIVISOR (1000 por defecto, igual que el sistema Java).
        // El monto guardado en `pago.monto` es siempre el monto real de la cuota;
        // solo el monto que se envía a la API externa se ajusta.
        $montoApi = round($monto / config('services.pagofacil.sandbox_divisor'), 4);

        try {
            $qr = $this->pagoFacil->generarQr([
                'paymentMethod' => 34,
                'clientName'    => trim($inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido),
                'documentType'  => 1,
                'documentId'    => $inscripcion->estudiante->nro_documento,
                'phoneNumber'   => $inscripcion->estudiante->telefono,
                'email'         => $correoSender,
                'paymentNumber' => $paymentNumber,
                'amount'        => $montoApi,
                'currency'      => 2,
                'clientCode'    => config('services.pagofacil.client_code'),
                'callbackUrl'   => config('services.pagofacil.callback_url'),
                'orderDetail'   => [[
                    'serial'   => 1,
                    'product'  => $inscripcion->tipoCursoNombre(),
                    'quantity' => 1,
                    'price'    => $montoApi,
                    'discount' => 0,
                    'total'    => $montoApi,
                ]],
            ]);
        } catch (\Throwable $e) {
            $pago->delete(); // evitar registros huérfanos, igual que en el sistema original
            throw $e;
        }

        $pago->update([
            'id_transaccion' => $qr['transaction_id'],
            'nro_pedido'     => $paymentNumber,
        ]);

        return [
            'tipo'           => 'qr',
            'pago'           => $pago,
            'qr_base64'      => $qr['qr_base64'],
            'transaction_id' => $qr['transaction_id'],
        ];
    }

    /** Llamado desde el webhook de PagoFacil cuando confirma el pago. */
    public function confirmarPago(string $transactionId): Pago
    {
        return DB::transaction(function () use ($transactionId) {
            $pago = Pago::where('id_transaccion', $transactionId)->lockForUpdate()->firstOrFail();
            $pago->update(['estado_pago' => 'pagado']);
            return $pago;
        });
    }
}
```

> Agrega un método `tipoCursoNombre()` en `Inscripcion` (o carga la relación `curso.tipoCurso`) según cómo esté modelado ese lado en tu proyecto.

---

## 7. Validación de entrada (Form Request)

```php
// app/Http/Requests/StorePagoRequest.php
class StorePagoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'inscripcion_id' => ['required', 'integer', 'exists:inscripcion,id'],
            'metodo_id'      => ['required', 'integer', Rule::in([1, 2])],
        ];
    }
}
```

---

## 8. Controlador + rutas (Inertia)

```php
// app/Http/Controllers/PagoController.php
class PagoController extends Controller
{
    public function __construct(private PagoService $pagoService) {}

    public function index(Inscripcion $inscripcion)
    {
        return Inertia::render('Pagos/Index', [
            'inscripcion' => $inscripcion->load('planPago'),
            'pagos'       => $inscripcion->pagos()->with('metodoPago')->orderBy('id')->get(),
        ]);
    }

    public function store(StorePagoRequest $request)
    {
        $resultado = $this->pagoService->registrarPago(
            $request->integer('inscripcion_id'),
            $request->integer('metodo_id'),
            $request->user()->correo,
        );

        if ($resultado['tipo'] === 'qr') {
            return back()->with([
                'qr' => [
                    'base64'         => $resultado['qr_base64'],
                    'transaction_id' => $resultado['transaction_id'],
                    'pago_id'        => $resultado['pago']->id,
                ],
            ]);
        }

        return back()->with('success', 'Pago en efectivo registrado correctamente.');
    }

    /** Para que el Vue haga polling del estado mientras espera la confirmación del banco */
    public function estado(Pago $pago)
    {
        return response()->json([
            'estado_pago' => $pago->estado_pago,
        ]);
    }
}
```

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/inscripciones/{inscripcion}/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/pagos/{pago}/estado', [PagoController::class, 'estado'])->name('pagos.estado');
});
```

---

## 9. Webhook de confirmación (lo que el sistema Java nunca implementó)

```php
// app/Http/Controllers/PagoFacilWebhookController.php
class PagoFacilWebhookController extends Controller
{
    public function __invoke(Request $request, PagoService $pagoService)
    {
        // Verificar firma/token que PagoFacil envíe según su documentación
        // (ej. header propio o campo firmado) antes de confiar en el payload.
        $transactionId = $request->input('transactionId');

        $pago = $pagoService->confirmarPago($transactionId);

        NotificarPagoConfirmadoJob::dispatch($pago->id);

        return response()->json(['ok' => true]);
    }
}
```

```php
// routes/web.php (o routes/api.php)
Route::post('/webhooks/pagofacil', PagoFacilWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
```

> En Laravel 11+, en vez de `withoutMiddleware` en la ruta, agrega la URI a las excepciones de CSRF en `bootstrap/app.php`:
> ```php
> ->withMiddleware(function (Middleware $middleware) {
>     $middleware->validateCsrfTokens(except: ['webhooks/pagofacil']);
> })
> ```

**Esto reemplaza por completo al `PagoNotificacionThread` que hacía polling cada 8 segundos** en el sistema Java: aquí la confirmación llega por evento (el webhook), no por sondeo constante.

---

## 10. Notificación por correo (reemplaza `SendEmailThread`)

```php
// app/Jobs/NotificarPagoConfirmadoJob.php
class NotificarPagoConfirmadoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $pagoId) {}

    public function handle(): void
    {
        $pago = Pago::with('inscripcion.estudiante')->findOrFail($this->pagoId);

        if ($pago->notificado) {
            return; // evita duplicar el correo, igual que el flag `notificado` original
        }

        Mail::to($pago->correo_notificacion ?? $pago->inscripcion->estudiante->correo)
            ->send(new PagoConfirmadoMail($pago));

        $pago->update(['notificado' => true]);
    }
}
```

```php
// app/Mail/PagoConfirmadoMail.php
class PagoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pago $pago) {}

    public function build()
    {
        return $this->subject('Pago confirmado')
                     ->view('emails.pago-confirmado', ['pago' => $this->pago]);
    }
}
```

Para el correo con el QR (cuando se genera, no cuando se confirma) puedes enviarlo directo desde el controlador o un job similar, adjuntando la imagen decodificada desde el `qr_base64`.

---

## 11. Frontend: Inertia + Vue

### `resources/js/Pages/Pagos/Index.vue`

```vue
<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({ inscripcion: Object, pagos: Array })
const page = usePage()

const form = useForm({
  inscripcion_id: props.inscripcion.id,
  metodo_id: 1,
})

const qr = computed(() => page.props.flash?.qr)

function pagar() {
  form.post(route('pagos.store'))
}
</script>

<template>
  <h1>Pagos de la inscripción #{{ inscripcion.id }}</h1>

  <table>
    <thead>
      <tr><th>Cuota</th><th>Monto</th><th>Método</th><th>Estado</th></tr>
    </thead>
    <tbody>
      <tr v-for="p in pagos" :key="p.id">
        <td>{{ p.nro_cuota }}</td>
        <td>{{ p.monto }}</td>
        <td>{{ p.metodo_pago.nombre }}</td>
        <td>{{ p.estado_pago }}</td>
      </tr>
    </tbody>
  </table>

  <form @submit.prevent="pagar">
    <select v-model="form.metodo_id">
      <option :value="1">Efectivo</option>
      <option :value="2">QR (PagoFacil)</option>
    </select>
    <button :disabled="form.processing">Pagar cuota</button>
  </form>

  <PagoQr v-if="qr" :qr="qr" />
</template>
```

### `resources/js/Components/PagoQr.vue` (muestra el QR y consulta el estado hasta que se confirme)

```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({ qr: Object })
const estado = ref('pendiente')
let intervalo

async function consultarEstado() {
  const { data } = await axios.get(`/pagos/${props.qr.pago_id}/estado`)
  estado.value = data.estado_pago
  if (estado.value === 'pagado') clearInterval(intervalo)
}

onMounted(() => {
  intervalo = setInterval(consultarEstado, 4000) // polling simple mientras no haya WebSockets
})
onUnmounted(() => clearInterval(intervalo))
</script>

<template>
  <div class="qr-box">
    <img :src="`data:image/png;base64,${qr.base64}`" alt="QR de pago" />
    <p v-if="estado === 'pendiente'">Esperando confirmación del banco...</p>
    <p v-else class="text-green-600">¡Pago confirmado!</p>
  </div>
</template>
```

**Alternativa mejor que el polling** (opcional, si el proyecto lo permite): usar **Laravel Reverb/Echo** para hacer *broadcast* del evento cuando el webhook confirma el pago, y que el Vue escuche el canal en vez de consultar cada 4 segundos. El polling es válido y más simple para un proyecto académico; broadcasting es la mejora natural si después se quiere pulir.

---

## 12. Checklist de implementación

- [ ] Modelos con `$table` y `CREATED_AT`/`UPDATED_AT` apuntando a los nombres reales (`pago`, `metodopago`, `planpago`, `creado_en`, `actualizado_en`).
- [ ] `PagoFacilClient` con credenciales en `.env`; el divisor de sandbox (`PAGOFACIL_SANDBOX_DIVISOR`) va por config, no hardcodeado — para pasar a producción basta con ponerlo en `1`, no requiere tocar el `PagoService`.
- [ ] `PagoService::registrarPago()` reproduce las reglas: método válido (1 o 2), no exceder cuotas del plan, cálculo `monto_total / numero_cuotas`, rollback del pago pendiente si PagoFacil falla.
- [ ] Ruta webhook `/webhooks/pagofacil` excluida de CSRF, valida el origen/firma del proveedor.
- [ ] `confirmarPago()` cambia `estado_pago` a `pagado` y dispara la notificación (reemplaza el thread de polling).
- [ ] Job de notificación respeta el flag `notificado` para no duplicar correos.
- [ ] Vista Vue de pago en efectivo (confirmación inmediata) y de pago QR (imagen + estado hasta confirmar).
