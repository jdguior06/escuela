# Documentación funcional: Módulo de Pagos y Módulo de Control de Certificación

> Sistema actual: Java + PostgreSQL, interpretación de comandos recibidos por email (lenguaje de comandos propio: `INSPAG`, `INSCRT`, etc.).
> Objetivo de este documento: explicar la lógica de negocio real (no solo el código) de estos dos módulos para poder reimplementarlos en **Laravel + Inertia + Vue**.

---

## 1. Contexto general

El sistema gestiona cursos de una autoescuela/academia (ACB). Un flujo típico es:

```
Estudiante se inscribe en un curso  →  Paga (una o varias cuotas)  →  Al finalizar el curso, se le certifica (nota) → se emite un PDF
```

Los tres módulos están enlazados por la tabla **`Inscripcion`**:

- `Inscripcion` → tiene un `plan_pago_id` (define cuántas cuotas) y un `monto_total` (precio del curso).
- `Pago` → uno o varios registros por inscripción (una fila por cuota).
- `ControlCertificacion` → un registro por inscripción, creado cuando el curso termina y se evalúa al alumno.

---

## 2. Modelo de datos

### 2.1 Tablas involucradas (DDL actual en `script_bd_postgresql.sql`)

**`MetodoPago`** — catálogo de formas de pago
```sql
CREATE TABLE MetodoPago (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(150),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
Hoy solo existen 2 filas activas en la lógica: **1 = Efectivo**, **2 = PagoFacil QR** (el id se usa "hardcodeado" en el código, no hay lookup por nombre en la validación).

**`PlanPago`** — planes de cuotas
```sql
CREATE TABLE PlanPago (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    numero_cuotas INT NOT NULL,
    estado VARCHAR(20) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
Ej: "Contado" (1 cuota), "3 Cuotas" (3 cuotas). No maneja intereses ni recargos, solo define en cuántas partes se divide el `monto_total`.

**`Pago`** — cada cuota pagada o por pagar
```sql
CREATE TABLE Pago (
    id SERIAL PRIMARY KEY,
    fecha DATE NOT NULL,
    monto FLOAT NOT NULL,
    nro_cuota INTEGER NOT NULL DEFAULT 1,
    id_transaccion VARCHAR(100),
    nro_pedido VARCHAR(50),
    estado_pago VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    correo_notificacion VARCHAR(150),
    notificado BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INTEGER NOT NULL REFERENCES Usuario(id),
    metodo_id INTEGER NOT NULL REFERENCES MetodoPago(id),
    inscripcion_id INTEGER NOT NULL REFERENCES Inscripcion(id)
);
```

**`ControlCertificacion`** — evaluación/certificado por inscripción
```sql
CREATE TABLE ControlCertificacion (
    id SERIAL PRIMARY KEY,
    nota FLOAT NOT NULL,
    estado_certificacion VARCHAR(50) NOT NULL,
    fecha_emision DATE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    inscripcion_id INTEGER NOT NULL REFERENCES Inscripcion(id)
);
```

**`Inscripcion`** (relevante para ambos módulos)
```sql
CREATE TABLE Inscripcion (
    id SERIAL PRIMARY KEY,
    fecha_inscripcion DATE NOT NULL,
    estado_inscripcion VARCHAR(20) NOT NULL,
    monto_total FLOAT NOT NULL,
    estudiante_id INTEGER NOT NULL REFERENCES Usuario(id),
    plan_pago_id INTEGER NOT NULL REFERENCES PlanPago(id),
    curso_id INTEGER NOT NULL REFERENCES Curso(id)
);
```

### 2.2 Relaciones (para pensar los modelos Eloquent)

```
Usuario 1───N Inscripcion N───1 PlanPago
Inscripcion 1───N Pago N───1 MetodoPago
Inscripcion 1───1 ControlCertificacion   (debería ser 1 a 1, hoy no está forzado por BD)
Inscripcion N───1 Curso
```

Sugerencia de migración Laravel: `Pago`, `MetodoPago`, `PlanPago`, `ControlCertificacion` como tablas/modelos propios (`Payment`, `PaymentMethod`, `PaymentPlan`, `CertificationControl` o mantener nombres en español si el resto del proyecto usa español).

---

## 3. Módulo de Pagos

### 3.1 Conceptos clave

- **Plan de pago**: determina en cuántas **cuotas** (`numero_cuotas`) se divide el `monto_total` de la inscripción.
- **Cálculo de cuota**: siempre equitativo, sin intereses:
  ```
  monto_cuota = monto_total / numero_cuotas
  ```
  (no hay ajuste de redondeo en la última cuota — si no divide exacto, todas las cuotas "pesan" igual y puede quedar un pequeño remanente sin cuadrar céntimo a céntimo).
- **Método de pago**: `1 = Efectivo`, `2 = PagoFacil (QR)`. Cualquier otro valor se rechaza.
- **Número de cuota**: se calcula automáticamente contando cuántos pagos con `estado_pago = 'pagado'` ya existen para esa inscripción, y sumando 1. No se puede pagar más cuotas que las que define el plan (si ya están todas pagadas, se rechaza el intento).

### 3.2 Estados de un pago

| Estado | Significado |
|---|---|
| `pendiente` | Se generó un QR pero el banco aún no confirma el pago. |
| `pagado` | Confirmado — inmediato en efectivo, o confirmado por PagoFacil en QR. |

Campo adicional `notificado` (boolean): evita mandar el correo de "pago confirmado" más de una vez.

### 3.3 Flujo A — Pago en efectivo

1. Se solicita registrar el pago de una inscripción con método `1`.
2. El sistema calcula la cuota (`nroCuota`, `montoCuota`) como se explicó arriba.
3. Se inserta el `Pago` directamente con `estado_pago = 'pagado'` y `notificado = true`.
4. No hay pasos posteriores: el pago queda cerrado en el mismo momento (pago presencial, en caja).

Este es el caso simple: **efectivo = confirmación inmediata**.

### 3.4 Flujo B — Pago con QR (PagoFacil)

Este es el flujo más importante a explicar porque tiene una integración externa y un paso asíncrono.

1. **Cálculo de cuota** — igual que en efectivo (`nroCuota`, `montoCuota`).
2. **Registro "pendiente"**: se inserta el `Pago` con `estado_pago = 'pendiente'` para reservar el registro y obtener un `id`.
3. **Número de operación único**: se arma un `paymentNumber` combinando timestamp actual + el id del pago (para que sea único frente a PagoFacil).
4. **Llamada a la pasarela PagoFacil (API REST, MasterQR)**:
   - **Autenticación**: `POST /api/services/v2/login` con headers `tcTokenService` y `tcTokenSecret` (credenciales fijas del comercio) → devuelve un `accessToken` (JWT).
   - **Generación del QR**: `POST /api/services/v2/generate-qr` con `Authorization: Bearer <token>` y un body JSON que incluye: nombre y documento del cliente, teléfono, correo, monto, moneda, código de cliente, detalle del "producto" (nombre del curso) y una `callbackUrl` a la que PagoFacil debe avisar cuando el pago se acredite.
   - La respuesta trae `transactionId` (id de la transacción en PagoFacil) y `qrBase64` (imagen PNG del código QR, en base64).
   - **Detalle importante para la migración**: el monto que se envía a la API se divide entre 1000 (`montoApi = montoCuota / 1000`). Esto es un ajuste de entorno de pruebas del proyecto actual (para no operar montos reales en el sandbox) — **no debería replicarse en producción**, hay que usar el monto real.
   - Si la llamada a PagoFacil falla, el pago `pendiente` que se había insertado se **elimina** (rollback manual) para no dejar basura en la tabla.
5. **Actualización del registro**: se guarda `id_transaccion` y `nro_pedido` (el `paymentNumber`) en el `Pago`, que sigue en estado `pendiente`.
6. **Entrega del QR al usuario**: se envía un correo con el código QR incrustado como imagen para que lo escanee desde su banca móvil.
7. **Confirmación (asíncrona)**: cuando el banco acredita el pago, PagoFacil debería notificar a la `callbackUrl` configurada. **Este callback NO está implementado en el sistema actual** — es un hueco funcional real: algo (manual o externo) tiene que poner `estado_pago = 'pagado'`.
8. **Aviso al usuario**: un proceso en segundo plano revisa cada cierto tiempo (actualmente cada 8 segundos) los pagos que ya están en `pagado` pero no `notificado`, y les manda un correo de confirmación, marcando `notificado = true`.

### 3.5 Diagrama del flujo QR

```
Usuario pide pagar cuota
        │
        ▼
Calcular nroCuota / montoCuota
        │
        ▼
INSERT Pago (estado='pendiente')  ──► id local
        │
        ▼
Login PagoFacil (token)
        │
        ▼
Generar QR (POST /generate-qr) ──► transactionId + imagen QR
        │  (si falla: DELETE del pago pendiente y error al usuario)
        ▼
UPDATE Pago SET id_transaccion, nro_pedido   (sigue "pendiente")
        │
        ▼
Enviar correo con QR
        │
        ▼
   ... el banco acredita ...
        │
        ▼
[FALTA] Webhook/callback de PagoFacil → UPDATE Pago SET estado_pago='pagado'
        │
        ▼
Proceso periódico detecta pagado+no-notificado → envía correo de confirmación → marca notificado
```

### 3.6 Reglas de negocio a preservar

1. Método de pago solo puede ser Efectivo o QR (validar contra catálogo real en la versión Laravel, no hardcodear ids).
2. No se puede pagar una cuota si ya se completaron todas las cuotas del plan.
3. Cálculo de cuota = `monto_total / numero_cuotas`, sin intereses ni mora.
4. Si la pasarela de pago falla, no debe quedar un registro de pago "fantasma" (pendiente sin transacción real detrás).
5. La notificación al alumno del pago confirmado debe evitar duplicados (equivalente al flag `notificado`).

### 3.7 Puntos que la versión Laravel debería corregir/mejorar (no son "features", son huecos del sistema actual)

- **Implementar el callback/webhook real de PagoFacil** (endpoint público que reciba la confirmación del banco y actualice el estado del pago). En Laravel: una ruta `POST /webhooks/pagofacil` sin CSRF, que valide la firma/token del proveedor y dispare un evento o job.
- Reemplazar el "sleep cada 8 segundos" por un enfoque basado en eventos: cuando el webhook marca el pago como `pagado`, se dispara inmediatamente la notificación (Job/Notification), sin polling.
- No dividir el monto entre 1000 — usar el monto real de producción.
- Las credenciales de PagoFacil deben ir en `.env`, no en código.

---

## 4. Módulo de Control de Certificación

### 4.1 Qué es

Cuando el curso termina, se registra la **nota final** del alumno para esa inscripción. Según la nota, el sistema decide si emite un certificado en PDF o no, y en ambos casos libera el cupo del curso.

### 4.2 Regla central: nota mínima = 70

```
si nota < 70:
    liberar el curso (vuelve a estar disponible para otro alumno)
    rechazar la operación (no se crea ningún registro de certificación)
    → el alumno NO puede reintentar "aprobar" esa misma inscripción después

si nota >= 70:
    crear registro en ControlCertificacion (nota, estado, fecha_emision)
    generar el PDF del certificado
    enviar el PDF por correo
    liberar el curso (vuelve a estar disponible para otro alumno)
```

Puntos importantes para no perder al migrar:

- El umbral **70** está fijo en el código (no es configurable desde base de datos). Conviene decidir si en Laravel se deja como constante o se vuelve configurable.
- **Si la nota es insuficiente, no queda ningún rastro en `ControlCertificacion`** — solo un mensaje de error al momento de intentarlo. Esto significa que hoy **no hay histórico de intentos reprobados**. Es una limitación a valorar: quizás en la versión web sí conviene guardar el intento reprobado (con estado "reprobado") en vez de simplemente rechazar, para tener trazabilidad.
- El campo `estado_certificacion` es un texto libre ("aprobado", "reprobado", "pendiente"...) — no hay una lista cerrada de valores en el sistema actual. En Laravel conviene modelarlo como un enum controlado.
- No hay restricción de unicidad: técnicamente se podría certificar dos veces la misma inscripción. En la versión nueva conviene forzar **una certificación por inscripción** (constraint único).
- Liberar el curso significa volver a poner el curso en estado "disponible" para que otro estudiante pueda inscribirse en ese cupo.

### 4.3 Datos que componen un certificado

Al generar el certificado se recopila (con joins a Usuario/Curso/Instructor):

- Nombre completo del estudiante
- Número de documento (cédula)
- Nombre del curso
- Nota (con 1 decimal)
- Estado de certificación
- Fecha de emisión
- Nombre del instructor
- Fecha de inicio y fin del curso
- Un número de referencia (el id del registro de certificación)

### 4.4 Generación del PDF

- Se usa una librería de generación de PDF (iText en el sistema actual; en Laravel el equivalente típico sería `barryvdh/laravel-dompdf` o `spatie/browsershot`/`laravel-snappy` si se quiere maquetar con Blade/HTML).
- El PDF tiene: imagen de fondo con membrete institucional, título ("CERTIFICADO DE APROBACIÓN" o variante según el estado), nombre del alumno en mayúsculas, cédula, nombre del curso, nota y calificación, período del curso (fecha inicio–fin), nombre del instructor, fecha de emisión y lugar, y el número de referencia del certificado.
- El PDF se genera en memoria (no se guarda a disco) y se adjunta directamente al correo. En la versión Laravel, conviene decidir si además se **almacena** (ej. en storage/S3) para poder volver a descargarlo desde la interfaz web sin regenerarlo.

### 4.5 Entrega del certificado

- Se arma un correo con cuerpo HTML (resumen de los datos) + el PDF adjunto, y se envía al correo del alumno.
- Si por algún motivo falla la generación del PDF, el sistema igual avisa que "la certificación quedó registrada", para no perder la operación de negocio aunque falle el documento. Es una decisión de diseño a mantener: **la certificación en base de datos no depende de que el PDF se genere correctamente.**

### 4.6 Diagrama del flujo de certificación

```
Registrar nota de una inscripción
        │
        ▼
   nota < 70? ────────────► Sí ──► liberar curso ──► rechazar (sin registro)
        │
        No
        ▼
INSERT ControlCertificacion (nota, estado, fecha_emision)
        │
        ▼
Obtener datos completos (alumno, curso, instructor, fechas)
        │
        ▼
Liberar curso (disponible de nuevo)
        │
        ▼
Generar PDF del certificado
        │
        ▼
Enviar correo con PDF adjunto
```

---

## 5. Flujo integral (de punta a punta)

```
1) Inscripción del alumno en un curso
        → define monto_total y plan de pago (número de cuotas)

2) Pago de cuotas (una o varias veces, según el plan)
        → efectivo: confirmación inmediata
        → QR: pendiente → confirmación por PagoFacil (webhook) → aviso al alumno

3) Fin del curso → registro de nota / certificación
        → nota >= 70: certificado PDF + correo + libera curso
        → nota < 70: solo libera curso, no hay certificado
```

El pago **no bloquea** hoy la certificación (no hay una validación de "todas las cuotas pagadas" como requisito para poder certificar) — son dos procesos independientes que comparten la misma `Inscripcion`. Vale la pena confirmar con el equipo si esa regla ("no se puede certificar si no pagó todo") debería agregarse en la versión web, ya que actualmente no existe.

---

## 6. Notas para el equipo de Laravel/Inertia/Vue

Resumen de piezas a construir en el nuevo stack, mapeadas a lo que hace el sistema actual:

| Concepto actual (Java) | Equivalente sugerido en Laravel |
|---|---|
| Comando de email `INSPAG` | Endpoint `POST /inscripciones/{id}/pagos` (form Inertia con selección de método de pago) |
| `NPago.guardar()` | `PaymentService::create()` / Form Request con las mismas validaciones (método válido, cuotas no completas) |
| `PagoFacilService` (llamadas REST) | Cliente HTTP (`Http::` facade) o un paquete dedicado, credenciales en `.env` |
| Callback inexistente de PagoFacil | Ruta webhook pública + verificación de firma + Job que actualiza el pago y dispara notificación |
| `PagoNotificacionThread` (polling 8s) | Eliminado — reemplazado por evento disparado desde el webhook (colas/Jobs) |
| `SendEmailThread` | `Mailable` + `Notification` de Laravel (con cola) |
| Comando `INSCRT` | Endpoint `POST /inscripciones/{id}/certificacion` |
| `NControlCert.guardar()` (nota >= 70) | `CertificationService::create()` con la misma regla de negocio |
| `CertificadoPdfBuilder` | Vista Blade + `barryvdh/laravel-dompdf` (o similar) para generar el PDF desde una plantilla HTML |
| Envío de PDF por correo | `Mailable` con `attachData()` del PDF generado |

Esto te da el guion para explicarle al equipo: **qué reglas de negocio hay que respetar** (cálculo de cuotas, estados de pago, umbral de nota, liberación de curso, no duplicar notificaciones) y **qué huecos del sistema original conviene cerrar** al construir la versión web (webhook real de pagos, trazabilidad de certificaciones reprobadas, unicidad de certificación por inscripción, credenciales fuera del código).

---

## 7. Referencia rápida de comandos actuales (para contraste con los nuevos endpoints)

| Comando email | Equivale a | Parámetros |
|---|---|---|
| `INSPAG["id_inscripcion","id_metodo"]` | Registrar pago de una cuota | `id_metodo`: 1=Efectivo, 2=QR |
| `MODPAG["id","fecha","monto"]` | Corregir un pago | — |
| `DELPAG["id"]` | Eliminar un pago | — |
| `LISPAG["*"]` | Listar pagos | — |
| `INSCRT["id_inscripcion","nota","estado","fecha_emision"]` | Registrar certificación | nota >= 70 para que se emita |
| `MODCRT["id","nota","estado","fecha_emision"]` | Corregir certificación | — |
| `DELCRT["id"]` | Eliminar certificación | — |
| `LISCRT["*"]` | Listar certificaciones | — |
