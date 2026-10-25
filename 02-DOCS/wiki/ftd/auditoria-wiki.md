---
author: Oscar
branch: docs/auditoria-wiki
status: in-progress
---
# Auditoría de la wiki contra el código

## Intent

Que la documentación diga lo mismo que el código. La auditoría del 25/10/2026 encontró páginas
falsas (`02-base-de-datos.md` describía tablas en inglés y Bootstrap) y otras incompletas o
desfasadas. Además, que el fallo de rsc que detecta Python no vuelva a pasar en este proyecto.

## Scope

Dentro:
- Reescribir `02-base-de-datos.md` a partir de las migraciones.
- Ampliar `04-arquitectura.md` (rutas, búsquedas, login y roles, importador).
- Partir `01-client-finder-documentacion.md`: la instalación pasa a `05-instalacion.md`; se quitan
  el plan de desarrollo viejo, el "siguiente paso" y el aviso de npm ya resuelto.
- Corregir `CLAUDE.md` (nombre de `tbl_tipos_vencimiento`, `/api/search` exige login, `Estado`).
- Corregir el comentario de `BuscadorPage.jsx` que apuntaba a `docs/`.
- Quitar `react-router-dom` (instalado y sin uso; la decisión era no usar router en cliente).
- rsc: quitar el agente `python-reviewer` y añadir al CI una comprobación que falle si `python`
  vuelve a aparecer en `.rsc.json`.

Fuera:
- Cambios de comportamiento de la aplicación.
- Arreglar el escáner de rsc (es código del paquete, no del proyecto).

## Checklist

- [x] `02-base-de-datos.md` coincide con las migraciones — prueba: comparación campo a campo.
- [x] `04-arquitectura.md` coincide con rutas y controladores — prueba: `php artisan route:list`.
- [x] `01` partido y `05-instalacion.md` creado — prueba: índice actualizado.
- [x] `CLAUDE.md` y comentario de `BuscadorPage.jsx` corregidos — prueba: diff.
- [x] `react-router-dom` quitado — prueba: `npm ls react-router-dom` vacío y build en verde.
- [x] Python fuera del arnés y vigilado — prueba: `rsc doctor` sano y el paso del CI falla con
  `python` y pasa sin él.
- [x] Pest, Pint, ESLint, Prettier y build en verde — prueba: salida de los comandos.
- [ ] CI en verde y PR fusionado — prueba: `gh pr checks` y `gh pr view`.

## Evidence

- Causa del fallo de Python (rsc 3.3.1, `scripts/lib/onboarding.js`): el escaneo ignora
  `node_modules`, `.venv`, `dist`… pero no `vendor/`. Encuentra el `requirements.txt` de un paquete
  de Composer y añade el stack `python`. Tampoco reconoce `composer.json` como PHP.
- `rsc sync` respeta lo declarado en `.rsc.json`; `rsc onboard` vuelve a escanear y reintroduciría
  `python`.
- `rsc uninstall python-reviewer` (3.3.1): quita el agente y el comando `python-review`;
  `rsc doctor` → `healthy: true`.
- Paso del CI "Arnés rsc sin Python": con el `.rsc.json` actual sale con 0; con una copia que
  declara `python` y `python-reviewer` sale con 1 y dice cómo quitarlos.
- `02-base-de-datos.md` reescrito campo a campo desde las 10 migraciones.
- `php artisan route:list --except-vendor`: 30 rutas, todas coinciden con `04-arquitectura.md`.
- `npm uninstall react-router-dom`: `npm ls react-router-dom react-router` → vacío.
- Pest 105 passed (509 assertions); Pint passed; ESLint sin errores; Prettier ok; build ok.

## Next

PR, CI en verde y merge.
