---
paths:
  - tests/Feature/TurnoTest.php
---

# Feature

## Probar paginas de recursos Filament con Livewire
Para montar paginas de recursos en tests: en beforeEach `Filament::setCurrentPanel('monitoreo')` + `$this->actingAs($usuario)` con rol super_admin (si no, policy deniega 403). `Livewire::test(CreateTurno::class)->fillForm([...])->call('create')->assertHasNoFormErrors()->assertRedirect()`. CreateRecord redirige a la pagina edit (no index). Repeaters con `->relationship()` se llenan bajo el nombre de la relacion.
