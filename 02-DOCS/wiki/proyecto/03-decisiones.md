# Decisiones del proyecto

## Al empezar el proyecto

JavaScript (`.js`/`.jsx`) en lugar de TypeScript para el código propio.

Motivo:

El proyecto nació del starter kit de Laravel con TypeScript, pero se decidió trabajar en JavaScript.
Las primitivas de `resources/js/components/ui/` y otros restos del starter siguen en `.tsx`.

---

Sin router en el cliente (ni `react-router` ni `react-router-dom`).

Motivo:

Inertia ya gestiona la navegación: Laravel decide qué página se muestra.

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

---

Excepción única al principio 12: la migración de pólizas pasa de `2026_07_11_175802` a
`2026_07_12_153328` (solo cambia el nombre del archivo; en el SQLite local se actualizó su fila en
la tabla `migrations`, sin tocar datos).

Motivo:

Se creaba antes que compañías, ramos, tipos de vencimiento y estados, a los que apunta con claves
foráneas. SQLite lo acepta, pero MySQL/MariaDB (el servidor) no. Solo estaba aplicada en local.

---

CI en GitHub Actions (`.github/workflows/ci.yml`) en cada pull request hacia `main`: Pint, ESLint,
Prettier, build y Pest contra SQLite, MySQL 8.0 y MariaDB 10.11, con migrate → reset → migrate.
No se fusiona un PR con el CI en rojo.

Motivo:

Comprobar antes de fusionar que todo funciona también con los motores del servidor. El repositorio
es privado en plan gratuito: GitHub no puede bloquear el merge, así que la regla la aplica el agente.

---

Importador de Excel (.xlsx) en el panel de admin con OpenSpout 4. Simulación previa, aceptar o
descartar por cliente y guardado todo o nada. Clientes existentes: se actualizan sus datos con los
del Excel (una celda vacía no borra) y se añaden las pólizas que no tenían.

Motivo:

Decidido por Oscar. OpenSpout lee fila a fila con poca memoria (hosting compartido). Spec y plan en
`02-DOCS/wiki/sdd/specs/importador-excel.md` y `02-DOCS/wiki/sdd/plans/importador-excel.md`.

---

`composer.json` fija `config.platform.php = 8.2.0`.

Motivo:

El mínimo declarado es PHP 8.2 y el CI usa 8.2; sin esto Composer resuelve dependencias con el PHP
local (8.5) y puede elegir versiones que no funcionan en 8.2.

---

Python no es un stack de este proyecto. Se quita el agente `python-reviewer` del arnés y el CI falla
si `python` vuelve a aparecer en las skills o agentes de `.rsc.json`. Para actualizar el arnés se usa
`rsc sync`, nunca `rsc onboard`.

Motivo:

rsc 3.3.1 no ignora `vendor/` al escanear: encuentra el `requirements.txt` de un paquete de Composer
y cree que el proyecto es Python. `rsc sync` respeta lo declarado; `rsc onboard` vuelve a escanear y
lo reintroduciría. El CI lo detecta aunque alguien lo ejecute por error.

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