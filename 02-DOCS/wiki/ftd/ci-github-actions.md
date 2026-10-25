---
author: Oscar
branch: ci/tests-github-actions
status: in-progress
---
# CI en GitHub Actions con base de datos real

## Intent

Que cada pull request hacia main compruebe automáticamente que todo funciona, también contra los
motores de base de datos del servidor (MySQL y MariaDB), antes de fusionarlo.

## Scope

Dentro:
- Arreglar el orden de las migraciones: pólizas se creaba antes que las tablas a las que apunta y
  fallaba en MySQL/MariaDB. Solo se renombra el archivo (contenido idéntico) y se actualiza su fila
  en la tabla `migrations` del SQLite local. Excepción única aprobada por Oscar el 25/10/2026.
- `.github/workflows/ci.yml`: calidad (Pint, ESLint, Prettier, build) y tests con SQLite, MySQL 8.0
  y MariaDB 10.11, incluido el ciclo migrate → reset → migrate.
- Registrar la excepción y el CI en decisiones y en la constitución (enmienda PATCH).

Fuera:
- El importador de Excel.
- Contratar hosting o configurar el subdominio.

## Checklist

- [x] Migración renombrada — prueba: `migrate:status` local sin pendientes y pólizas tras estados.
- [x] Ciclo de migraciones en SQLite limpio — prueba: migrate, reset y migrate en un archivo temporal.
- [x] Comprobaciones locales — prueba: Pest, Pint, ESLint, Prettier y build en verde.
- [x] CI en verde en GitHub en los 4 jobs — prueba: `gh pr checks`.
- [ ] PR fusionado — prueba: `gh pr view` en estado MERGED.

## Evidence

- Local: `migrate:status` muestra 2026_07_12_153328_create_polizas_table después de estados, "Nothing to migrate".
- Ciclo en SQLite temporal: migrate, reset y migrate sin errores.
- Local: Pest 53 passed (229 assertions); Pint, ESLint, Prettier y build ok.
- Primer CI (run 40211817683): Calidad ok, los 3 jobs de tests fallaron. Causa: Inertia buscaba las
  páginas en js/Pages y Linux distingue mayúsculas (Windows no). Arreglado en f8bdb42 publicando
  config/inertia.php con js/pages.
- Segundo CI (run 40212038199): los 4 jobs en verde; en MySQL y MariaDB create_polizas_table DONE
  tres veces (migrate, reset, migrate) y "Tests: 53 passed (229 assertions)".

## Next

Fusionar el PR #2 cuando el CI esté en verde y actualizar main en local.
