---
paths:
  - config/filament-shield.php
---

# Config

## super_admin requiere define_via_gate=true para bypass
Con 'define_via_gate' => false (default), 'intercept_gate' => 'before' NO se registra: Shield solo registra el Gate::before cuando define_via_gate=true. Con false, el rol super_admin debe tener cada permiso asignado. El proyecto usa true para bypass total (usuarios con rol super_admin pasan cualquier policy).
