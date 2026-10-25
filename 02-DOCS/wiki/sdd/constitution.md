---
type: constitution
title: client-finder — Constitución
description: Los principios no negociables que obedece cada fase de rsc-sdd en Client Finder.
tags: [sdd, constitution]
timestamp: 2026-10-25T16:00:00Z
topic: sdd
version: v1.0.1
---

# client-finder — Constitución

> Versión: v1.0.1 · Ratificada: 2026-10-25 · Última enmienda: 2026-10-25
> Principios no negociables. Si uno se incumple, el trabajo está mal aunque funcione.
> El detalle vive en `02-DOCS/wiki/proyecto/*`; aquí se ratifica el principio y se enlaza.

## 1. Stack

1. Backend PHP ^8.2 + Laravel 12; frontend React 19 a través de Inertia 2, con Vite y Tailwind 4.
   Fijado en `composer.json` y `package.json`. Cambiar un framework es una enmienda MAJOR.
2. Inertia es el único sistema de navegación: no se usa `react-router` ni otro router de cliente.
3. El código nuevo de la aplicación es `.js`/`.jsx`. `.ts`/`.tsx` solo en las primitivas que ya
   existen en `resources/js/components/ui/`. Detalle: [decisiones](../proyecto/03-decisiones.md).
4. SQLite en desarrollo; el servidor usará MySQL/MariaDB. El acceso a datos es por Eloquent o
   Query Builder, sin SQL propio de un motor concreto. Lo comprueba el CI
   (`.github/workflows/ci.yml`) con SQLite, MySQL 8.0 y MariaDB 10.11.
5. No se añade ningún paquete de Composer o npm sin decir por qué y sin confirmación del usuario.
   Los lockfiles (`composer.lock`, `package-lock.json`) se commitean.

## 2. Calidad

6. `vendor/bin/pest` pasa entero antes de cada merge. Ningún test se salta ni se comenta para
   ponerlo en verde. El CI lo ejecuta en cada pull request y no se fusiona en rojo.
7. Cada cambio de comportamiento del backend llega con un test Pest de feature que lo cubre.
   No hay suelo de cobertura numérico (no hay driver de cobertura instalado).
8. `./vendor/bin/pint --test`, `npm run lint` y `npm run format:check` terminan limpios
   (job "Calidad" del CI).
   No hay comprobador de tipos (ni PHPStan ni TypeScript en código nuevo); añadirlo es enmienda MINOR.

## 3. Convenciones

9. Tablas con prefijo `tbl_` y nombres en español; modelos en singular; controladores acabados en
   `Controller`; claves foráneas `<entidad>_id`. Código, comentarios y mensajes de dominio en
   español. Detalle: [decisiones](../proyecto/03-decisiones.md), [base de datos](../proyecto/02-base-de-datos.md).
10. Toda tabla lleva `activo`, `created_at`/`updated_at` y `deleted_at`; todos los modelos usan
    `SoftDeletes`. Nunca `forceDelete()`, `DELETE` ni `TRUNCATE` sobre datos.
11. Se respetan los `onDelete` existentes: `cascade` Cliente→Póliza, `restrict` Compañía y Ramo,
    `set null` TipoVencimiento. Cambiar uno exige propuesta previa con sus consecuencias.
12. Una migración ya aplicada no se edita ni se borra; los cambios de esquema van en una nueva.
13. Las dos búsquedas siguen separadas: `SearchController` (`/api/search`) y `DashboardController`
    (`/dashboard`). Unirlas solo si el usuario lo pide.
14. Los mensajes de commit empiezan por un gitmoji (Conventional Commits opcional) y están en
    español cuando describen dominio. Lo hace cumplir `.rsc/gitmoji-guard.mjs`.

## 4. Ramas y entrega

15. El trabajo va en una rama desde `main` y entra por pull request. El agente no hace commit en
    `main` (`.rsc/branch-guard.mjs`) ni `push --force` / `reset --hard` sin confirmación
    (`.rsc/danger-guard.mjs`).
16. **La autoría de git es humana.** Commits y PR son de Oscar: sin `Co-Authored-By` de una IA ni
    pie "generated with". Se comprueba en la fase `ship`.

## 5. Seguridad y privacidad (RGPD/LOPD)

17. Ningún secreto en el repositorio: `.env` está en `.gitignore`; las credenciales de
    herramientas van en `01-TOOLS/<proveedor>/.env`.
18. Nombres, DNI/NIE/CIF, matrículas y números de póliza son datos personales: nunca aparecen en
    logs, seeders, fixtures, commits, comentarios ni en `02-DOCS/`. Tests y ejemplos usan valores
    falsos evidentes (p. ej. `00000000T`).
19. Toda pantalla y API de la aplicación exige login; un usuario inactivo no entra
    (`ComprobarUsuarioActivo`) y lo exclusivo del admin pasa por `SoloAdmin`. Cada regla tiene su
    test de feature.
20. Los comandos destructivos de base de datos (`migrate:fresh`, `rollback` más allá del último
    lote) solo se ejecutan sobre una BD local desechable y con confirmación del usuario.

## 6. Interfaz

21. La interfaz está en español. Cada campo de formulario tiene su `<label>`, se puede usar con
    teclado y muestra el foco; los errores de validación salen junto al campo, en español.

## 7. Rendimiento

_Sin presupuesto todavía. Se fija cuando haya datos reales importados (tiempo de respuesta de la
búsqueda)._

## 8. Conocimiento y decisiones

22. Toda decisión importante se apunta en [decisiones](../proyecto/03-decisiones.md) con fecha,
    opciones y motivo. Esta constitución es el registro de decisiones de mayor rango.
23. Los cambios de base de datos (esquema, migraciones, seeders, relaciones) se proponen y se
    aprueban antes de implementarse.
24. Lo pendiente vive en [pendientes](../proyecto/pendientes.md) y el agente lo actualiza cuando
    algo se decide, se hace o cambia.

## Definición de terminado (lo que comprueba `verify`)

Un cambio entra solo si se cumple todo:

- [ ] `vendor/bin/pest` en verde, sin tests saltados (6) y con test del comportamiento nuevo (7).
- [ ] Pint, ESLint y Prettier limpios (8).
- [ ] Convenciones de nombres, SoftDeletes, `onDelete` y migraciones respetadas (9-13).
- [ ] Commit con gitmoji, en rama, por PR y con autoría humana (14-16).
- [ ] Sin secretos ni datos personales reales en código, tests, logs o documentación (17-18).
- [ ] Rutas nuevas protegidas por login y, si procede, por `SoloAdmin` (19).
- [ ] Decisiones y pendientes actualizados (22, 24).

## Registro de enmiendas (solo se añade)

| Fecha | Versión | Cambio | Motivo |
|-------|---------|--------|--------|
| 2026-10-25 | draft | Borrador generado por `rsc onboard`. | El suelo SDD necesita una constitución. |
| 2026-10-25 | draft | Principios redactados desde CLAUDE.md, decisiones y código. | Fase `constitution`. |
| 2026-10-25 | v1.0.0 | Ratificada por Oscar con los 24 principios. | Primera versión obligatoria antes de cualquier feature SDD. |
| 2026-10-25 | v1.0.1 | Los principios 4, 6 y 8 nombran el CI que los comprueba. Excepción única al 12: la migración de pólizas se renombra a `2026_07_12_153328` (contenido idéntico). | Pólizas se creaba antes que sus tablas referenciadas y fallaba en MySQL/MariaDB; solo estaba aplicada en el SQLite local de desarrollo. Aprobado por Oscar. |
