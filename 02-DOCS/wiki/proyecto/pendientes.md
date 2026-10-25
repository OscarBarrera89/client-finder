---
proyecto: client-finder
prefijo: CF
formato: 1
actualizado: 2026-10-25
---
# Pendientes — Client Finder

Mantenido por Claude Code. Formato: `02-DOCS/wiki/harness/formato-pendientes.md`.

## CF-001 · Alta y edición de pólizas y clientes

- estado: bloqueado
- prioridad: media
- tipo: feature
- bloqueado-por: CF-002
- doc: —
- actualizado: 2026-10-25

Decidido:

- Se pueden dar de alta pólizas de clientes nuevos y de clientes existentes.
- Se pueden modificar pólizas existentes.
- Desde el detalle del cliente: "Añadir póliza" (cliente ya elegido) y "Editar".
- Desde fuera (botón "Nueva póliza"): elegir "Cliente nuevo" (por defecto) o "Cliente existente".
- Dar de baja se hace desde "Editar póliza":
  - No se borra: se marca inactiva y se guarda la fecha de anulación.
  - No aparece en los resultados del buscador.
  - Buscar por su matrícula o n.º de póliza NO la encuentra.
  - En el detalle del cliente aparece marcada como "Dada de baja".
- Compañías, ramos y demás datos que completan una póliza se pueden añadir y modificar
  (solo desactivar, nunca borrar).
- Permisos (2026-10-25): en principio, **todos los usuarios (admin y empleado)** pueden dar de alta
  y editar pólizas, clientes y catálogos. Puede cambiar más adelante.
- Campos obligatorios (de momento, puede cambiar):
  - Cliente: nombre y DNI/NIE/CIF (único).
  - Póliza: n.º de póliza (único), compañía y ramo.
  - El resto, opcionales.

Bloqueado porque el buscador todavía lee datos de prueba (`resources/js/data/clientesDemo.js`): lo
que se cree en la BD no se vería hasta conectar `/api/search` (CF-002).

- [ ] Confirmar campos obligatorios definitivos.

## CF-002 · Base de datos y servidor

- estado: pendiente
- prioridad: alta
- tipo: infra
- bloqueado-por: —
- doc: —
- actualizado: 2026-10-25

- El usuario no quiere montar la BD en local: la quiere en el servidor.
- Proveedor: Hostinger (preferencia del usuario, le han dado buenas referencias).
- Pendiente decidir: hosting gestionado (Premium/Unlimited/Cloud Startup; el plan "Business" ya no
  existe con ese nombre) o VPS (KVM 1/KVM 2); centro de datos en la UE en cualquier caso.
- Dominio: opción preferida, subdominio de cordobagestion.net (p. ej. clientes.cordobagestion.net).
  Las DNS de cordobagestion.net están en Webempresa (web y correo alojados allí): NO cambiar los
  nameservers, solo añadir un registro A en el Editor de zona de Webempresa apuntando a la IP de
  Hostinger. Alternativa: dominio propio .es en Hostinger (gratis el 1.er año, ~11 EUR/año
  después). Comparativa completa en `C:\proyectos\client-finder-hosting\plan-dominio-hosting.pdf`
  (fuera del repo).
- Recomendaciones ya dadas: VPS en la UE, MySQL/MariaDB no expuesta a internet, HTTPS, copias de
  seguridad cifradas fuera del servidor, `APP_DEBUG=false`, `SESSION_DRIVER=database`.

- [ ] Elegir nombre del subdominio y conseguir acceso al panel de Webempresa.
- [ ] Elegir plan y contratar hosting.
- [ ] Instalar y configurar servidor + base de datos.
- [ ] Configurar el correo (para "recuperar contraseña").
- [ ] En el servidor: `upload_max_filesize` y `post_max_size` de PHP de al menos 10M para subir el Excel.
- [ ] Usar PHP 8.2, 8.3 u 8.4 en el servidor (OpenSpout 4 no declara soporte de 8.5).
- [ ] Conectar el buscador (`BuscadorPage`) a `/api/search` cuando haya BD.

## CF-003 · Restricción por IP de la oficina

- estado: en-espera
- prioridad: baja
- tipo: feature
- bloqueado-por: —
- doc: —
- actualizado: 2026-10-25

Aparcado hasta que el usuario lo comente. Idea: empleados solo desde la IP de la oficina; el admin
(jefe) desde cualquier sitio. No se sabe aún si la IP de la oficina es fija.

## CF-004 · Importar los datos reales desde Excel

- estado: bloqueado
- prioridad: alta
- tipo: feature
- bloqueado-por: CF-002, Excel terminado
- doc: `02-DOCS/wiki/sdd/specs/importador-excel.md`
- actualizado: 2026-10-25

El importador ya está hecho y probado (ver `## Hecho`). Falta cargar el Excel real. Orden
recomendado: hosting + subdominio (no hace falta comprar dominio) → importar el Excel real en el
servidor.

- [ ] Recibir el Excel terminado y comprobar que sus cabeceras coinciden con las que se reconocen.
- [ ] Probar con una copia en el servidor.
- [ ] Importar los datos reales.

## CF-005 · Sesiones en local

- estado: en-espera
- prioridad: baja
- tipo: infra
- bloqueado-por: —
- doc: —
- actualizado: 2026-10-25

El `.env` local usa `SESSION_DRIVER=cookie`. Recomendado: `database` (es lo que trae
`.env.example`). Pendiente de que el usuario confirme el cambio.

## CF-006 · Arnés rsc

- estado: en-espera
- prioridad: baja
- tipo: harness
- bloqueado-por: —
- doc: `02-DOCS/wiki/ftd/auditoria-wiki.md`
- actualizado: 2026-10-25

rsc-harness 3.3.1 instalado el 2026-10-25 para Claude Code (flujo: ramas + PR; `main` cerrado para
el agente). Desactivados: knowledge-sync, permisos git automáticos y auto-update. Añadidas las skills
`laravel` y `php`.

Fallo de rsc: no ignora `vendor/` al escanear y confunde el proyecto con Python. Resuelto aquí
(skill `python` y agente `python-reviewer` quitados; el CI falla si vuelven). Actualizar el arnés con
`rsc sync`, nunca `rsc onboard`.

- [ ] El usuario publica el aviso del fallo en github.com/ericrisco/rsc-harness/issues.

## CF-008 · Auditoría periódica de dependencias

- estado: pendiente
- prioridad: baja
- tipo: infra
- bloqueado-por: —
- doc: —
- actualizado: 2026-10-25

Repetir `composer audit` y `npm audit` periódicamente. El CI no lo hace todavía.

- [ ] Decidir si se añade al CI (y con qué frecuencia) o se hace a mano.

## Hecho

- CF-007 · Seguridad de dependencias — 2026-10-25 — `composer update` y `npm audit fix`: 0 avisos en Composer y npm. Doc: `02-DOCS/wiki/ftd/actualizar-dependencias.md`
- CF-009 · Importador de Excel — 2026-10-25 — en el panel de admin, con simulación, aceptar/descartar por cliente y guardado todo o nada. Medido con 5.000 filas inventadas (simulación 0,9 s, importación 4,6 s, 65 MB) y probado en el navegador. Doc: `02-DOCS/wiki/sdd/plans/importador-excel.md`
- CF-010 · Unificar documentación y constitución — 2026-10-25 — `docs/` movido a `02-DOCS/wiki/proyecto/`; constitución v1.0.0 ratificada; quitado `bootstrap`. Doc: `02-DOCS/wiki/ftd/arnes-rsc-y-documentacion.md`
- CF-011 · CI en GitHub Actions — 2026-10-25 — calidad + tests con SQLite, MySQL 8.0 y MariaDB 10.11; corregido el orden de la migración de pólizas; rutas de Inertia en minúsculas. Doc: `02-DOCS/wiki/ftd/ci-github-actions.md`
- CF-012 · Auditoría de la wiki — 2026-10-25 — wiki corregida contra el código; quitado `react-router-dom`; Python fuera del arnés y vigilado en el CI. Doc: `02-DOCS/wiki/ftd/auditoria-wiki.md`
- CF-013 · Formato común de pendientes — 2026-10-25 — este archivo pasa al formato v1. Doc: `02-DOCS/wiki/ftd/formato-pendientes.md`

Antes del formato:

- Login obligatorio (buscador, dashboard y `/api/search`).
- Roles admin/empleado y pantalla de gestión de usuarios.
- Registro público eliminado; los empleados no pueden borrar su cuenta.
- Páginas de acceso y "Mi cuenta" pasadas a .jsx.
- Tema claro/oscuro guardado en la cuenta de cada usuario.
- Usuarios de ejemplo (solo local) en `database/seeders/DatabaseSeeder.php`.
- Mensajes de error y validación en español (`lang/es`, `APP_LOCALE=es`).
