---
paths:
  - 'app/Providers/Filament/**'
  - 'resources/views/components/evento-detalle.blade.php'
---

# Filament

## Sin mapa embebido en el detalle de evento
El detalle de evento (components/evento-detalle) NO debe usar mapa embebido (Leaflet/OSM ni otro): se quito porque cargaba scripts externos (unpkg) y peticiones de tiles que colgaban la pagina (Chrome "La pagina no responde"), sobre todo con el polling de las tablas re-renderizando el modal. Solo se muestra el enlace "Abrir en Google Maps" con las coordenadas del evento.
