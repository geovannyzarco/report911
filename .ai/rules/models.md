---
paths:
  - 'app/Models/*.php'
---

# Models

## Sector usa tabla 'sectores' fija via $table
La tabla de sectores se creo como 'sectores' (espanol) y el modelo Sector elocuente resolveria 'sectors' (plural ingles). El modelo Sector define `protected $table = 'sectores';` a proposito. Al crear modelos para catalogos del modulo, verificar que el nombre de tabla de la migracion coincida con el plural ingles que Eloquent deduce; si difiere, definir `$table`.
