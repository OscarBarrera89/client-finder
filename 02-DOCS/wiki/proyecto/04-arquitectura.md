# Arquitectura

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+ (CI en 8.2), Laravel 12 |
| Puente | Inertia 2: Laravel elige la página y React la pinta. No hay router en el cliente |
| Frontend | React 19, JavaScript/JSX, Tailwind CSS 4, Vite 6 |
| Base de datos | SQLite en local; MySQL/MariaDB en el servidor. Detalle en `02-base-de-datos.md` |
| Excel | OpenSpout 4 (lee fila a fila, poca memoria) |
| Tests | Pest; CI en GitHub Actions contra SQLite, MySQL 8.0 y MariaDB 10.11 |

```text
Navegador ──▶ Laravel (rutas, middleware, controladores, modelos) ──▶ BD
                 │
                 └─ Inertia::render('Pagina', props) ──▶ resources/js/pages/Pagina.jsx
```

## Acceso y roles

- **Todo exige login**, incluida la página principal y `/api/search`. No hay registro público.
- Roles en `users.rol`: `admin` (jefe) y `empleado`.
- Middleware propio:
  - `ComprobarUsuarioActivo` (grupo `web`): echa al usuario si lo han desactivado.
  - `SoloAdmin` (alias `admin`): protege `/admin/*`.

## Rutas

| Ruta | Quién | Qué hace |
|---|---|---|
| `GET /` | Cualquier usuario | `BuscadorPage` (buscador principal) |
| `GET /dashboard` | Cualquier usuario | `DashboardController@index`: búsqueda en el servidor |
| `GET /api/search` | Cualquier usuario | `SearchController@search`: búsqueda en JSON. Límite: 60/min por usuario |
| `/admin/usuarios` | Admin | `UsuarioController`: alta, contraseña, activar/desactivar |
| `/admin/importacion` | Admin | `ImportacionController`: importar Excel |
| `/settings/*` | Cualquier usuario | Perfil, contraseña y apariencia (tema). No se puede borrar la cuenta |
| `/login`, `/forgot-password`… | Invitados | Acceso y recuperación de contraseña (`routes/auth.php`) |

`routes/api.php` usa el grupo `web` para compartir la sesión del login.

## Las dos búsquedas (las dos son intencionadas)

| | `SearchController` (`/api/search`) | `DashboardController` (`/dashboard`) |
|---|---|---|
| Devuelve | JSON | Props de Inertia para `dashboard.jsx` |
| `type=cliente` | nombre, email, teléfono, identificación | — |
| `type=poliza` | n.º de póliza, matrícula | — |
| Búsqueda única | — | nombre, identificación, matrícula, n.º de póliza y nombre de compañía |
| Límite | 20 resultados, mínimo 2 caracteres | 20 resultados |
| Tests | `tests/Feature/ApiSearchTest.php` | `tests/Feature/ClienteSearchTest.php` |

Las dos normalizan DNI y matrícula antes de buscar (`Normalizador`).

**Estado actual:** `BuscadorPage.jsx` todavía busca en datos de prueba en memoria
(`resources/js/data/clientesDemo.js`), no en `/api/search`. Se conectará cuando haya datos reales
en el servidor (ver `pendientes.md`).

## Importador de Excel

Código en `app/Importacion/`. Spec y plan en `02-DOCS/wiki/sdd/`.

1. `LectorExcel` lee el `.xlsx` y reconoce las cabeceras.
2. `Normalizador` limpia DNI, matrícula y otros valores.
3. `SimuladorImportacion` calcula qué pasaría **sin guardar nada**: clientes nuevos o existentes,
   pólizas nuevas o coincidentes, diferencias, catálogos que se crearían, avisos y errores.
4. El admin acepta o descarta cada cliente en `admin/ImportacionPage.jsx`.
5. `EjecutorImportacion` guarda todo o nada (una transacción).

La simulación caduca (`SimulacionCaducada`). Un archivo no válido lanza `ArchivoNoValido`.

## Frontend

- Entrada: `resources/js/main.jsx` (y `ssr.jsx` para SSR). Las páginas se resuelven por nombre en
  `resources/js/pages/**/*.jsx`.
- Páginas propias: `BuscadorPage`, `dashboard`, `admin/UsuariosPage`, `admin/ImportacionPage`,
  `auth/*` y `settings/*`.
- Componentes propios (`.jsx`): `Buscador`, `ResultadoCliente`, `ClienteDetalle`, `TarjetaPoliza`,
  `BarraSuperior`, `Campo`, `PantallaAcceso`, `LayoutAjustes`.
- Primitivas de shadcn/Radix en `resources/js/components/ui/` y restos del starter kit: siguen en
  `.tsx`. El código nuevo se escribe en `.js`/`.jsx`.

## Otros

- Comando `php artisan usuario:crear-admin`: crea el administrador.
- `DatabaseSeeder`: usuarios de ejemplo (`admin@example.com`, `empleado@example.com`), solo con
  `APP_ENV=local`.
- Textos y validaciones en español (`lang/es`, `APP_LOCALE=es`).
