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