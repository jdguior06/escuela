-- =====================================================
-- SCRIPT DE BASE DE DATOS PARA POSTGRESQL (ACTUALIZADO)
-- Sistema de Gestión Autoescuela
-- Generado a partir del estado actual de las migraciones de Laravel
-- Este script es solo de referencia/documentación: la base real
-- se crea y versiona con `php artisan migrate`.
-- =====================================================

-- =====================================================
-- ELIMINAR TABLAS SI EXISTEN (OPCIONAL - SOLO PARA REINICIAR)
-- =====================================================
DROP TABLE IF EXISTS visita_pagina CASCADE;
DROP TABLE IF EXISTS bitacora CASCADE;
DROP TABLE IF EXISTS rol_menu CASCADE;
DROP TABLE IF EXISTS menu CASCADE;
DROP TABLE IF EXISTS cuota_plan_pago CASCADE;
DROP TABLE IF EXISTS reserva CASCADE;
DROP TABLE IF EXISTS control_certificacion CASCADE;
DROP TABLE IF EXISTS pago CASCADE;
DROP TABLE IF EXISTS inscripcion CASCADE;
DROP TABLE IF EXISTS curso CASCADE;
DROP TABLE IF EXISTS vehiculo CASCADE;
DROP TABLE IF EXISTS tipo_curso CASCADE;
DROP TABLE IF EXISTS tipo_vehiculo CASCADE;
DROP TABLE IF EXISTS franja_horaria CASCADE;
DROP TABLE IF EXISTS plan_pago CASCADE;
DROP TABLE IF EXISTS metodo_pago CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS usuario CASCADE;
DROP TABLE IF EXISTS rol CASCADE;
DROP TABLE IF EXISTS role_has_permissions CASCADE;
DROP TABLE IF EXISTS model_has_roles CASCADE;
DROP TABLE IF EXISTS model_has_permissions CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS permissions CASCADE;
DROP TABLE IF EXISTS failed_jobs CASCADE;
DROP TABLE IF EXISTS job_batches CASCADE;
DROP TABLE IF EXISTS jobs CASCADE;
DROP TABLE IF EXISTS cache_locks CASCADE;
DROP TABLE IF EXISTS cache CASCADE;

-- =====================================================
-- INFRAESTRUCTURA DEL FRAMEWORK (Laravel)
-- No son tablas de negocio: las requiere el framework para
-- funcionar (caché interno, cola de trabajos en segundo plano).
-- =====================================================

CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration BIGINT NOT NULL
);
CREATE INDEX idx_cache_expiration ON cache (expiration);

CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration BIGINT NOT NULL
);
CREATE INDEX idx_cache_locks_expiration ON cache_locks (expiration);

CREATE TABLE jobs (
    id SERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX idx_jobs_queue ON jobs (queue);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT,
    cancelled_at INTEGER,
    created_at INTEGER NOT NULL,
    finished_at INTEGER
);

CREATE TABLE failed_jobs (
    id SERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_failed_jobs_connection_queue_failed_at ON failed_jobs (connection, queue, failed_at);

-- =====================================================
-- PERMISOS (paquete Spatie Laravel-Permission)
-- Sistema de permisos granular a nivel de código, independiente
-- del modelo de negocio Rol/Menu/RolMenu definido más abajo.
-- =====================================================

CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (name, guard_name)
);

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE (name, guard_name)
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions (id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);
CREATE INDEX idx_model_has_permissions_model ON model_has_permissions (model_id, model_type);

CREATE TABLE model_has_roles (
    role_id BIGINT NOT NULL REFERENCES roles (id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);
CREATE INDEX idx_model_has_roles_model ON model_has_roles (model_id, model_type);

CREATE TABLE role_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions (id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles (id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- =====================================================
-- TABLAS DE NEGOCIO ORIGINALES
-- Mismas columnas, tipos y relaciones que el script original;
-- solo cambian de nombre a snake_case por convención de Laravel/Eloquent.
-- =====================================================

CREATE TABLE rol (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(150),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (Usuario)
-- remember_token: columna estándar de Laravel para la función "recordarme" del login.
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    genero VARCHAR(1),
    nro_documento VARCHAR(20) UNIQUE NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    estado_usuario VARCHAR(20) DEFAULT 'activo',
    remember_token VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rol_id INTEGER NOT NULL REFERENCES rol (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tablas estándar de Laravel para recuperación de contraseña y sesiones.
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX idx_sessions_user_id ON sessions (user_id);
CREATE INDEX idx_sessions_last_activity ON sessions (last_activity);

-- Tabla: (MetodoPago)
CREATE TABLE metodo_pago (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(150),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (PlanPago)
CREATE TABLE plan_pago (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    numero_cuotas INT NOT NULL,
    estado VARCHAR(20) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (FranjaHoraria)
CREATE TABLE franja_horaria (
    id SERIAL PRIMARY KEY,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (TipoVehiculo)
CREATE TABLE tipo_vehiculo (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(150),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (TipoCurso)
CREATE TABLE tipo_curso (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    precio FLOAT NOT NULL,
    estado_curso VARCHAR(20) NOT NULL,
    duracion_horas INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_vehiculo_id INTEGER NOT NULL REFERENCES tipo_vehiculo (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (Vehiculo)
CREATE TABLE vehiculo (
    id SERIAL PRIMARY KEY,
    placa VARCHAR(20) UNIQUE NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    estado_vehiculo VARCHAR(20) NOT NULL,
    fecha_mantenimiento DATE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_vehiculo_id INTEGER NOT NULL REFERENCES tipo_vehiculo (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (Curso)
-- estado_curso: 'disponible' | 'reservado' | 'inscrito' | 'cancelado'
-- Ciclo: disponible → reservado → inscrito → disponible (al emitir certificado)
CREATE TABLE curso (
    id SERIAL PRIMARY KEY,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    precio_final FLOAT NOT NULL,
    estado_curso VARCHAR(20) NOT NULL DEFAULT 'disponible',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    instructor_id INTEGER NOT NULL REFERENCES usuario (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    vehiculo_id INTEGER NOT NULL REFERENCES vehiculo (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    tipo_curso_id INTEGER NOT NULL REFERENCES tipo_curso (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    franja_horaria_id INTEGER NOT NULL REFERENCES franja_horaria (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (Inscripcion)
CREATE TABLE inscripcion (
    id SERIAL PRIMARY KEY,
    fecha_inscripcion DATE NOT NULL,
    estado_inscripcion VARCHAR(20) NOT NULL,
    monto_total FLOAT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estudiante_id INTEGER NOT NULL REFERENCES usuario (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    plan_pago_id INTEGER NOT NULL REFERENCES plan_pago (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    curso_id INTEGER NOT NULL REFERENCES curso (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (Pago)
CREATE TABLE pago (
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
    usuario_id INTEGER NOT NULL REFERENCES usuario (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    metodo_id INTEGER NOT NULL REFERENCES metodo_pago (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    inscripcion_id INTEGER NOT NULL REFERENCES inscripcion (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (ControlCertificacion)
-- pdf_path: ruta del PDF del certificado generado.
-- UNIQUE(inscripcion_id): evita que una misma inscripción tenga dos certificados.
CREATE TABLE control_certificacion (
    id SERIAL PRIMARY KEY,
    nota FLOAT NOT NULL,
    estado_certificacion VARCHAR(50) NOT NULL,
    fecha_emision DATE,
    pdf_path VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    inscripcion_id INTEGER NOT NULL UNIQUE REFERENCES inscripcion (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- =====================================================
-- TABLAS DE NEGOCIO NUEVAS
-- No existían en el script original; se agregaron para
-- funcionalidades incorporadas en fases posteriores del proyecto.
-- =====================================================

-- Tabla: (Reserva)
-- Quién reservó un curso vive únicamente aquí (usuario_id), junto con
-- fecha de reserva, fecha de vencimiento y estado, para poder liberar
-- automáticamente el curso si el estudiante no confirma a tiempo.
-- (Curso.reservado_por existió brevemente y se eliminó por ser
-- redundante con esta tabla; nunca se llegó a consultar en el código.)
CREATE TABLE reserva (
    id SERIAL PRIMARY KEY,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento TIMESTAMP NOT NULL,
    estado_reserva VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    usuario_id INTEGER NOT NULL REFERENCES usuario (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    curso_id INTEGER NOT NULL REFERENCES curso (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla: (CuotaPlanPago)
-- El script original solo registraba pagos ya realizados (Pago.nro_cuota).
-- Esta tabla planifica por adelantado cuántas cuotas debe pagar el
-- estudiante, su monto y su fecha de vencimiento, según el PlanPago elegido.
CREATE TABLE cuota_plan_pago (
    id SERIAL PRIMARY KEY,
    inscripcion_id INTEGER NOT NULL REFERENCES inscripcion (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    nro_cuota INTEGER NOT NULL,
    monto FLOAT NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado_cuota VARCHAR(20) NOT NULL DEFAULT 'pendiente'
);

-- Tabla: (Menu)
-- Menú de navegación dinámico. grupo: agrupa las opciones del menú
-- por sección (agregado en una migración posterior).
CREATE TABLE menu (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ruta VARCHAR(150) NOT NULL,
    icono VARCHAR(50),
    grupo VARCHAR(30),
    orden INTEGER NOT NULL DEFAULT 0,
    padre_id INTEGER REFERENCES menu (id) ON DELETE SET NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

-- Tabla: (RolMenu)
-- Pivote que define qué opciones de menú ve cada rol.
CREATE TABLE rol_menu (
    rol_id INTEGER NOT NULL REFERENCES rol (id) ON DELETE CASCADE,
    menu_id INTEGER NOT NULL REFERENCES menu (id) ON DELETE CASCADE,
    PRIMARY KEY (rol_id, menu_id)
);

-- Tabla: (Bitacora)
-- Auditoría de eventos del sistema (login, acciones relevantes) por usuario.
CREATE TABLE bitacora (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuario (id) ON DELETE SET NULL,
    tipo_evento VARCHAR(30) NOT NULL,
    recurso VARCHAR(150),
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: (VisitaPagina)
-- Contador de visitas por página, usado en reportes y estadísticas.
CREATE TABLE visita_pagina (
    id SERIAL PRIMARY KEY,
    pagina VARCHAR(150) UNIQUE NOT NULL,
    contador INTEGER NOT NULL DEFAULT 0,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;
