# Base de datos

Fuente de verdad: `database/migrations/`. Si esta página y una migración no coinciden, manda la
migración y hay que corregir esta página.

Motor: SQLite en local y en los tests. En el servidor, MySQL/MariaDB (el CI prueba los tres).

## Convenciones

- Tablas del dominio con prefijo `tbl_` y nombres en español.
- Clave primaria `id`; claves foráneas `<singular>_id` (`cliente_id`, `compania_id`…).
- Todas las tablas del dominio llevan `activo` (por defecto `true`), `created_at`, `updated_at` y
  `deleted_at` (SoftDeletes). No se borra nada de verdad: se desactiva o se borra "en blando".
- DNI/NIE/CIF y matrícula se guardan normalizados (mayúsculas, sin espacios ni guiones). Lo hace
  `App\Importacion\Normalizador`, y las búsquedas lo aplican también al texto buscado.

## Relaciones

```text
tbl_clientes ──1:N──▶ tbl_polizas ◀──N:1── tbl_companias
                          ▲  ▲  ▲
            tbl_ramos ────┘  │  └──── tbl_estados
                 tbl_tipos_vencimiento
```

| Clave en `tbl_polizas` | Apunta a | ¿Obligatoria? | Al borrar el padre |
|---|---|---|---|
| `cliente_id` | `tbl_clientes` | Sí | `cascade`: se borran sus pólizas |
| `compania_id` | `tbl_companias` | Sí | `restrict`: no deja borrar la compañía |
| `ramo_id` | `tbl_ramos` | Sí | `restrict`: no deja borrar el ramo |
| `tipo_vencimiento_id` | `tbl_tipos_vencimiento` | No | `set null` |
| `estado_id` | `tbl_estados` | No | `set null` |

Estas reglas actúan en un borrado real de la fila. Como la aplicación usa SoftDeletes, en el día a
día no llegan a dispararse.

## `tbl_clientes` — modelo `Cliente`

| Campo | Tipo | Notas |
|---|---|---|
| `nombre` | string(150) | Indexado |
| `identificacion` | string(20) | DNI/NIE/CIF. **Único** |
| `telefono` | string(20) | Opcional |
| `email` | string(150) | Opcional |
| `direccion` | string(255) | Opcional |

## `tbl_polizas` — modelo `Poliza`

| Campo | Tipo | Notas |
|---|---|---|
| `numero_poliza` | string(50) | **Único** |
| `cliente_id`, `compania_id`, `ramo_id` | foreignId | Obligatorias (ver tabla de relaciones) |
| `tipo_vencimiento_id`, `estado_id` | foreignId | Opcionales |
| `matricula` | string(15) | Opcional, indexada |
| `marca_modelo` | string(150) | Opcional |
| `prima_anual` | decimal(10,2) | Opcional |
| `p_produccion` | decimal(10,2) | Opcional |
| `p_cartera` | decimal(10,2) | Opcional |
| `fecha_contratacion` | date | Opcional |
| `fecha_vencimiento` | date | Opcional |
| `fecha_anulacion` | date | Opcional. Se rellena al dar de baja la póliza |
| `riesgo` | text | Opcional |
| `observaciones` | text | Opcional |

## Catálogos

`tbl_companias` (`Compania`), `tbl_ramos` (`Ramo`), `tbl_tipos_vencimiento` (`TipoVencimiento`) y
`tbl_estados` (`Estado`) tienen la misma forma:

| Campo | Tipo | Notas |
|---|---|---|
| `codigo` | string(20) | **Único** |
| `nombre` | string(150) | string(50) en `tbl_tipos_vencimiento` |

Tipos de vencimiento previstos: Anual, Semestral, Trimestral y Mensual.

## `users` — modelo `User`

Tabla de Laravel (sin prefijo `tbl_`). Además de los campos estándar (`name`, `email`, `password`,
`email_verified_at`, `remember_token`, timestamps y `deleted_at`):

| Campo | Tipo | Notas |
|---|---|---|
| `rol` | string | `admin` (jefe) o `empleado`. Por defecto `empleado` |
| `activo` | boolean | Un usuario desactivado no puede iniciar sesión |
| `tema` | string | `claro`, `oscuro` o `sistema` (por defecto) |

## Tablas técnicas de Laravel

`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches` y `failed_jobs`.
No guardan datos del negocio.

## Orden de las migraciones

Pólizas se crea después de todos sus catálogos (`2026_07_12_153328`). MySQL/MariaDB exigen que la
tabla apuntada exista antes. Ver `03-decisiones.md`.
