---
author: Oscar
branch: docs/formato-pendientes
status: in-progress
---
# Formato común de `pendientes.md`

## Intent

Que todos los proyectos con arnés rsc escriban sus pendientes igual, para que la futura fábrica de
software pueda leerlos y juntarlos en una sola vista. Client Finder es el primero en adoptarlo.

## Scope

Dentro:
- Norma v1 en `02-DOCS/wiki/harness/formato-pendientes.md`.
- Pasar `02-DOCS/wiki/proyecto/pendientes.md` al formato v1 sin perder contenido.
- Índice de la wiki y `CLAUDE.md` apuntando a la norma.

Fuera:
- Aplicarlo en los demás proyectos (se hará en una sesión de cada uno).
- Un validador automático en el CI (propuesto como siguiente paso).

Decisiones del usuario (2026-10-25): ficha por pendiente con campos fijos; lo terminado se queda en
una sección `## Hecho` al final del mismo archivo.

## Checklist

- [x] Norma v1 escrita — prueba: la página existe y está en el índice.
- [x] `pendientes.md` en formato v1 — prueba: comprobación automática de cabecera, campos y valores.
- [x] Ningún pendiente abierto perdido — prueba: comparación con la versión anterior.
- [ ] CI en verde y PR fusionado — prueba: `gh pr checks` y `gh pr view`.

## Evidence

- Comprobador temporal (fuera del repo) de cabecera, títulos, los seis campos en orden, valores
  permitidos, fechas, códigos únicos y orden: `pendientes.md` → "OK: 7 abiertos (CF-001…CF-006,
  CF-008), 6 hechos con código". Una copia estropeada a propósito (prioridad `urgente`, campo
  `tipos`, código repetido) → 7 errores y salida 1.
- Las 10 tareas `[ ]` de la versión anterior siguen abiertas: 8 con el mismo texto y 2 reescritas
  (Excel terminado → CF-004; aviso a rsc → CF-006).

## Next

PR, CI en verde y merge. Después: convertir el comprobador en un paso del CI y llevar la norma a
los demás proyectos.
