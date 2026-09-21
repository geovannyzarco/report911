---
paths:
  - app/Filament/Resources/TurnoResource.php
---

# Resources

## Conteos por relacion van en TextColumn y disabled si dehydrata
En Filament v5, `counts()` es metodo de TextColumn (`->counts('rel')` sobre columna `rel_count`), no de Table. `TextColumn` no tiene `placeholder()`. Un Select `->disabled()` en v5 NO pone dehydrated(false): el valor se envia y guarda igual (se usa para fijar despacho_id a los despachadores).
