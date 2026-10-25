---
type: plan
title: Plan — Importador de clientes y pólizas desde Excel
description: Cómo se construye el importador .xlsx aprobado en la spec importador-excel.
tags: [sdd, plan, importacion, excel]
timestamp: 2026-10-25T17:30:00Z
topic: sdd
slug: importador-excel
status: planned
---

# Plan — Importador de clientes y pólizas desde Excel

> Spec: [importador-excel](../specs/importador-excel.md) (aprobada 2026-10-25) ·
> Constitución: [v1.0.1](../constitution.md)

## Enfoque

Tres piezas de dominio sin estado, separadas de la parte web:

1. **Lector**: abre el `.xlsx`, toma la primera hoja, reconoce las cabeceras y devuelve las filas
   con su número de fila de Excel. Fechas y números ya convertidos.
2. **Simulador**: valida las filas, las agrupa por DNI, las compara con la base de datos y devuelve
   la simulación (resumen, clientes con su estado y diferencias, avisos, errores). Solo lee.
3. **Ejecutor**: recibe el archivo, los DNI aceptados y la huella de la simulación que vio el admin.
   Vuelve a simular dentro de una transacción; si la huella no coincide, no guarda nada. Si
   coincide, crea catálogos, crea o actualiza clientes y crea pólizas.

La parte web es un controlador del panel de admin y una página Inertia.

## Piezas

| Pieza | Ubicación | Responsabilidad |
|---|---|---|
| Paquete | `openspout/openspout` (Composer, versión compatible con PHP 8.2) | Leer `.xlsx` fila a fila con poca memoria |
| Normalizador | `app/Importacion/Normalizador.php` | DNI y matrícula (mayúsculas, sin espacios/guiones/puntos); clave de catálogo (minúsculas, sin tildes, espacios colapsados); textos con trim |
| Lector | `app/Importacion/LectorExcel.php` | Cabeceras → claves internas; fila vacía se ignora; columnas extra se ignoran; fecha y prima convertidas |
| Simulador | `app/Importacion/SimuladorImportacion.php` | Validación, agrupación, comparación con BD, huella |
| Ejecutor | `app/Importacion/EjecutorImportacion.php` | Re-simular, comparar huella, guardar en una transacción |
| Excepciones | `app/Importacion/SimulacionCaducada.php`, `ArchivoNoValido.php` | Mensajes claros sin datos personales |
| Controlador | `app/Http/Controllers/Admin/ImportacionController.php` | `index`, `simular`, `confirmar`, `cancelar` |
| Rutas | `routes/web.php`, grupo `admin` existente | `GET admin/importacion`, `POST admin/importacion`, `POST admin/importacion/confirmar`, `DELETE admin/importacion` |
| Página | `resources/js/pages/admin/ImportacionPage.jsx` | Subida, resumen, errores, lista de clientes con casillas, confirmar/cancelar |
| Enlace | Barra superior, junto a "Usuarios" (solo admin) | Acceso a "Importar Excel" |
| Búsquedas | `SearchController` y `DashboardController` | Añadir la búsqueda por DNI y matrícula normalizados (mismo cambio en ambas, sin unificarlas) |

## Flujo y datos

- **Subir** (`simular`): valida `file|mimes:xlsx|max:10240`; guarda el archivo en el disco privado
  (`storage/app/private/importaciones/<uuid>.xlsx`); guarda en la sesión solo el uuid y la hora;
  redirige a `index`.
- **Ver** (`index`): si la sesión tiene una importación de menos de una hora, lee el archivo,
  simula y manda a la página la simulación y su huella. Si caducó, borra el archivo y lo dice.
- **Confirmar** (`confirmar`): recibe `aceptados[]` (DNI normalizados) y `huella`; el Ejecutor
  vuelve a simular. Huella distinta → no guarda nada y vuelve a `index` con la simulación nueva.
  Bien → guarda, borra el archivo, limpia la sesión y vuelve con los totales.
- **Cancelar**: borra el archivo y limpia la sesión.
- **Limpieza**: en cada `index` y `simular` se borran los archivos de importación de más de una
  hora. No hace falta cron.
- **Huella**: SHA-256 de la simulación serializada (clientes, pólizas, diferencias y errores).
  Detecta cambios en la BD entre ver y confirmar.

## Reglas de validación (por fila)

- Obligatorios: nombre (≤150), DNI (≤20 tras normalizar), nº de póliza (≤50), compañía (≤150),
  ramo (≤150).
- Opcionales: matrícula (≤15 tras normalizar), teléfono (≤20), email (formato, ≤150), dirección
  (≤255), prima (número ≥0 y ≤99.999.999,99; acepta celda numérica o texto `1.234,56`), fecha de
  contratación (celda de fecha o texto `dd/mm/aaaa`), vencimiento (texto ≤50 → tipo).
- Entre filas: mismo nº de póliza repetido → error en ambas; mismo DNI con nombres distintos →
  error; mismo DNI con contacto distinto → aviso y primer valor no vacío.
- Contra la BD (incluidos los dados de baja): DNI dado de baja → error; nº de póliza dado de baja →
  error; nº de póliza de otro cliente → error; nº de póliza del mismo cliente → coincidente.

## Guardado

- Catálogos: búsqueda por clave normalizada del nombre; si no existe, se crea con `codigo`
  generado del nombre (mayúsculas, `_`, máx. 20) con sufijo `_2`, `_3`… si choca.
- Cliente nuevo: se crea con `activo = true`. Cliente existente aceptado: se actualizan solo los
  campos con valor en el Excel.
- Pólizas nuevas: `activo = true`; `estado_id`, `fecha_vencimiento` y `fecha_anulacion` vacíos.
- Todo dentro de `DB::transaction`. Cualquier excepción deshace todo.
- Log: una línea con totales y el id del admin. Nunca DNI, nombres ni nº de póliza.

## Base de datos

**Sin cambios de esquema ni migraciones nuevas.** Solo inserciones y actualizaciones con Eloquent
sobre las tablas existentes, más consultas `withTrashed` para detectar registros dados de baja.

## Estrategia de tests (Pest)

- Un helper de tests crea `.xlsx` temporales con datos inventados usando el escritor de OpenSpout.
- Unit: Normalizador (DNI, matrícula, clave de catálogo).
- Feature: un test por criterio de aceptación de la spec (13), más validaciones de fila, cabecera
  sin columna obligatoria, caducidad de la simulación, cancelar y la búsqueda normalizada en las
  dos búsquedas.
- Atomicidad: forzar un fallo en mitad del guardado y comprobar que la BD no cambia.
- Logs: capturar los logs durante una importación y comprobar que no aparecen datos personales.
- El CI ejecuta todo contra SQLite, MySQL y MariaDB.
- Medición manual: un `.xlsx` inventado de 5.000 filas; se anotan tiempo y memoria en la evidencia.

## Riesgos

- **Cabeceras reales distintas**: la lista de alias está en un único sitio del Lector.
- **Rendimiento**: simular dos veces (ver y confirmar) duplica el trabajo; si 5.000 filas tardan
  demasiado, se pasa a cola (nuevo ciclo).
- **Mayúsculas y minúsculas en búsquedas**: MySQL compara sin distinguirlas y SQLite solo en ASCII;
  los tests de búsqueda usan valores que funcionan en ambos.

## Fuera del plan

Historial de importaciones, exportación, CSV, procesamiento en cola.

## Tareas

| # | Tarea | Depende | Hecho cuando |
|---|---|---|---|
| T1 | Instalar `openspout/openspout` compatible con PHP 8.2 | — | `composer.lock` lo incluye y su requisito de PHP admite 8.2 |
| T2 | Normalizador + tests unit | — | Tests de DNI, matrícula y clave de catálogo en verde |
| T3 | Helper de tests que escribe `.xlsx` inventados | T1 | Un test lo usa y el Lector lee lo escrito |
| T4 | Lector (cabeceras, filas vacías, fecha, prima) + tests | T1-T3 | Tests de lectura en verde, incluida cabecera sin columna obligatoria |
| T5 | Simulador (validación, agrupación, comparación, huella) + tests | T4 | Tests de simulación de los criterios 1, 3, 4, 6, 7 y de validación en verde |
| T6 | Ejecutor (transacción, catálogos, alta/actualización, huella) + tests | T5 | Tests de los criterios 2, 5, 8, 9, 10 en verde |
| T7 | Controlador + rutas + acceso solo admin + tests | T6 | Tests de los criterios 11, 12, caducidad y cancelar en verde |
| T8 | Página `ImportacionPage.jsx` + enlace en la barra superior | T7 | Build en verde y la página muestra simulación y confirma en local |
| T9 | Búsqueda normalizada en las dos búsquedas + tests | T2 | Buscar "1234-abc" encuentra la matrícula "1234ABC" en ambas |
| T10 | Logs sin datos personales + test | T6 | El test captura logs y no encuentra DNI, nombre ni nº de póliza (criterio 13) |
| T11 | Medición con 5.000 filas inventadas | T7 | Tiempo y memoria anotados en la evidencia |
| T12 | Verificación completa | T1-T11 | Pest, Pint, ESLint, Prettier y build en verde en local y en los 4 jobs del CI |

T2 y T9 son independientes del resto; T3-T7 van en cadena.

## Evidencia de implementación (2026-10-25, piloto automático)

| Tarea | Resultado observado |
|---|---|
| T1 | `openspout/openspout` v4.28.5 (requiere PHP ~8.2/~8.3/~8.4). Se fija `config.platform.php = 8.2.0` en `composer.json`: sin eso Composer elegía v5.12 (PHP ≥ 8.4) por el PHP 8.5 local |
| T2 | `tests/Unit/NormalizadorTest.php`: rojo (clase inexistente) → verde, 5 tests |
| T3-T4 | Helper `crearExcel()`/`filaExcel()` en `tests/Pest.php`. Lector: rojo → verde, 7 tests. OpenSpout solo numera filas reales con `SHOULD_PRESERVE_EMPTY_ROWS` |
| T5 | Simulador: 16 tests; 1 fallo inicial (orden de errores de cabecera) corregido con ordenación estable |
| T6, T10 | Ejecutor: 10 tests en verde a la primera, **escritos junto al código sin ver el rojo antes** (desviación de TDD declarada). Incluye atomicidad (fallo forzado → BD intacta) y logs sin datos personales |
| T7 | Controlador: rojo (10 fallos) → verde, 10 tests |
| T8 | `ImportacionPage.jsx` + enlace "Importar Excel" en la barra superior. Build en verde. **No probada en un navegador**: los tests comprueban las props que recibe, no el render |
| T9 | Búsquedas: el test de la API dio un rojo falso (faltaban imports); verificado después con el código antiguo (rojo real) y el nuevo (verde). Añadido caso "solo símbolos no devuelve todo" |
| T11 | 5.000 filas / 2.500 clientes en SQLite temporal: simulación 0,9 s y 58 MB; importación 4,6 s y 65 MB; .xlsx de 260 KB |
| Revisión | Encontrado: nº de póliza que solo difiere en mayúsculas habría dado error 500 en MySQL/MariaDB. Test rojo → verde comparando sin mayúsculas |
| T12 | Local: Pest 105 passed (509 assertions); Pint, ESLint, Prettier y build ok. CI: ver PR |
