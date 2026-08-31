# API - Anotaciones

Endpoint para consultar las anotaciones/notas de un evento del CAD (ViperCAD_Log).

## URL Base

```
http://192.168.145.65/report911/public/api/v1
```

## Endpoint

```
GET /api/v1/anotaciones/{numeroSecuencia}
```

## Parametros

| Parametro | Tipo | Descripcion |
|-----------|------|-------------|
| `numeroSecuencia` | string | Numero de secuencia del evento (ej: `SE911:2026:08:12:0963`) |

## Ejemplo de Peticion

```bash
curl http://192.168.145.65/report911/public/api/v1/anotaciones/SE911:2026:08:12:0963
```

## Respuesta Exitosa (200)

```json
{
    "success": true,
    "data": [
        {
            "HORA_CREACION_EVENTO": "2026-08-12 22:20:04.557",
            "AGENTE_CREA_EVENTO": "JOSUE LEVI HERNANDEZ MENDOZA",
            "AGENTE_INGRESA_NOTAS": "JOSUE LEVI HERNANDEZ MENDOZA",
            "NOTAS": "LLAMA LA INFORMANTE REPORTANDO QUE ESTA SIENDO AGREDIDA...",
            "HORA_NOTA_AGREGADA": "2026-08-12 22:20:28.160",
            "PUESTO_DE_TRABAJO": "PUESTO10",
            "GRUPO_DESPACHO": "SSC.SUR"
        }
    ]
}
```

## Campos de Respuesta

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `HORA_CREACION_EVENTO` | datetime | Fecha y hora de creacion del evento |
| `AGENTE_CREA_EVENTO` | string | Nombre del agente que creo el evento |
| `AGENTE_INGRESA_NOTAS` | string | Nombre del agente que agrego la nota |
| `NOTAS` | string | Texto de la anotacion/nota |
| `HORA_NOTA_AGREGADA` | datetime | Fecha y hora en que se agrego la nota |
| `PUESTO_DE_TRABAJO` | string | Puesto de trabajo del agente |
| `GRUPO_DESPACHO` | string | Grupo de despacho al que pertenece |

## Respuesta Error (500)

```json
{
    "success": false,
    "message": "Error al consultar anotaciones",
    "error": "mensaje de error"
}
```

## Notas Tecnicas

- **Base de datos**: SQL Server (ViperCAD_Log)
- **Procedimiento almacenado**: `Sp_Anotaciones`
- **Usuario BD**: `cescobar` (requiere permiso EXECUTE sobre el SP)
- **Connection**: `sqlsrv_cad` (configurada en `config/database.php`)

## Permisos Requeridos en SQL Server

```sql
USE [ViperCAD_Log]
GO
GRANT EXECUTE ON [dbo].[Sp_Anotaciones] TO [cescobar]
GO
```
