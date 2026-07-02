# Integración PagoFácil CheckOut en Laravel + Inertia + Vue

> Basado en la especificación oficial PagoFácil CheckOut v2.1 (24/06/2024).  
> Esta guía reemplaza el servidor Node.js independiente por rutas nativas de Laravel.

---

## Índice

1. [Flujo completo](#1-flujo-completo)
2. [Variables de entorno](#2-variables-de-entorno)
3. [Configuración](#3-configuración)
4. [Rutas](#4-rutas)
5. [Modelo Pago](#5-modelo-pago)
6. [Controlador PagoFacilController](#6-controlador-pagofacilcontroller)
   - [iniciarPago()](#61-iniciarpago)
   - [callback()](#62-callback)
   - [retorno()](#63-retorno)
   - [factura()](#64-factura)
7. [Vista Vue — Botón de Pago](#7-vista-vue--botón-de-pago)
8. [Excluir callback del CSRF](#8-excluir-callback-del-csrf)
9. [Estados y Métodos de Pago](#9-estados-y-métodos-de-pago)
10. [Respuesta requerida por PagoFácil](#10-respuesta-requerida-por-pagofácil)

---

## 1. Flujo completo

```
Usuario                 Laravel                    PagoFácil
   |                      |                            |
   |-- click "Pagar" ---> |                            |
   |                      | construye tcDatosCheckout  |
   |<-- form POST --------|--------------------------> |
   |                      |                            |
   |                      |          [usuario paga]    |
   |                      |                            |
   |                      |<-- POST /pagofacil/callback|
   |                      |  {PedidoID, Estado, ...}   |
   |                      | UPDATE pago SET estado...  |
   |                      |-- JSON {values:true} ----> |
   |                      |                            |
   |<-- redirect ---------|<-- redirige a UrlReturn    |
   |  /pagofacil/retorno  |                            |
```

---

## 2. Variables de entorno

Agrega en tu `.env` de Laravel:

```env
PAGOFACIL_COMMERCE_ID=8c1f1046219ddd216a023f792356ddf127fce372a72ec9b4cdac989ee5b0b455
PAGOFACIL_TOKEN_SERVICE=51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f...
PAGOFACIL_CHECKOUT_URL=https://checkout.pagofacil.com.bo/es/pay
```

> `PAGOFACIL_TOKEN_SERVICE` es el **TokenService** (clave de integración).  
> `PAGOFACIL_COMMERCE_ID` es el **CommerceID** que provee PagoFácil por contrato.

---

## 3. Configuración

Crea `config/pagofacil.php`:

```php
<?php

return [
    'commerce_id'   => env('PAGOFACIL_COMMERCE_ID'),
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    'checkout_url'  => env('PAGOFACIL_CHECKOUT_URL', 'https://checkout.pagofacil.com.bo/es/pay'),
    'callback_url'  => env('APP_URL') . '/pagofacil/callback',
    'return_url'    => env('APP_URL') . '/pagofacil/retorno',
    'factura_url'   => env('APP_URL') . '/pagofacil/factura',
    'moneda'        => 2,  // 2 = Bolivianos
    'p4'            => 11, // valor fijo requerido por PagoFácil
];
```

---

## 4. Rutas

En `routes/web.php`:

```php
use App\Http\Controllers\PagoFacilController;

Route::prefix('pagofacil')->name('pagofacil.')->group(function () {
    Route::post('iniciar',   [PagoFacilController::class, 'iniciarPago'])->name('iniciar');
    Route::post('callback',  [PagoFacilController::class, 'callback'])->name('callback');
    Route::get('retorno',    [PagoFacilController::class, 'retorno'])->name('retorno');
    Route::post('factura',   [PagoFacilController::class, 'factura'])->name('factura');
});
```

---

## 5. Modelo Pago

El modelo que ya existe en la BD. Solo necesita los campos que usa el callback:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';

    protected $fillable = [
        'nro_pedido',
        'estado_pago',
        'actualizado_en',
    ];

    // Mapeo de número de estado PagoFácil → texto en BD
    public static array $estadoMap = [
        1 => 'pendiente',
        2 => 'pagado',
        3 => 'revertido',
        4 => 'anulado',
    ];
}
```

---

## 6. Controlador PagoFacilController

Crea `app/Http/Controllers/PagoFacilController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoFacilController extends Controller
{
    // ---------------------------------------------------------------
    // 6.1 iniciarPago()
    // ---------------------------------------------------------------

    /**
     * Construye los parámetros tcCommerceID y tcDatosCheckout
     * y redirige al checkout de PagoFácil mediante un form auto-submit.
     *
     * Parámetros esperados en el request:
     *   - pago_id    : int    — ID del registro en tabla `pago` (= PedidoID)
     *   - monto      : float  — monto total a cobrar
     *   - email      : string — email del cliente
     *   - telefono   : string — teléfono del cliente
     *   - productos  : array  — lista de productos (ver estructura abajo)
     *
     * Estructura de cada producto en `productos`:
     *   [
     *     'serial'    => 1,
     *     'producto'  => 'Nombre del producto',
     *     'cantidad'  => 2,
     *     'precio'    => 50.00,
     *     'descuento' => 0,
     *     'total'     => 100.00,
     *   ]
     */
    public function iniciarPago(Request $request)
    {
        $request->validate([
            'pago_id'              => 'required|integer|exists:pago,id',
            'monto'                => 'required|numeric|min:0.01',
            'email'                => 'required|email',
            'telefono'             => 'required|string',
            'productos'            => 'required|array|min:1',
            'productos.*.serial'   => 'required|integer',
            'productos.*.producto' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'   => 'required|numeric',
            'productos.*.descuento'=> 'required|numeric',
            'productos.*.total'    => 'required|numeric',
        ]);

        $cfg = config('pagofacil');

        // Construir P3: array de productos con formato PagoFácil
        $p3 = json_encode(
            array_map(fn($p) => [
                'Serial'    => $p['serial'],
                'Producto'  => $p['producto'],
                'LinkPago'  => 0,
                'Cantidad'  => $p['cantidad'],
                'Precio'    => $p['precio'],
                'Descuento' => $p['descuento'],
                'Total'     => $p['total'],
            ], $request->productos),
            JSON_UNESCAPED_UNICODE
        );

        // Construir la cadena plana requerida:
        // "TokenService|Email|Telefono|PedidoID|Monto|Moneda|P1|P2|P3|P4"
        $cadena = implode('|', [
            $cfg['token_service'],
            $request->email,
            $request->telefono,
            $request->pago_id,
            $request->monto,
            $cfg['moneda'],
            $cfg['callback_url'],   // P1
            $cfg['return_url'],     // P2
            $p3,                    // P3
            $cfg['p4'],             // P4 = 11
        ]);

        $tcDatosCheckout = base64_encode($cadena);
        $tcCommerceID    = $cfg['commerce_id'];
        $checkoutUrl     = $cfg['checkout_url'];

        // Renderizar página intermedia con form auto-submit hacia PagoFácil
        return Inertia::render('Pagos/Checkout', [
            'checkoutUrl'     => $checkoutUrl,
            'tcCommerceID'    => $tcCommerceID,
            'tcDatosCheckout' => $tcDatosCheckout,
        ]);
    }

    // ---------------------------------------------------------------
    // 6.2 callback()
    // ---------------------------------------------------------------

    /**
     * PagoFácil llama a esta URL (POST) al finalizar el pago.
     *
     * Body JSON recibido:
     * {
     *   "PedidoID"   : "15",
     *   "Fecha"      : "2025-01-15",
     *   "Hora"       : "14:30:00",
     *   "MetodoPago" : 4,
     *   "Estado"     : 2
     * }
     *
     * IMPORTANTE: Esta ruta debe estar excluida del middleware CSRF.
     * Ver sección 8 de esta documentación.
     */
    public function callback(Request $request)
    {
        $pedidoId  = (int) $request->input('PedidoID');
        $estadoNum = (int) $request->input('Estado');
        $estadoTxt = Pago::$estadoMap[$estadoNum] ?? null;

        Log::info('[pagofacil.callback]', [
            'PedidoID'   => $pedidoId,
            'Estado'     => $estadoNum,
            'estadoTxt'  => $estadoTxt,
            'Fecha'      => $request->input('Fecha'),
            'Hora'       => $request->input('Hora'),
            'MetodoPago' => $request->input('MetodoPago'),
        ]);

        if (!$estadoTxt) {
            Log::warning('[pagofacil.callback] Estado desconocido', ['Estado' => $estadoNum]);
            return response()->json([
                'error'   => 0,
                'status'  => 1,
                'message' => 'Estado no reconocido.',
                'values'  => false,
            ]);
        }

        $pago = Pago::where('nro_pedido', $pedidoId)->first();

        if (!$pago) {
            Log::warning('[pagofacil.callback] PedidoID no encontrado', ['PedidoID' => $pedidoId]);
            return response()->json([
                'error'   => 0,
                'status'  => 1,
                'message' => 'Pago no encontrado.',
                'values'  => false,
            ]);
        }

        $pago->update([
            'estado_pago'    => $estadoTxt,
            'actualizado_en' => now(),
        ]);

        Log::info('[pagofacil.callback] Pago actualizado', [
            'PedidoID' => $pedidoId,
            'estado'   => $estadoTxt,
        ]);

        return response()->json([
            'error'   => 0,
            'status'  => 1,
            'message' => 'Pago actualizado correctamente.',
            'values'  => true,
        ]);
    }

    // ---------------------------------------------------------------
    // 6.3 retorno()
    // ---------------------------------------------------------------

    /**
     * PagoFácil redirige al cliente a esta URL después del pago.
     * Aquí se muestra la página de confirmación al usuario.
     */
    public function retorno(Request $request)
    {
        $pedidoId = $request->query('PedidoID');

        $pago = $pedidoId
            ? Pago::where('nro_pedido', $pedidoId)->first()
            : null;

        return Inertia::render('Pagos/Retorno', [
            'pago' => $pago,
        ]);
    }

    // ---------------------------------------------------------------
    // 6.4 factura()
    // ---------------------------------------------------------------

    /**
     * PagoFácil puede llamar a esta URL para obtener la factura/recibo
     * del pedido en formato bytes (PDF, imagen, etc.).
     *
     * Parámetros recibidos: { "PedidoID": 15 }
     * Si PedidoID es 0 o vacío = pago no exitoso.
     */
    public function factura(Request $request)
    {
        $pedidoId = (int) $request->input('PedidoID');

        if (!$pedidoId) {
            return response()->json([
                'error'   => 1,
                'status'  => 0,
                'message' => 'PedidoID inválido.',
                'values'  => false,
            ]);
        }

        $pago = Pago::where('nro_pedido', $pedidoId)->first();

        if (!$pago) {
            return response()->json([
                'error'   => 1,
                'status'  => 0,
                'message' => 'Pedido no encontrado.',
                'values'  => false,
            ]);
        }

        // TODO: generar el PDF/recibo y retornar los bytes
        // Ejemplo con Dompdf:
        // $pdf = PDF::loadView('pagos.recibo', ['pago' => $pago]);
        // return response($pdf->output(), 200)
        //     ->header('Content-Type', 'application/pdf');

        return response()->json([
            'error'   => 0,
            'status'  => 1,
            'message' => 'Factura generada.',
            'values'  => true,
        ]);
    }
}
```

---

## 7. Vista Vue — Botón de Pago

### 7.1 Página intermedia de checkout (`resources/js/Pages/Pagos/Checkout.vue`)

Esta página recibe los parámetros de Laravel y hace auto-submit del form hacia PagoFácil:

```vue
<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
    checkoutUrl:     String,
    tcCommerceID:    String,
    tcDatosCheckout: String,
})

const form = ref(null)

onMounted(() => {
    form.value?.submit()
})
</script>

<template>
    <div class="flex items-center justify-center min-h-screen">
        <p class="text-gray-500">Redirigiendo a PagoFácil...</p>

        <form
            ref="form"
            method="POST"
            :action="checkoutUrl"
            enctype="multipart/form-data"
            class="hidden"
        >
            <input type="hidden" name="tcCommerceID"    :value="tcCommerceID" />
            <input type="hidden" name="tcDatosCheckout" :value="tcDatosCheckout" />
        </form>
    </div>
</template>
```

### 7.2 Botón de pago en tu vista existente

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    pagoId:    Number,
    monto:     Number,
    email:     String,
    telefono:  String,
    productos: Array,
})

const form = useForm({
    pago_id:   props.pagoId,
    monto:     props.monto,
    email:     props.email,
    telefono:  props.telefono,
    productos: props.productos,
})

function pagar() {
    form.post(route('pagofacil.iniciar'))
}
</script>

<template>
    <button
        @click="pagar"
        :disabled="form.processing"
        class="btn btn-primary"
    >
        {{ form.processing ? 'Redirigiendo...' : 'Pagar con PagoFácil' }}
    </button>
</template>
```

### 7.3 Página de retorno (`resources/js/Pages/Pagos/Retorno.vue`)

```vue
<script setup>
defineProps({
    pago: Object,
})
</script>

<template>
    <div class="max-w-lg mx-auto mt-16 text-center">
        <template v-if="pago?.estado_pago === 'pagado'">
            <h1 class="text-2xl font-bold text-green-600">¡Pago exitoso!</h1>
            <p class="mt-2 text-gray-600">Tu pedido #{{ pago.nro_pedido }} fue procesado correctamente.</p>
        </template>
        <template v-else>
            <h1 class="text-2xl font-bold text-yellow-600">Pago en proceso</h1>
            <p class="mt-2 text-gray-600">Tu pago está siendo verificado. Te notificaremos cuando se confirme.</p>
        </template>
    </div>
</template>
```

---

## 8. Excluir callback del CSRF

PagoFácil hace un POST desde sus servidores sin token CSRF. Debes excluir la ruta.

**Laravel 11+ (`bootstrap/app.php`):**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'pagofacil/callback',
        'pagofacil/factura',
    ]);
})
```

**Laravel 10 (`app/Http/Middleware/VerifyCsrfToken.php`):**

```php
protected $except = [
    'pagofacil/callback',
    'pagofacil/factura',
];
```

---

## 9. Estados y Métodos de Pago

### Estados (campo `Estado`)

| Valor | Texto en BD   | Significado            |
|-------|---------------|------------------------|
| 1     | `pendiente`   | Pendiente / En proceso |
| 2     | `pagado`      | Pago exitoso           |
| 3     | `revertido`   | Pago revertido         |
| 4     | `anulado`     | Pago anulado           |

### Métodos de pago (campo `MetodoPago`)

| Valor | Método                        |
|-------|-------------------------------|
| 1     | TigoMoney                     |
| 2     | Punto PagoFácil               |
| 3     | Tarjeta Débito/Crédito-Enlace |
| 4     | Transferencia Bancos QR       |
| 5     | BCP Rápido y Seguro           |
| 6     | LinkSer                       |
| 7     | Soli Pagos                    |

---

## 10. Respuesta requerida por PagoFácil

El callback **siempre debe responder HTTP 200**, independientemente del resultado. Si se responde con error HTTP, PagoFácil reintentará indefinidamente.

```json
{
    "error": 0,
    "status": 1,
    "message": "Pago actualizado correctamente.",
    "messageMostrar": 0,
    "messageSistema": "",
    "values": true
}
```

| Campo            | Tipo   | Descripción                         |
|------------------|--------|-------------------------------------|
| `error`          | int    | Siempre `0`                         |
| `status`         | int    | Siempre `1`                         |
| `message`        | string | Mensaje descriptivo                 |
| `messageMostrar` | int    | `0` (no mostrar al usuario)         |
| `messageSistema` | string | Vacío                               |
| `values`         | bool   | `true` si se procesó, `false` si no |

---

## Resumen de archivos a crear/modificar

| Archivo                                         | Acción    |
|-------------------------------------------------|-----------|
| `.env`                                          | Modificar — agregar variables `PAGOFACIL_*` |
| `config/pagofacil.php`                          | Crear     |
| `routes/web.php`                                | Modificar — agregar rutas `pagofacil.*` |
| `app/Http/Controllers/PagoFacilController.php` | Crear     |
| `app/Models/Pago.php`                           | Crear/verificar |
| `bootstrap/app.php` o `VerifyCsrfToken.php`    | Modificar — excluir callback de CSRF |
| `resources/js/Pages/Pagos/Checkout.vue`         | Crear     |
| `resources/js/Pages/Pagos/Retorno.vue`          | Crear     |
