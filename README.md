# Escuela de Conducción

Sistema web para la gestión integral de una escuela de conducción. La aplicación permite administrar cursos, estudiantes, inscripciones, reservas, pagos, usuarios, permisos y procesos de certificación.

> Proyecto académico desarrollado para la materia **INF513 - Sistemas de Aplicación**, carrera de Ingeniería en Sistemas, Universidad Autónoma Gabriel René Moreno (U.A.G.R.M.). Construido con Laravel, Inertia.js y Vue.js.

## Vista general

El sistema acompaña el flujo principal de una escuela de conducción:

```text
Estudiante se registra
        ↓
Se inscribe en un curso
        ↓
Realiza el pago de una o varias cuotas
        ↓
Completa el curso y registra su calificación
        ↓
Se genera el certificado en PDF
```

## Funcionalidades

### Gestión académica

- Administración de cursos y tipos de curso.
- Gestión de vehículos y tipos de vehículo.
- Configuración de franjas horarias.
- Registro y seguimiento de inscripciones.
- Gestión de reservas de clases o turnos.

### Usuarios y permisos

- Autenticación de usuarios.
- Gestión de perfiles.
- Roles y permisos mediante `spatie/laravel-permission`.
- Acceso diferenciado para propietarios, secretaría, instructores y estudiantes.
- Bitácora de operaciones importantes.

### Pagos

- Configuración de planes de pago y cuotas.
- Registro de pagos en efectivo.
- Seguimiento del estado de los pagos.
- Generación de recibos.
- Integración con PagoFácil para pagos mediante QR.
- Recepción de callbacks de la pasarela de pago.

### Certificación

- Registro de la nota final del estudiante.
- Validación de la nota mínima de aprobación.
- Generación de certificados en PDF.
- Consulta y descarga de certificados.

### Reportes y dashboard

- Panel principal con información resumida del sistema.
- Búsqueda de registros.
- Reportes administrativos.
- Gráficos utilizando Chart.js y vue-chartjs.

## Tecnologías utilizadas

### Backend

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- Laravel Breeze
- Eloquent ORM
- Laravel DomPDF
- Spatie Laravel Permission
- Tighten Ziggy

### Frontend

- Vue 3
- Inertia.js 2
- Vite
- Tailwind CSS
- Chart.js
- Vue Chart.js

### Base de datos

- PostgreSQL — scripts de base de datos incluidos en el repositorio.
- SQLite — configuración utilizada por defecto en `.env.example` para facilitar el desarrollo local.

## Arquitectura del proyecto

```text
escuela/
├── app/
│   ├── Http/Controllers/     # Controladores y flujo HTTP
│   ├── Models/               # Modelos Eloquent del dominio
│   ├── Observers/            # Observadores de eventos de modelos
│   ├── Providers/            # Proveedores de servicios
│   └── Services/             # Servicios reutilizables de negocio
├── bootstrap/                # Inicialización de Laravel
├── config/                   # Configuración de la aplicación
├── database/                 # Migraciones, factories y seeders
├── lang/                     # Archivos de traducción
├── public/                   # Punto de entrada y assets públicos
├── resources/
│   ├── css/                  # Estilos de la aplicación
│   ├── js/                   # Aplicación Vue e Inertia
│   └── views/                # Plantilla Blade principal y PDFs
├── routes/
│   ├── auth.php              # Rutas de autenticación
│   ├── console.php           # Comandos Artisan
│   └── web.php               # Rutas web de la aplicación
├── storage/                  # Logs, archivos y caché
├── tests/                    # Pruebas automatizadas
├── composer.json             # Dependencias PHP
├── package.json              # Dependencias frontend
└── vite.config.js            # Configuración de Vite
```

## Modelos principales

El dominio de la aplicación se representa principalmente mediante los siguientes modelos:

- `Usuario`: usuarios del sistema y estudiantes.
- `Rol`: roles y permisos de acceso.
- `Curso`: cursos ofrecidos por la escuela.
- `TipoCurso`: clasificación de cursos.
- `Inscripcion`: relación entre un estudiante y un curso.
- `Reserva`: reserva de horarios o clases.
- `FranjaHoraria`: disponibilidad de horarios.
- `Vehiculo`: vehículos utilizados en la formación.
- `TipoVehiculo`: clasificación de vehículos.
- `PlanPago`: cantidad de cuotas y condiciones de pago.
- `CuotaPlanPago`: cuotas asociadas a un plan.
- `Pago`: pagos realizados por una inscripción.
- `MetodoPago`: catálogo de métodos de pago.
- `ControlCertificacion`: calificación y estado de certificación.
- `Bitacora`: registro de actividades relevantes.

## Requisitos

Antes de instalar el proyecto, asegúrate de tener:

- PHP >= 8.3
- Composer
- Node.js y npm
- PostgreSQL o SQLite
- Extensiones PHP requeridas por Laravel

## Instalación local

1. Clona el repositorio:

```bash
git clone https://github.com/jdguior06/escuela.git
cd escuela
```

2. Instala las dependencias de PHP:

```bash
composer install
```

3. Crea el archivo de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configura la conexión a la base de datos en `.env`.

### Opción rápida con SQLite

Crea el archivo de base de datos y utiliza una configuración similar a esta:

```env
DB_CONNECTION=sqlite
# DB_DATABASE=/ruta/absoluta/al/proyecto/database/database.sqlite
```

```bash
touch database/database.sqlite
```

### Opción con PostgreSQL

Configura las variables de conexión según tu instalación:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=escuela
DB_USERNAME=postgres
DB_PASSWORD=tu contraseña
```

Si utilizas el script SQL incluido en el repositorio, puedes ejecutarlo desde PostgreSQL:

```bash
psql -U postgres -d escuela -f script_bd_postgresql_actualizado.sql
```

5. Ejecuta las migraciones y, si corresponde, los seeders:

```bash
php artisan migrate
php artisan db:seed
```

6. Instala las dependencias frontend:

```bash
npm install
```

7. Inicia el servidor de desarrollo frontend:

```bash
npm run dev
```

En otra terminal, inicia Laravel:

```bash
php artisan serve
```

La aplicación estará disponible normalmente en:

```text
http://127.0.0.1:8000
```

## Comandos útiles

```bash
# Servidor de desarrollo Laravel
php artisan serve

# Compilar assets para producción
npm run build

# Ejecutar pruebas
php artisan test

# Limpiar cachés
php artisan optimize:clear

# Ver las rutas registradas
php artisan route:list
```

También puedes utilizar el script de configuración incluido en `composer.json`:

```bash
composer run setup
```

## Configuración de PagoFácil

La integración requiere credenciales proporcionadas por PagoFácil. No incluyas credenciales reales en el repositorio.

Agrega las variables correspondientes en `.env`:

```env
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo/api/services/v2
PAGOFACIL_TOKEN_SERVICE=tu_token_de_servicio
PAGOFACIL_TOKEN_SECRET=tu_token_secreto
PAGOFACIL_CLIENT_CODE=tu_codigo_de_cliente
PAGOFACIL_CALLBACK_URL="${APP_URL}/pagofacil/callback"
PAGOFACIL_SANDBOX_DIVISOR=1000
```

El flujo de pago contempla:

1. Creación del pago en estado pendiente.
2. Comunicación con PagoFácil.
3. Confirmación mediante callback.
4. Actualización del estado del pago.
5. Retorno del usuario a la aplicación.

## Roles del sistema

El acceso a los módulos se controla mediante roles. Entre los perfiles contemplados están:

- **Propietario:** administración general, usuarios, configuración, reportes y bitácora.
- **Secretaria:** gestión operativa de usuarios, cursos y pagos.
- **Instructor:** control de certificaciones.
- **Estudiante:** inscripciones, reservas, pagos y consulta de certificación.

## Flujo de certificación

La certificación se genera a partir de la calificación final del estudiante. La nota mínima configurada actualmente es `70` y se define mediante:

```env
CERTIFICACION_NOTA_MINIMA=70
```

Cuando el estudiante cumple el criterio de aprobación, el sistema puede generar el certificado en PDF para su consulta o descarga.

## Estado del proyecto

Proyecto en desarrollo. La base funcional incluye autenticación, gestión de usuarios y roles, cursos, inscripciones, reservas, pagos, certificación, reportes e integración con una pasarela de pago.

## Próximas mejoras

- Incorporar pruebas automatizadas para los flujos críticos.
- Añadir datos demo para facilitar la instalación y presentación.
- Completar la documentación visual con capturas de pantalla.
- Añadir CI/CD para ejecutar pruebas y revisar la calidad del código automáticamente.

## Autor

**Juan Diego Guirapoigua Oregua**
GitHub: [@jdguior06](https://github.com/jdguior06)

Proyecto desarrollado como parte de la formación académica en Ingeniería en Sistemas, U.A.G.R.M.