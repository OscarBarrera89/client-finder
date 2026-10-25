# Formato común de `pendientes.md`

Versión 1 · 2026-10-25

Norma para todos los proyectos con arnés rsc. La fábrica de software leerá los `pendientes.md` de
cada proyecto, así que todos deben seguir exactamente esta forma. Copiar esta página tal cual a
`02-DOCS/wiki/harness/` de cada proyecto nuevo.

## Dónde

`02-DOCS/wiki/proyecto/pendientes.md`. Un archivo por proyecto. Lo mantiene el agente: lo lee al
empezar la sesión y lo actualiza cuando algo se decide, se hace o cambia.

## Estructura del archivo

```markdown
---
proyecto: client-finder
prefijo: CF
formato: 1
actualizado: 2026-10-25
---
# Pendientes — Client Finder

## CF-001 · Título corto
(ficha)

## CF-002 · Título corto
(ficha)

## Hecho
(una línea por pendiente terminado)
```

- **Cabecera** (entre `---`): `proyecto` es el nombre de la carpeta; `prefijo`, 2–3 letras únicas
  por proyecto; `formato`, la versión de esta norma; `actualizado`, la fecha del último cambio.
- **Pendientes abiertos:** uno por sección `##`, ordenados por código.
- **`## Hecho`:** siempre la última sección.

## La ficha

```markdown
## CF-002 · Base de datos y servidor

- estado: pendiente
- prioridad: alta
- tipo: infra
- bloqueado-por: —
- doc: —
- actualizado: 2026-10-25

Contexto en texto libre: qué es, qué se ha decidido, qué falta decidir.

- [x] Subtarea hecha
- [ ] Subtarea pendiente
```

Las seis líneas de campos van siempre, en este orden, justo debajo del título. Si un campo no
aplica, se escribe `—`.

| Campo | Valores |
|---|---|
| `estado` | `pendiente` (se puede empezar) · `en-curso` (se está haciendo) · `en-espera` (aparcado o esperando una respuesta del usuario) · `bloqueado` (depende de otro pendiente o de algo externo) |
| `prioridad` | `alta` · `media` · `baja` |
| `tipo` | `feature` · `bug` · `infra` · `docs` · `decision` · `harness` |
| `bloqueado-por` | Códigos (`CF-002`) o una frase corta si es externo (`Excel terminado`). `—` si nada |
| `doc` | Ruta del FTD o de la spec SDD que lo desarrolla. `—` si aún no hay |
| `actualizado` | Fecha `AAAA-MM-DD` del último cambio de la ficha |

Después de los campos, texto libre y, si hace falta, subtareas con `- [ ]` / `- [x]`.

## Reglas

- **Códigos:** `PREFIJO-NNN`, correlativos. Nunca se reutiliza ni se renumera un código.
- **Terminar:** cuando un pendiente se completa, su ficha sale de la lista y se añade una línea en
  `## Hecho`:

  ```markdown
  - CF-007 · Seguridad de dependencias — 2026-10-25 — 0 avisos en Composer y npm. Doc: `02-DOCS/wiki/ftd/actualizar-dependencias.md`
  ```

  El detalle queda en el FTD o la spec enlazados, no en `pendientes.md`.
- **Descartar:** igual que terminar, pero escribiendo `descartado:` delante del motivo.
- **Lo hecho antes de la v1** puede ir en `## Hecho` sin código, bajo una línea `Antes del formato:`.
- **Nada de datos personales** (nombres, DNI, pólizas reales). Este archivo lo leerá la fábrica.
- **Fechas** siempre `AAAA-MM-DD`.
- Al cambiar cualquier ficha, actualizar su `actualizado` y el de la cabecera.

## Historial de la norma

- v1 (2026-10-25): primera versión, definida en Client Finder.
