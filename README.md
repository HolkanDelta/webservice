# Webservice de Integración GPS (Middleware)

Este proyecto es un sistema intermediario (middleware) basado en **Laravel 12** e **Inertia.js con React** diseñado para sincronizar datos de localización y telemetría de unidades de transporte en tiempo real. 

El flujo principal extrae la información de ubicación desde la API de **Mapon** y la transmite formateada y adaptada a múltiples plataformas externas de rastreo (clientes de monitoreo).

---

## 🛠️ Arquitectura Técnica y Flujo de Datos

El sistema opera bajo el siguiente flujo:
1. **Origen de Datos (Mapon API)**: Consultando el endpoint `https://acceso.holkan.com.mx/api/v1/unit/list.json` usando el `apikey` configurado para cada cliente.
2. **Procesamiento e Integración**: Los controladores (SDKs correspondientes) parsean los datos de geolocalización (latitud, longitud, rumbo, velocidad, odómetro, estado del motor, batería, etc.) y estructuran el payload según el formato requerido por cada plataforma destino (XML/SOAP, JSON/REST).
3. **Transmisión y Registro**: El sistema envía las actualizaciones a las plataformas destino y registra el resultado, payload y tiempos de respuesta en la base de datos para auditoría y visualización.

---

## 📂 Modelos de Datos Principales

Los modelos Eloquent (`app/Models`) estructuran la base de datos de la siguiente manera:
- **`Client`**: Almacena las credenciales de conexión tanto para Mapon (`apikey`) como para las plataformas destino (`user_name`, `user_pass`, `company_id`, `token`).
- **`Service`**: Define los distintos servicios de integración configurados (ej. `MAPON - RECURSO_CONFIABLE`).
- **`ServiceLog`**: Registra la bitácora de cada ejecución, almacenando el estado (`success`, `partial`, `failure`), mensaje de error, tiempo de ejecución en milisegundos (`runtime_ms`) y el payload transmitido.
- **`User`**: Gestiona el acceso de usuarios administrativos al panel web.

---

## 🚀 Integraciones Disponibles (SDKs en `app/Http/Controllers/sdk`)

El proyecto cuenta con módulos específicos para conectarse a las siguientes plataformas de rastreo:

*   **Recurso Confiable (Rcontrol)** ([RcController.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/RcController.php)): Integración SOAP/WCF. Envía actualizaciones de eventos. Cuenta con auto-recuperación de token en caliente.
*   **Landstar** ([Landstar.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/Landstar.php)): Integración SOAP/WCF para reportar la localización de remolques y tractos.
*   **Kronh** ([Kronh.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/Kronh.php)): Integración REST/JSON con telemetría avanzada. Utiliza el servicio helper [KronhTrackerService.php](file:///c:/laragon/www/Webservice/app/Services/KronhTrackerService.php).
*   **Pegasus** ([Pegasus.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/Pegasus.php)): Integración REST/JSON.
*   **SkyAngel** ([SkyAngel.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/SkyAngel.php)): Integración REST/JSON.
*   **Control T** ([controlT.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/controlT.php)): Integración REST/JSON.
*   **Fleet Rocket** ([sdkfleet.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/sdkfleet.php)): Sincronización y logins.
*   **Unigis** ([Unigis.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/Unigis.php)) & **UnigisService** ([UnigisService.php](file:///c:/laragon/www/Webservice/app/Services/UnigisService.php)): Sincronización de eventos de viaje.

---

## ⏱️ Programación de Tareas (Cron / Scheduling)

Las tareas de sincronización están automatizadas en [console.php](file:///c:/laragon/www/Webservice/routes/console.php) para ejecutarse periódicamente:

| Comando | Frecuencia | Descripción |
| :--- | :--- | :--- |
| `app:recurso-confiable-command` | Cada 5 minutos | Transmite ubicaciones a Recurso Confiable. |
| `app:recurso-token-change` | Dos veces al día (10:10 y 16:10) | Renovación preventiva del token de acceso de Recurso Confiable. |
| `app:control-tcommand` | Cada 5 minutos | Envía ubicaciones a Control T. |
| `app:land-star-command` | Cada 5 minutos | Envía ubicaciones a Landstar. |
| `app:sky-angelcommand` | Cada 5 minutos | Envía ubicaciones a SkyAngel. |
| `app:pegasus-command` | Cada 5 minutos | Envía ubicaciones a Pegasus. |
| `app:kronh-command` | Cada 5 minutos | Envía ubicaciones a Kronh. |
| `fleet-rocket_command` | Cada 5 minutos | Envía ubicaciones a Fleet Rocket. |

---

## 🛡️ Mecanismo de Autorecuperación (Self-Healing)

En la integración con **Recurso Confiable**, en ocasiones el token de sesión expira o se invalida antes de lo previsto, devolviendo errores como `CGI:USERUNK` o `"Autentificación incorrecta (Usuario, Contraseña o Token)"` con `idJob` igual a `0`.

Para evitar la pérdida de transmisiones y caídas de servicio, se implementó un flujo de auto-recuperación en caliente dentro de [RcController.php](file:///c:/laragon/www/Webservice/app/Http/Controllers/sdk/RcController.php):

1. **Detección**: Se evalúa la respuesta SOAP devuelta por el servidor. Si el XML contiene patrones de falla (`CGI:USERUNK` o `Autentificación incorrecta`), se atrapa el error.
2. **Renovación**: El sistema ejecuta en ese mismo instante el comando Artisan `app:recurso-token-change` síncronamente.
3. **Actualización**: Se refresca la instancia del modelo `$client->refresh()` para cargar el nuevo token guardado en base de datos.
4. **Reintento**: Se realiza nuevamente la petición SOAP con los mismos eventos. Todo esto ocurre de manera transparente en la misma ejecución del cron.

---

## 📊 Panel de Administración Web (Dashboard)

El proyecto cuenta con un panel web administrativo montado sobre Laravel, Inertia y React:
*   **Métricas Globales**: Tasa de éxito promedio global, tiempo promedio de transmisión (latencia) y porcentaje de servicios activos.
*   **Gestión**: Formularios para dar de alta/modificar Clientes (`clientes`) y vincularlos a Servicios (`servicios`).
*   **Monitorización de Errores**: Listado en tiempo real de los últimos logs de ejecución con detalles de payloads y respuestas de error para depuración rápida.
