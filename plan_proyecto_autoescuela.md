# Plan de Proyecto — Sistema de Gestión de Autoescuela

**Stack:** Laravel 11 + Inertia.js + Vue 3 + PostgreSQL
**Arquitectura:** 3 capas (Presentación=Vue/Inertia, Lógica=Laravel Controllers/Services, Datos=PostgreSQL vía Eloquent)
**Grupo:** grupo13sa
**Entorno:** Local (Fedora/Rocky), sin depender de tecnoweb13sa.online

---

## 1. Roles del sistema

| Rol | Tipo | Responsabilidades principales |
|---|---|---|
| **Propietario (Administrador)** | No cuenta como rol de negocio | Acceso total, reportes/estadísticas, configuración de catálogos (tipos de curso, vehículos, métodos de pago) |
| **Secretaria** | Rol de negocio | Gestión de usuarios, reservas, inscripciones, pagos, atención al cliente |
| **Instructor** | Rol de negocio | Ve sus cursos asignados, registra control y certificación (notas, asistencia) |
| **Estudiante** | Rol de negocio | Reserva cursos, se inscribe, paga, consulta su progreso y certificación |

Con Secretaria + Instructor + Estudiante ya se cumplen y se superan los "2 roles de negocio" mínimos exigidos.

---

## 2. Mapeo de Casos de Uso → Módulos → Roles

| CU | Módulo | Roles con acceso |
|---|---|---|
| CU-01 Gestión de Usuarios | `usuarios` | Propietario, Secretaria |
| CU-02 Gestión de Vehículos | `vehiculos` | Propietario, Secretaria |
| CU-03 Gestión de Reservas | `reservas` | Secretaria, Estudiante (propia) |
| CU-04 Gestión de Cursos | `cursos` | Propietario, Secretaria, Instructor (lectura) |
| CU-05 Gestión de Inscripciones | `inscripciones` | Secretaria, Estudiante (propia) |
| CU-06 Control y Certificación | `control-certificacion` | Instructor, Propietario |
| CU-07 Gestión de Pagos | `pagos` | Secretaria, Estudiante (propios), Propietario |
| CU-08 Reportes y Estadísticas | `reportes` | Propietario |

---

## 3. Cambios necesarios al esquema de base de datos

Tu script `script_bd_postgresql.sql` cubre bien el núcleo del negocio, pero le faltan tablas para los requisitos transversales (menú dinámico, matriz de acceso, bitácora, reservas, visitas, cuotas). Se agregan:

```sql
-- Reservas (CU-03, hoy solo existe como estado_curso='reservado', necesita ser entidad)
CREATE TABLE Reserva (
    id SERIAL PRIMARY KEY,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento TIMESTAMP NOT NULL,
    estado_reserva VARCHAR(20) NOT NULL DEFAULT 'pendiente', -- pendiente, confirmada, cancelada, vencida
    usuario_id INTEGER NOT NULL REFERENCES Usuario(id),
    curso_id INTEGER NOT NULL REFERENCES Curso(id)
);

-- Menú dinámico (requisito #2)
CREATE TABLE Menu (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ruta VARCHAR(150) NOT NULL,
    icono VARCHAR(50),
    orden INT DEFAULT 0,
    padre_id INTEGER REFERENCES Menu(id),
    activo BOOLEAN DEFAULT TRUE
);
CREATE TABLE RolMenu (
    rol_id INTEGER NOT NULL REFERENCES Rol(id),
    menu_id INTEGER NOT NULL REFERENCES Menu(id),
    PRIMARY KEY (rol_id, menu_id)
);

-- Bitácora (requisito #4)
CREATE TABLE Bitacora (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES Usuario(id), -- null si login fallido con correo inexistente
    tipo_evento VARCHAR(30) NOT NULL, -- login_exitoso, login_fallido, acceso_recurso
    recurso VARCHAR(150),
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contador de visitas por página (requisito #7)
CREATE TABLE VisitaPagina (
    id SERIAL PRIMARY KEY,
    pagina VARCHAR(150) UNIQUE NOT NULL,
    contador INTEGER DEFAULT 0,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cuotas planificadas (complementa PlanPago/Pago, requisito #10)
CREATE TABLE CuotaPlanPago (
    id SERIAL PRIMARY KEY,
    inscripcion_id INTEGER NOT NULL REFERENCES Inscripcion(id),
    nro_cuota INTEGER NOT NULL,
    monto FLOAT NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado_cuota VARCHAR(20) DEFAULT 'pendiente' -- pendiente, pagada, vencida
);
```

**Nota sobre matriz de acceso:** en vez de crear `Permiso`/`RolPermiso` a mano, se recomienda usar el paquete **Spatie Laravel-Permission**. Ahorra muchísimo tiempo, ya trae middleware de autorización y es el estándar en proyectos Laravel — cumple el requisito igual (roles y permisos en base de datos) sin reinventar la rueda.

**Nota sobre Pago/PagoFácil:** tu tabla `Pago` ya tiene `id_transaccion`, `nro_pedido`, `correo_notificacion`, `notificado` — esos campos calzan perfectamente con el flujo de PagoFácil (QR + notificación por correo/webhook). Para la fase inicial se simula el pago (formulario que marca `estado_pago = 'pagado'`), dejando la estructura lista para conectar la API real de PagoFácil después.

**Nota sobre temas/accesibilidad (requisito #5):** se recomienda manejarlo en frontend (Vue + `localStorage` + atributo `data-theme` en `<html>`, variables CSS) en vez de tabla en BD — es más rápido de implementar y cumple el requisito igual. Si luego quieres persistirlo por usuario, se agrega `PreferenciaUsuario` sin gran esfuerzo.

---

## 4. Requisitos transversales — cómo se resuelven

| # | Requisito | Solución técnica |
|---|---|---|
| 1 | Diseño y navegación | Layout Inertia compartido (`AppLayout.vue`) con header, sidebar de menú dinámico, footer |
| 2 | Roles + menú dinámico | Spatie Permission + tablas `Menu`/`RolMenu`, prop compartida de Inertia (`HandleInertiaRequests`) |
| 4 | Matriz de acceso + Bitácora | Spatie Permission (matriz) + `Bitacora` con listeners de eventos `Login`/`Failed` + middleware de log de acceso a recursos |
| 5 | Temas + accesibilidad | Vue store (Pinia) + CSS variables + `localStorage`; 3 temas visuales + modo día/noche automático por hora + control de tamaño de letra/contraste |
| 6 | Validación en español | Laravel Form Requests con mensajes personalizados en `resources/lang/es/validation.php` |
| 7 | Contador de visitas | Middleware que incrementa `VisitaPagina` por ruta, mostrado en footer vía prop compartida |
| 8 | Estadísticas | Módulo `reportes` — cursos más vendidos, ingresos por mes, tasa de aprobación, roles/recursos más accedidos (de Bitácora) |
| 9 | Búsqueda en header | Endpoint de búsqueda global (cursos, instructores, vehículos) con Inertia partial reloads |
| 10 | Pagos electrónicos | `PlanPago` + `CuotaPlanPago` + `Pago` (simulado ahora, PagoFácil después) |

---

## 5. Fases de implementación sugeridas

1. **Setup base** — Laravel + Breeze (Inertia+Vue) + PostgreSQL + Spatie Permission + migraciones completas + seeders (roles, catálogos)
2. **CRUDs núcleo** — Usuario, Vehículo, TipoCurso, Curso, FranjaHoraria, MetodoPago, PlanPago
3. **Flujo de negocio** — Reservas → Inscripciones → Pagos (simulado) → Control/Certificación
4. **Transversales** — Menú dinámico, Bitácora, contador de visitas, búsqueda global
5. **Estilo y accesibilidad** — 3 temas + modo día/noche + accesibilidad (Tailwind + CSS vars)
6. **Reportes y estadísticas**
7. **Pulido final** — validaciones en español completas, pruebas de flujo end-to-end

---

## 6. Decisión: tabla de autenticación

Se usa la tabla de negocio `Usuario` como tabla de autenticación de Laravel, **en vez de** la tabla `users` genérica que trae el scaffolding de Breeze. Cambios que esto implica:

1. La migración `create_users_table` se reemplaza/adapta para crear `Usuario` con todos sus campos (`rol_id`, `nro_documento`, `correo`, etc.), en vez de la tabla genérica.
2. `app/Models/User.php` (o se renombra a `Usuario.php`):
   - `protected $table = 'usuario';`
   - `$fillable` con los campos reales del negocio.
   - Login por `correo` en vez de `email` (override en el `LoginRequest` de Breeze).
3. Vistas de Breeze (`Login.vue`, `Register.vue`, `Profile/*.vue`) adaptadas de `name`/`email` a `nombre`/`apellido`/`correo`.
4. Las tablas `cache`, `sessions`, `jobs` **sí se quedan** como las trae Laravel (no tienen relación con este cambio).
5. Las FK existentes (`Curso.instructor_id`, `Inscripcion.estudiante_id`, `Pago.usuario_id`, etc.) no cambian, solo cambia el nombre de la tabla que referencian.

## 7. Próximo paso

Con este plan como contexto, en Claude Code seguimos con:
1. Scaffolding del proyecto Laravel+Inertia+Vue
2. Migraciones basadas en el esquema ampliado de arriba
3. Seeders de roles y catálogos
4. Implementación módulo por módulo siguiendo el orden de fases
