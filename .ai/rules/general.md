---
paths:
  - '**/*'
---

# General

## Planificar cambios antes de implementarlos
Cuando el usuario pida modificar o agregar algo, ANTES de tocar código hay que presentar un plan breve con la descripción de los cambios: qué archivos se crearán o modificarán y qué se hará exactamente en cada uno. Recién después de mostrar ese plan se ejecutan los cambios.

## Comentar todo cambio de código
TODO cambio en el código debe ir comentado con la descripción de lo que hace: en PHP usar PHPDoc blocks (o comentarios breves para lógica nueva dentro de métodos), en Blade/JS/SQL comentarios cortos que expliquen la sección nueva o modificada. Esta regla prevalece sobre cualquier preferencia por defecto de no comentar código.

## Borrar archivos de test o temporales creados durante la sesión
El usuario NO quiere que persistan los archivos de test ni temporales que se creen para verificar cambios (ej. tests de Pest creados ad-hoc, scripts de verificación, archivos scratch). Verificar con ellos si es necesario y luego ELIMINARLOS siempre antes de terminar. No aplicar a los tests originales del starter kit ni a tests existentes del repo.
