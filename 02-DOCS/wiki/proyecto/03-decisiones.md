# Decisiones del proyecto

## 10/07/2026

Se utilizará SQLite como base de datos durante el desarrollo.

Motivo:

- No requiere servidor.
- Fácil de distribuir.
- Laravel ofrece soporte nativo.
- Si en el futuro la empresa necesita MySQL, bastará con cambiar la conexión y ejecutar las migraciones.

---

Se separan clientes y pólizas.

Motivo:

Un cliente puede tener varias pólizas.

## 25/10/2026

Se instala el arnés rsc (rsc-harness 3.3.1) para Claude Code, con flujo de ramas y pull request.

Motivo:

- Que el agente tenga memoria del proyecto, reglas fijas y un método para cada cambio.
- Se desactivan la sincronización automática de documentación, los permisos de git sin preguntar
  y la autoactualización: la app maneja datos personales y cada commit/push debe pasar por el usuario.

---

La documentación de `docs/` se mueve a `02-DOCS/wiki/proyecto/`.

Motivo:

Una sola carpeta de documentación, la que leen las skills de rsc.

---

Se ratifica la constitución v1.0.0 (`02-DOCS/wiki/sdd/constitution.md`).

Motivo:

Fijar por escrito las reglas no negociables antes de la primera feature con SDD.

---

Altas y ediciones de pólizas, clientes y catálogos: en principio, todos los usuarios (admin y empleado).

Motivo:

Decisión del usuario. Puede cambiar más adelante.

---

Los estilos son Tailwind CSS 4. Se quita el paquete `bootstrap`, que estaba instalado sin usarse.

Motivo:

Todo el frontend ya usa Tailwind; tener los dos confundía la documentación.

---

Pint no formatea `database/migrations` (`pint.json`).

Motivo:

Una migración ya aplicada no se edita (constitución, principio 12), ni siquiera por formato.

## Convenciones
 - Las tablas llevarán el prefijo tbl_.
 - Los modelos estarán en singular (Cliente, Poliza, Compania, Ramo).
 - Los controladores terminarán en Controller.
 - Todo el código propio estará en español.
 - Las claves primarias se llamarán id (convención de Laravel).
 - Las claves foráneas seguirán el patrón cliente_id, compania_id, ramo_id.
 - Todas las tablas tendrán:
 - activo
 - created_at
 - updated_at
 - deleted_at (SoftDeletes)