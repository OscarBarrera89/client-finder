---
author: Oscar
branch: ci/comprobar-pendientes
status: in-progress
---
# Comprobador de pendientes en el CI

## Intent

Que `pendientes.md` no pueda salirse del formato común v1 sin que nadie lo note. La fábrica de
software leerá estos archivos con un programa; un campo mal escrito la haría equivocarse.

## Scope

Dentro:
- Script `.github/scripts/comprobar-pendientes.mjs`, sin dependencias, reutilizable tal cual en los
  demás proyectos.
- Paso «Formato de pendientes» en el trabajo de calidad del CI.
- Dos reglas nuevas en la norma: el `doc` debe existir y `bloqueado-por` solo puede citar códigos
  que existan.
- CF-006: aviso publicado (#325) y a la espera del arreglo.

Fuera:
- Llevarlo a los demás proyectos (en la sesión de cada uno).

## Checklist

- [x] El script pasa con el `pendientes.md` actual — prueba: salida 0.
- [x] El script falla con cada tipo de error — prueba: copias estropeadas a propósito, salida 1.
- [x] ESLint en verde con el script nuevo — prueba: `npx eslint .`.
- [ ] El paso se ejecuta en GitHub — prueba: paso «Formato de pendientes» en `success`.
- [ ] CI en verde y PR fusionado — prueba: `gh pr checks` y `gh pr view`.

## Evidence

- `pendientes.md` actual → "formato v1 correcto. 7 abiertos, 7 hechos con código.", salida 0.
- 11 copias estropeadas a propósito, todas con salida 1 y su mensaje: sin prefijo, fecha de cabecera
  `26/10/2026`, título sin código, `prioridad: urgente`, campo `tipo` borrado, `doc` inexistente,
  `bloqueado-por: CF-099`, código repetido, códigos desordenados, línea de Hecho sin fecha y una
  ficha debajo de `## Hecho`.
- ESLint revisa el script (`--print-config` lo resuelve) y sale con 0. Prettier, Pint, Pest
  (105 passed) y build en verde.

## Next

PR, CI en verde y merge.
