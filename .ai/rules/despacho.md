---
paths:
  - app/Filament/Resources/DespachoResource.php
---

# Despacho

## Un Select multiple() con relacion no se incluye en $data
En Filament v5, un `Select::make(...)->multiple()->relationship(...)` para many-to-many se define `dehydrated(false)` automaticamente y Filament sincroniza el pivot al guardar. Si se sobreescribe `handleRecordCreation()` (como en CreateDespacho), esos ids NO aparecen en `$data`; hay que leerlos del estado en bruto: `$this->form->getRawState()['sectores']`.