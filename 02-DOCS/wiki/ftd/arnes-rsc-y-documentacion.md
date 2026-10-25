---
author: Oscar
branch: chore/arnes-rsc-y-documentacion
status: in-progress
---
# Arnés rsc, constitución y documentación unificada

## Intent

Dejar el arnés rsc recién instalado listo para trabajar: constitución ratificada, una sola
carpeta de documentación (02-DOCS) y todo el trabajo pendiente del repositorio guardado en commits.

## Scope

Dentro:
- Mover docs/ a 02-DOCS/wiki/proyecto/ (git mv; PENDIENTES pasa a Markdown) y crear wiki/index.md.
- Actualizar referencias en CLAUDE.md y en la memoria de Claude Code.
- Registrar la decisión: altas y ediciones permitidas, en principio, a admin y empleado.
- Completar y ratificar 02-DOCS/wiki/sdd/constitution.md.
- Commits por tema en esta rama, autoría solo de Oscar (sin Co-Authored-By).
- Añadido durante el trabajo: quitar el paquete bootstrap sin uso, ESLint ignora el arnés,
  formato Pint/Prettier en commit aparte, pint.json excluye migraciones.

Fuera:
- Push y pull request (solo si se piden).
- Implementar los permisos de altas/ediciones en código.
- El resto de pendientes, que siguen pendientes.

## Checklist

- [x] docs/ movido — prueba: `git status` muestra los renombrados y docs/ ya no existe.
- [x] Referencias actualizadas — prueba: no quedan rutas `docs/0*` en CLAUDE.md ni eslint.config.js.
- [x] Decisión de permisos registrada en pendientes.md y 03-decisiones.md.
- [x] Constitución ratificada — prueba: `version: v1.0.0`, sin `status: draft`; `rsc doctor`
      devuelve `"status": "ready"` y `"pending": []`.
- [x] Pest, lint, formato y build pasan antes del commit de formato.
- [x] Commits hechos — prueba: `git log` y `git status` limpio.

## Evidence

- Movimiento: `git mv` de 02, 03 y 04 (R100); 01 y PENDIENTES no estaban versionados. Nota: los
  tres renombrados entraron en el commit 674113d porque ya estaban en el staging.
- `rsc doctor`: `Harness health: healthy`, onboarding `ready`, `pending: []`.
- Antes del commit de formato: `vendor/bin/pest` → 53 passed (229 assertions);
  `pint --test` ok; `npm run format:check` → "All matched files use Prettier code style!";
  `npm run lint` exit 0; `npm run build` → built.
- ESLint daba 1584 errores, todos en .claude/ y .rsc/ (scripts del arnés); resuelto con ignores.
- Commits: 674113d (backend), 2b729ed (frontend), de9043f (arnés), 95cd551 (.gitignore),
  cbc5840 (formato) y el commit de documentación.

## Next

Decidir si se sube la rama y se abre el pull request hacia main.
