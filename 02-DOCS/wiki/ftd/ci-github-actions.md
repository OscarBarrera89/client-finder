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

- [ ] Migración renombrada — prueba: `migrate:status` local sin pendientes y pólizas tras estados.
- [ ] Ciclo de migraciones en SQLite limpio — prueba: migrate, reset y migrate en un archivo temporal.
- [ ] Comprobaciones locales — prueba: Pest, Pint, ESLint, Prettier y build en verde.
- [ ] CI en verde en GitHub en los 4 jobs — prueba: `gh pr checks`.
- [ ] PR fusionado — prueba: `gh pr view` en estado MERGED.

## Evidence

_(se rellena al completar cada tarea)_

## Next

Renombrar la migración de pólizas.
