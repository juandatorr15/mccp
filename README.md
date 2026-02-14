# MCCP - Multi-Channel Content Processor

Aplicacion fullstack que permite redactar contenido, procesarlo mediante IA para obtener un resumen ejecutivo (max 100 caracteres) y distribuirlo automaticamente a multiples canales: Email, Slack y SMS.

## Stack Tecnologico

- **Backend:** PHP 8.4 + Laravel 12
- **Frontend:** React 19 + Tailwind CSS (via Vite)
- **Base de datos:** PostgreSQL
- **IA:** Soporte para OpenAI, Claude (Anthropic) y Google Gemini

## Arquitectura

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend (React)                       │
│  ┌──────────────────┐    ┌───────────────────────────┐  │
│  │  MessageForm      │    │  Dashboard (Historial)    │  │
│  │  - Titulo         │    │  - Tabla con mensajes     │  │
│  │  - Contenido      │    │  - Resumen IA             │  │
│  │  - Canales (chk)  │    │  - Estado por canal       │  │
│  └────────┬─────────┘    └───────────┬───────────────┘  │
│           │ POST /api/messages       │ GET /api/messages │
└───────────┼──────────────────────────┼──────────────────┘
            │                          │
┌───────────▼──────────────────────────▼──────────────────┐
│                   Backend (Laravel)                       │
│                                                           │
│  ┌─────────────────────────────────────────────────────┐ │
│  │              MessageController                       │ │
│  │  POST /api/messages  │  GET /api/messages            │ │
│  └──────────┬───────────┴──────────────────────────────┘ │
│             │                                             │
│  ┌──────────▼──────────────────────────────────────────┐ │
│  │           MessageProcessorService                    │ │
│  │                                                      │ │
│  │  1. Generar resumen IA (AiSummaryService)           │ │
│  │     └─ Si falla IA → no se envia a ningun canal     │ │
│  │                                                      │ │
│  │  2. Distribuir a canales (independiente cada uno)    │ │
│  │     └─ Si un canal falla → los demas continuan      │ │
│  └──────────┬───────────────────────────────────────────┘ │
│             │                                             │
│  ┌──────────▼──────────────────────────────────────────┐ │
│  │            AiSummaryService                          │ │
│  │  Proveedores: OpenAI | Claude | Gemini              │ │
│  │  Resumen ejecutivo de max 100 caracteres            │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                           │
│  ┌─────────────────────────────────────────────────────┐ │
│  │         Canales (ChannelInterface)                   │ │
│  │                                                      │ │
│  │  ┌─────────────┐ ┌────────────┐ ┌────────────────┐ │ │
│  │  │EmailChannel  │ │SlackChannel│ │  SmsChannel    │ │ │
│  │  │Simula REST   │ │POST real a │ │Genera XML SOAP │ │ │
│  │  │Log en        │ │Webhook.site│ │Log en          │ │ │
│  │  │laravel.log   │ │            │ │laravel.log     │ │ │
│  │  └─────────────┘ └────────────┘ └────────────────┘ │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                           │
│  ┌─────────────────────────────────────────────────────┐ │
│  │              Base de Datos (PostgreSQL)               │ │
│  │                                                      │ │
│  │  messages              delivery_logs                 │ │
│  │  ├─ id                 ├─ id                         │ │
│  │  ├─ title              ├─ message_id (FK)            │ │
│  │  ├─ content            ├─ channel (email|slack|sms)  │ │
│  │  ├─ ai_summary         ├─ status (pending|sent|fail) │ │
│  │  ├─ status             ├─ payload (JSON)             │ │
│  │  └─ timestamps         ├─ error_message              │ │
│  │                        └─ timestamps                 │ │
│  └─────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────┘
```

## Estructura del Proyecto

```
app/
├── Http/Controllers/
│   └── MessageController.php        # API REST (store, index)
├── Models/
│   ├── Message.php                  # Modelo de mensajes
│   └── DeliveryLog.php              # Modelo de logs de entrega
└── Services/
    ├── AiSummaryService.php         # Integracion con OpenAI/Claude/Gemini
    ├── MessageProcessorService.php  # Orquestador principal
    └── Channels/
        ├── ChannelInterface.php     # Contrato para canales
        ├── EmailChannel.php         # Simulacion REST → laravel.log
        ├── SlackChannel.php         # POST real a Webhook.site
        └── SmsChannel.php           # XML SOAP → laravel.log

resources/js/
├── app.jsx                          # Entry point React
└── components/
    ├── App.jsx                      # Layout y navegacion
    ├── MessageForm.jsx              # Formulario de envio
    └── Dashboard.jsx                # Tabla de historial

routes/
├── api.php                          # GET/POST /api/messages
└── web.php                          # Vista principal (SPA React)
```

## Configuracion e Instalacion

### Requisitos previos

- PHP 8.2+
- Composer
- Node.js 22+
- PostgreSQL

### Pasos

1. **Clonar el repositorio**
```bash
git clone <repo-url> mccp
cd mccp
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar `.env`**
```env
# Base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mccp
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Proveedor de IA (openai, claude o gemini)
AI_PROVIDER=gemini
OPENAI_API_KEY=
CLAUDE_API_KEY=
GEMINI_API_KEY=tu_api_key_aqui

# Slack Webhook (URL de Webhook.site o Beeceptor)
SLACK_WEBHOOK_URL=https://webhook.site/tu-uuid
```

5. **Crear base de datos y migrar**
```bash
createdb mccp
php artisan migrate
```

6. **Compilar frontend**
```bash
npm run build
```

7. **Iniciar servidor**
```bash
php artisan serve
```

La aplicacion estara disponible en `http://localhost:8000`

## API Endpoints

### `POST /api/messages`

Crea un nuevo mensaje, genera resumen IA y lo distribuye a todos los canales.

**Request:**
```json
{
    "title": "Titulo del mensaje",
    "content": "Contenido completo del mensaje..."
}
```

**Response (201):**
```json
{
    "id": 1,
    "title": "Titulo del mensaje",
    "content": "Contenido completo del mensaje...",
    "ai_summary": "Resumen generado por IA en max 100 caracteres",
    "status": "completed",
    "delivery_logs": [
        { "channel": "email", "status": "sent" },
        { "channel": "slack", "status": "sent" },
        { "channel": "sms", "status": "sent" }
    ]
}
```

### `GET /api/messages`

Retorna el historial de todos los mensajes con sus logs de entrega.

## Resiliencia

- **Si la IA falla:** No se envia a ningun canal. El mensaje queda con status `failed` y todos los delivery_logs registran el error.
- **Si un canal falla:** Los demas canales se procesan normalmente. Solo el canal afectado queda con status `failed` y su error registrado.

## Simulacion de Canales

### Email
Simula una llamada REST. El payload completo se loguea en `storage/logs/laravel.log`:
```
[Email Channel] Payload enviado: {"title":"...","summary":"...","original_content":"..."}
```

### Slack (Webhook)
Realiza un POST real a la URL configurada en `SLACK_WEBHOOK_URL` (Webhook.site o Beeceptor).

### SMS (SOAP)
Genera un XML SOAP estructurado y lo loguea en `storage/logs/laravel.log`:
```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://ultracem.com/sms">
    <soapenv:Header/>
    <soapenv:Body>
        <sms:SendSmsRequest>
            <sms:destination>+570000000000</sms:destination>
            <sms:message>[RESUMEN_IA]</sms:message>
            <sms:reference>[TITULO]</sms:reference>
        </sms:SendSmsRequest>
    </soapenv:Body>
</soapenv:Envelope>
```

## Evidencia

### Logs (storage/logs/laravel.log)
_(Agregar screenshots de los logs mostrando el payload de Email y el XML de SMS)_

### Webhook.site (Slack)
**URL:** `https://webhook.site/374fddd0-a684-49b0-a4fc-a4173f176ed4`

_(Agregar screenshots del POST recibido en Webhook.site)_

## Descripcion de la Solucion

La aplicacion sigue una arquitectura de servicios desacoplados:

1. **MessageController** recibe la peticion y delega al servicio orquestador.
2. **MessageProcessorService** coordina el flujo: primero llama a la IA, y si tiene exito, distribuye a cada canal de forma independiente.
3. **AiSummaryService** abstrae la integracion con multiples proveedores de IA (OpenAI, Claude, Gemini) mediante un patron Strategy configurable por `.env`.
4. **ChannelInterface** define un contrato comun que implementan los 3 canales (EmailChannel, SlackChannel, SmsChannel), facilitando la extension a nuevos canales.
5. Cada operacion queda registrada en `delivery_logs` para trazabilidad total.
