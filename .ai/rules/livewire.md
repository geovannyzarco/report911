---
paths:
  - app/Livewire/EventReportTable.php
---

# Livewire

## Paginación SQL en SQL Server 2008 R2
La base CAD es SQL Server 2008 R2 (no soporta OFFSET/FETCH). Para paginar usa ROW_NUMBER() OVER (ORDER BY ...). IMPORTANTE: un statement que comienza con WITH/CTEs NO puede envolverse con "SELECT ... FROM (<query con WITH>)"; SQL Server lanza "Sintaxis incorrecta cerca de WITH". Los CTEs siempre deben ir al inicio del statement: "WITH <ctes> SELECT ... FROM (<select sin WITH>) AS t". En EventReportTable los builders querySqlPorEvento/querySqlPorRango devuelven ['ctes'=>..., 'select'=>...] para componer el statement correcto.

## Evitar OR entre LIKE en SQL Server 2008 R2
Buscar con `a.SequenceNumber LIKE ? OR (i.SequenceNumber LIKE ? AND fechas)` hace que SQL Server 2008 R2 escanee toda la tabla (6.17M filas, 15-35s) aunque cada branch por separado sea <0.3s. Solución: separar cada LIKE en su propia subquery y unirlas con UNION ALL dentro del CTE. Para Responses.SequenceNumber usar prefijo sin wildcard inicial: `SE911:AAAA:MM:DD:NNNN%` (concatenar el término completo + '%'); para el contador del incidente usar `%:NNNN` + filtro de fecha.
