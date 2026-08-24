# Plan de Desarrollo — Sistema de Reportería y Monitoreo CAD
**Proyecto:** ViperCAD Reports & Monitor  
**Stack:** Laravel · Filament · Livewire · Laravel Reverb · SQLite · SQL Server (ViperCAD_Log)  
**Fecha de creación:** 2026-08-24  
**Responsable:** Por definir  

---

## Resumen de Herramientas

| Capa | Tecnología | Propósito |
|---|---|---|
| Framework Backend | Laravel 11+ | Lógica de negocio, queries, scheduling |
| Panel UI | Filament 3/4 | Tablas, filtros, formularios, dashboards |
| Reactividad | Livewire 3 | Componentes dinámicos sin JavaScript manual |
| Tiempo Real | Laravel Reverb | WebSockets para monitoreo en vivo |
| DB Local (App) | SQLite | Usuarios, roles, reportes guardados, bitácora |
| DB Producción | SQL Server (ViperCAD_Log) | Fuente de datos del CAD TiburonCAD |
| Exportación | Laravel Excel / DomPDF | Descarga de reportes en Excel y PDF |

---

## Etapas del Proyecto

```
Etapa 1 → Fundación del Proyecto          (Semana 1–2)
Etapa 2 → Gestión de Usuarios y Roles     (Semana 2–3)
Etapa 3 → Consultas y Servicios de Datos  (Semana 3–5)
Etapa 4 → Módulo de Reportes              (Semana 5–8)
Etapa 5 → Módulo de Monitoreo en Vivo     (Semana 8–10)
Etapa 6 → Exportación y Notificaciones    (Semana 10–12)
Etapa 7 → Pruebas, Ajustes y Producción   (Semana 12–14)
```

---

## Etapa 1 — Fundación del Proyecto
> **Objetivo:** Tener el proyecto Laravel corriendo con la doble conexión de base de datos, Filament instalado y la estructura de carpetas definida.

### Tareas

- [ ] **1.1** Crear proyecto Laravel 11 con Composer
  `composer create-project laravel/laravel viperReports`

- [ ] **1.2** Instalar y publicar Filament v3/v4
  `composer require filament/filament`
  `php artisan filament:install --panels`

- [ ] **1.3** Configurar base de datos SQLite en `.env`
  ```
  DB_CONNECTION=sqlite
  DB_DATABASE=/ruta/al/proyecto/database/viperreports.sqlite
  ```

- [ ] **1.4** Configurar conexión secundaria SQL Server en `config/database.php`
  ```php
  'sqlsrv_cad' => [
      'driver'   => 'sqlsrv',
      'host'     => env('CAD_DB_HOST', '192.168.1.122'),
      'database' => env('CAD_DB_DATABASE', 'ViperCAD_Log'),
      'username' => env('CAD_DB_USERNAME'),
      'password' => env('CAD_DB_PASSWORD'),
      'charset'  => 'utf8',
  ],
  ```

- [ ] **1.5** Instalar drivers PHP para SQL Server en el servidor
  - Verificar extensiones `sqlsrv` y `pdo_sqlsrv` activas en `php.ini`
  - Instalar Microsoft ODBC Driver 17/18 for SQL Server

- [ ] **1.6** Crear estructura de carpetas de servicios y modelos
  ```
  app/
    Services/
      CadReportService.php
      CadMonitorService.php
    Models/
      Cad/           <- Modelos de SQL Server (solo lectura)
        Response.php
        Incident.php
        Agent.php
        ResponseNote.php
      Local/         <- Modelos SQLite de la app
        User.php
        Role.php
        ReporteSaved.php
  ```

- [ ] **1.7** Crear los Modelos Eloquent base para SQL Server
  - `Response` con `$connection = 'sqlsrv_cad'` y `$primaryKey = 'OID'`
  - `Incident`, `Agent`, `ResponseType`, `Addresses`, `AssignModif`
  - Definir las relaciones (`hasMany`, `belongsTo`) entre modelos

- [ ] **1.8** Crear migración y tablas iniciales en SQLite
  - Tabla `users` (auth de Filament)
  - Tabla `activity_logs` (bitácora de acceso al sistema)

- [ ] **1.9** Verificar y probar la conexión doble
  - Crear ruta temporal `/test-cad-connection` que ejecute una query simple a `ViperCAD_Log` y muestre el resultado

- [ ] **1.10** Inicializar repositorio Git y definir ramas de trabajo
  - `main` (producción), `develop` (integración), `feature/*` (funcionalidades)

---

## Etapa 2 — Gestión de Usuarios y Roles
> **Objetivo:** Sistema de autenticación completo con roles que controlen quién puede acceder a cada módulo o reporte.

### Tareas

- [ ] **2.1** Instalar y configurar `spatie/laravel-permission`
  `composer require spatie/laravel-permission`

- [ ] **2.2** Instalar plugin de gestión de roles para Filament
  `composer require bezhansalleh/filament-shield`
  `php artisan filament:shield:install`

- [ ] **2.3** Definir los roles del sistema

  | Rol | Descripción |
  |---|---|
  | `super_admin` | Acceso total, configuración del sistema |
  | `jefe_despacho` | Todos los reportes, monitoreo en vivo |
  | `analista` | Reportes históricos, sin tiempo real |
  | `auditor` | Solo lectura de reportes y notas |

- [ ] **2.4** Crear panel de Filament para administrar usuarios
  - Crear, editar y desactivar cuentas de usuario
  - Asignar roles desde el panel

- [ ] **2.5** Configurar políticas de acceso por rol (Filament Policies)
  - Cada Recurso de Filament verificará el rol antes de mostrar secciones

- [ ] **2.6** Crear tabla `audit_logs` en SQLite para registro de accesos
  - Registrar: usuario, acción, IP, timestamp, parámetros usados

- [ ] **2.7** Personalizar la pantalla de Login de Filament
  - Agregar logo del sistema y nombre del proyecto

---

## Etapa 3 — Consultas y Capa de Servicios de Datos
> **Objetivo:** Encapsular todos los queries optimizados de ViperCAD_Log en servicios PHP reutilizables. Esta es la capa más crítica del proyecto.

### Tareas

- [ ] **3.1** Crear `CadReportService.php` con los métodos base
  ```php
  class CadReportService {
      public function getEventosPorRango(Carbon $desde, Carbon $hasta): Collection {}
      public function getDetallesEvento(string $sequenceNumber): array {}
      public function getCronologiaNotas(string $sequenceNumber): Collection {}
  }
  ```

- [ ] **3.2** Implementar query: **Reporte de Tiempos por Evento**
  (Query optimizado con CTEs de llamadas, fases y despachadores)
  - Parámetros: rango de fechas, agencia, tipo de evento
  - Incluye: Número de evento, Tipo, Telefonista (nombre+usuario+puesto), Despachador, todas las horas de fases, Tiempo Creación a Cierre

- [ ] **3.3** Implementar query: **Ficha Completa de Evento por Número**
  (Query de detalle único ya desarrollado)
  - Parámetros: `SequenceNumber`
  - Incluye: identificación, ubicación, informante, personal, tiempos KPI, disposición

- [ ] **3.4** Implementar query: **Cronología de Notas de un Evento**
  (Query con UNION ya desarrollado)
  - Parámetros: `SequenceNumber`
  - Incluye: Timestamp, Operador nombre+usuario, Estación, Texto de nota

- [ ] **3.5** Implementar query: **Resumen Estadístico por Rango**
  - Total de eventos por tipo
  - Total de eventos por agencia
  - Promedio de tiempo de despacho (CIEM 911)
  - Promedio de tiempo de respuesta (viaje)
  - Distribución de eventos por hora del día

- [ ] **3.6** Implementar query: **Productividad por Telefonista**
  - Eventos atendidos por telefonista en el rango
  - Promedio de tiempo de creación de evento
  - Distribución por turno

- [ ] **3.7** Implementar query: **Productividad por Despachador**
  - Eventos despachados por despachador en el rango
  - Promedio de tiempo desde despacho hasta recurso en sitio

- [ ] **3.8** Crear `CadMonitorService.php` para consultas de estado actual
  ```php
  class CadMonitorService {
      public function getEventosActivos(): Collection {}
      public function getRecursosEnCampo(): Collection {}
      public function getUltimosEventosRecientes(int $minutos = 30): Collection {}
  }
  ```

- [ ] **3.9** Crear sistema de caché por query
  - Queries de reportes históricos: caché de 5–15 minutos (`Cache::remember`)
  - Queries de monitoreo en vivo: sin caché o caché de 10–15 segundos

- [ ] **3.10** Escribir pruebas unitarias básicas para los servicios
  - Verificar que los queries retornan la estructura esperada
  - Probar con fechas de borde (cruce de medianoche, rango de un día)

---

## Etapa 4 — Módulo de Reportes
> **Objetivo:** Crear todas las pantallas de reportes dentro de Filament, con filtros interactivos, tablas paginadas y vistas de detalle.

### Tareas

- [ ] **4.1** Crear Resource Filament: **Lista de Eventos**
  - Tabla con columnas: Número, Tipo, Estado, Agencia, Telefonista, Despachador, Hora Llamada, Hora Cierre, Duración
  - Filtros: Rango de fechas, Tipo de Evento, Agencia, Estado
  - Buscador por número de evento o nombre de operador
  - Acción de ver detalle al hacer clic en una fila

- [ ] **4.2** Crear página de detalle: **Ficha del Evento**
  - Vista tipo "tarjeta" con todos los campos de la ficha completa
  - Sección de tiempos con mini-diagrama visual de la línea de tiempo
  - Sección de notas con la cronología completa al pie de la página

- [ ] **4.3** Crear Resource Filament: **Reporte de Tiempos**
  - Tabla con énfasis en columnas de tiempo y KPIs
  - Fila de totales/promedios al pie
  - Posibilidad de filtrar solo eventos cerrados

- [ ] **4.4** Crear página: **Estadísticas y Resumen**
  - Widgets de totales (tarjetas KPI): Total eventos, Promedio despacho, etc.
  - Gráfico de barras: Eventos por tipo
  - Gráfico de líneas: Volumen de eventos por hora del día
  - Gráfico de dona: Distribución por agencia

- [ ] **4.5** Crear página: **Productividad de Telefonistas**
  - Tabla con ranking de telefonistas por número de eventos atendidos
  - Columnas de métricas de tiempo promedio por operador

- [ ] **4.6** Crear página: **Productividad de Despachadores**
  - Similar a la de telefonistas, enfocado en métricas de despacho

- [ ] **4.7** Crear widget: **Buscador Rápido de Evento**
  - Input en el dashboard principal para buscar por número de evento
  - Al presionar Enter abre la Ficha Completa del Evento

- [ ] **4.8** Guardar filtros de reportes frecuentes
  - El usuario puede guardar una configuración de filtros con nombre propio
  - La configuración se almacena en SQLite (`reportes_saved` table)

---

## Etapa 5 — Módulo de Monitoreo en Vivo
> **Objetivo:** Panel en tiempo real usando Reverb y Livewire. Los supervisores verán eventos activos y recursos actualizarse automáticamente.

### Tareas

- [ ] **5.1** Instalar y configurar Laravel Reverb
  `composer require laravel/reverb`
  `php artisan reverb:install`
  Configurar en `.env` el host, puerto y clave del servidor WebSocket

- [ ] **5.2** Instalar Laravel Echo y Pusher JS en el frontend
  `npm install --save-dev laravel-echo pusher-js`
  Configurar en `resources/js/bootstrap.js`

- [ ] **5.3** Crear eventos de Broadcasting
  - `EventoActivoCreado`: cuando un nuevo evento es creado en CAD
  - `EventoEstadoCambiado`: cuando el estado de un evento cambia
  - `RecursoEstadoCambiado`: cuando una unidad cambia de estado

- [ ] **5.4** Crear `CadPollerJob` (Job en cola)
  - Job programado cada 15 segundos via Scheduler
  - Consulta `CadMonitorService` para detectar cambios desde la última ejecución
  - Si hay cambios, emite los eventos de Broadcasting correspondientes

- [ ] **5.5** Configurar Laravel Scheduler para ejecutar el Poller
  ```php
  Schedule::job(new CadPollerJob)->everyFifteenSeconds();
  ```

- [ ] **5.6** Crear componente Livewire: **Mapa de Eventos Activos**
  - Lista de tarjetas de eventos activos actualizadas en tiempo real
  - Código de colores por prioridad y tipo de evento
  - Se actualiza al recibir eventos de Reverb sin recargar la página

- [ ] **5.7** Crear componente Livewire: **Estado de Recursos (Unidades)**
  - Lista de unidades con su estado actual: Disponible, En Ruta, En Sitio, etc.
  - Se actualiza en tiempo real con cambios de `AssignModif`

- [ ] **5.8** Crear componente Livewire: **Feed de Actividad Reciente**
  - Últimas notas, despachos y cambios de estado en los últimos 30 minutos
  - Se presenta como un "log" que crece en tiempo real

- [ ] **5.9** Crear página de Dashboard de Monitoreo
  - Combina los tres componentes en un layout de pantalla completa
  - Diseñado para mostrarse en pantallas grandes de sala de operaciones

- [ ] **5.10** Implementar alertas visuales y sonoras
  - Si un evento supera X minutos sin recurso asignado, la tarjeta se marca en rojo
  - Sonido de alerta configurable (activar/desactivar por usuario)

---

## Etapa 6 — Exportación y Notificaciones
> **Objetivo:** Exportar reportes en Excel y PDF, y enviar notificaciones automáticas por correo o mensajería.

### Tareas

- [ ] **6.1** Instalar Laravel Excel (Maatwebsite)
  `composer require maatwebsite/excel`

- [ ] **6.2** Instalar DomPDF para generación de PDF
  `composer require barryvdh/laravel-dompdf`

- [ ] **6.3** Crear Export class para el Reporte de Tiempos
  - Exportar todas las columnas con cabeceras descriptivas
  - Aplicar formato de hora a las columnas de tiempo
  - Los filtros activos en pantalla se respetan en la exportación

- [ ] **6.4** Crear Export class para la Ficha Detallada de Evento
  - Formato de documento con secciones bien definidas
  - PDF con membrete configurable (logo y nombre de la institución)

- [ ] **6.5** Implementar exportación asíncrona con Queues
  - Reportes de rangos grandes (más de 30 días) se generan en background
  - Usar `WithChunkReading` de Laravel Excel para manejar memoria
  - Notificar al usuario dentro de Filament cuando el archivo está listo

- [ ] **6.6** Configurar sistema de correo (`SMTP` en `.env`)
  - Soporte para Gmail, Outlook, o servidor SMTP propio

- [ ] **6.7** Crear Notificación Laravel: **Reporte Listo para Descarga**
  - Envío por correo electrónico cuando un reporte pesado termina de generarse

- [ ] **6.8** Crear Notificación Laravel: **Alerta de Evento Crítico**
  - Configurable por tipo de evento o prioridad
  - Se puede enviar por correo o webhook (Slack/Telegram)

- [ ] **6.9** Crear panel de configuración de notificaciones en Filament
  - Cada usuario activa/desactiva qué tipo de alertas recibe
  - Configuración almacenada en SQLite

---

## Etapa 7 — Pruebas, Ajustes y Despliegue a Producción
> **Objetivo:** Asegurar la estabilidad del sistema, documentar su uso y desplegarlo en el servidor de producción.

### Tareas

- [ ] **7.1** Pruebas de carga sobre los queries de SQL Server
  - Ejecutar reportes de rango completo y medir el tiempo de respuesta
  - Ajustar caché si algún query supera los 3 segundos

- [ ] **7.2** Pruebas de concurrencia del WebSocket
  - Simular múltiples sesiones de supervisores conectadas a Reverb
  - Verificar que las actualizaciones en tiempo real llegan a todos

- [ ] **7.3** Pruebas de seguridad básica
  - Verificar que un usuario `auditor` no puede acceder a páginas de administración
  - Verificar que los queries a SQL Server no son susceptibles a SQL Injection

- [ ] **7.4** Optimización del rendimiento del frontend
  - `npm run build` para compilar y minificar assets
  - Configurar caché de assets estáticos en el servidor web (Nginx/Apache)

- [ ] **7.5** Crear archivo `.env.production` con variables de entorno de producción
  - `APP_ENV=production` y `APP_DEBUG=false`
  - Configurar el host correcto de Reverb para producción

- [ ] **7.6** Configurar servidor de producción
  - Definir si se usa Windows con XAMPP/IIS o Linux con Nginx
  - Instalar Supervisor para mantener Queue Worker corriendo
  - Instalar Supervisor para mantener servidor Reverb corriendo

- [ ] **7.7** Configurar proceso de despliegue
  - Script: `composer install --no-dev`, `php artisan migrate`, `npm run build`

- [ ] **7.8** Crear manual de usuario básico (PDF)
  - Guía de navegación de módulos de reportes
  - Guía de lectura de pantalla de monitoreo en vivo
  - Tabla de referencia de campos de la Ficha de Evento

- [ ] **7.9** Capacitación a usuarios finales
  - Sesión con jefes de despacho y analistas para demostración del sistema
  - Recolección de feedback y ajustes finales

- [ ] **7.10** Configurar respaldo automático de la base de datos SQLite
  - Script de copia diaria del archivo `.sqlite` a carpeta de respaldos

---

## Resumen de Plazos

| Etapa | Descripción | Semanas |
|---|---|---|
| Etapa 1 | Fundación del Proyecto | 1–2 |
| Etapa 2 | Gestión de Usuarios y Roles | 2–3 |
| Etapa 3 | Consultas y Servicios de Datos | 3–5 |
| Etapa 4 | Módulo de Reportes | 5–8 |
| Etapa 5 | Módulo de Monitoreo en Vivo | 8–10 |
| Etapa 6 | Exportación y Notificaciones | 10–12 |
| Etapa 7 | Pruebas, Ajustes y Producción | 12–14 |
| **Total estimado** | | **~14 semanas (3.5 meses)** |

> Los plazos están estimados para un solo desarrollador trabajando tiempo completo.
> Con un equipo de dos personas, el proyecto puede reducirse a 7–8 semanas
> trabajando etapas en paralelo.

---

## Prerrequisitos Técnicos (antes de iniciar Etapa 1)

- PHP 8.2 o superior
- Composer 2.x
- Node.js 18+ y NPM
- Extensiones PHP: `sqlsrv`, `pdo_sqlsrv`, `sqlite3`, `mbstring`, `openssl`, `bcmath`
- Microsoft ODBC Driver 17 o 18 for SQL Server
- Acceso de red al servidor SQL Server `192.168.1.122` desde el servidor de desarrollo
- Git instalado y acceso a repositorio (GitHub, GitLab, o Bitbucket)

---

*Documento generado el 2026-08-24 — Proyecto ViperCAD Reports & Monitor*
