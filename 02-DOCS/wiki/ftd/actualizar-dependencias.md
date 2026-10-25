---
author: Oscar
branch: chore/actualizar-dependencias
status: in-progress
---
# Actualización de seguridad de las dependencias

## Intent

Resolver los 24 avisos de seguridad de `composer audit` (uno crítico en `laravel/framework`)
antes de subir datos reales al servidor, y probar el importador de Excel en un navegador real.

## Scope

Dentro:
- `composer update` dentro de las restricciones actuales de `composer.json` (sin cambiar versiones
  mayores) y con la plataforma fijada en PHP 8.2.
- Revisar también `npm audit` y aplicar solo arreglos sin cambios de versión mayor.
- Prueba del importador en un navegador (Edge/Chrome sin ventana) sobre una base de datos temporal,
  con capturas de pantalla.

Fuera:
- Subir versiones mayores (Laravel 13, React, etc.).
- Cambios de código salvo los que exija la actualización.

## Checklist

- [x] `composer audit` sin avisos — prueba: salida del comando.
- [x] `npm audit` revisado — prueba: salida antes y después.
- [x] Pest, Pint, ESLint, Prettier y build en verde — prueba: salida de los comandos.
- [x] Importador probado en el navegador — prueba: capturas de subida, simulación y resultado.
- [ ] CI en verde y PR fusionado — prueba: `gh pr checks` y `gh pr view`.

## Evidence

- Antes: `composer audit` 24 avisos en 6 paquetes (crítico: laravel/framework CVE-2026-114509, bypass
  de autenticación de TokenGuard; altos en guzzle y commonmark). `npm audit`: 9 (1 crítica en
  shell-quote, 6 altas incluida vite).
- `composer update`: 69 actualizados y 2 añadidos, sin cambiar versiones mayores de la aplicación
  (laravel/framework 12.63.0 → 12.70.1, guzzle 7.14.0 → 7.16.0, commonmark 2.8.2 → 2.10.4,
  symfony 7.4.x). Solo paquetes de desarrollo de laravel/boost cambian de mayor (laravel/mcp 1.1,
  laravel/roster 1.0, hamcrest 3.0).
- `npm audit fix` (sin --force): solo cambia package-lock.json.
- Después: `composer audit` → "No security vulnerability advisories found."; `npm audit` →
  "found 0 vulnerabilities".
- Pest 105 passed (509 assertions); Pint, ESLint, Prettier y build (vite 6.4.5) ok.
- Navegador (Chrome sin ventana por CDP, SQLite temporal, datos inventados): login admin, enlace
  "Importar Excel", simulación con errores (botón confirmar desactivado), cancelar, simulación válida
  con cliente existente (diferencias y póliza coincidente visibles), descartar un cliente, confirmar
  ("1 clientes nuevos, 1 actualizados y 3 pólizas nuevas"), el dashboard encuentra "1111-bbb" y el
  cliente descartado no aparece. Sin errores de JavaScript ni respuestas 5xx.
- Defecto encontrado en el navegador: plurales ("1 pólizas nuevas", "1 clientes nuevos"). Se
  corrige en una rama aparte.

## Next

PR, CI en verde y merge.
