# Pendientes - Client Finder

Archivo mantenido por Claude Code. Se actualiza cuando algo se decide, se hace o cambia.
Última actualización: 25/10/2026

Leyenda: `[ ]` pendiente · `[~]` decidido en parte / en espera · `[x]` hecho

## 1. Alta y edición de pólizas y clientes `[~]`

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
- Permisos (25/10/2026): en principio, **todos los usuarios (admin y empleado)** pueden dar de alta
  y editar pólizas, clientes y catálogos. Puede cambiar más adelante.
- Campos obligatorios (de momento, puede cambiar):
  - Cliente: nombre y DNI/NIE/CIF (único).
  - Póliza: n.º de póliza (único), compañía y ramo.
  - El resto, opcionales.

Por decidir:

- [ ] Confirmar campos obligatorios definitivos.

Bloqueado por:

- Punto 2 (base de datos): el buscador todavía lee datos de prueba
  (`resources/js/data/clientesDemo.js`); lo que se cree en la BD no se vería.

## 2. Base de datos y servidor `[ ]`

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
- [ ] Conectar el buscador (`BuscadorPage`) a `/api/search` cuando haya BD.

## 3. Restricción por IP de la oficina `[~]`

- APARCADO hasta que el usuario lo comente.
- Idea: empleados solo desde la IP de la oficina; el admin (jefe) desde cualquier sitio. No se sabe
  aún si la IP de la oficina es fija.

## 4. Importar datos desde Excel `[ ]`

- Se puede hacer. Falta el Excel terminado.
- Necesario: fila de encabezados + una fila de ejemplo con datos INVENTADOS.
- Estado de la BD (25/10/2026): el esquema cubre todas las columnas documentadas del Excel. El
  orden de migraciones ya funciona en MySQL/MariaDB (ver decisiones). Falta el importador, que debe:
  agrupar filas por DNI (un cliente = varias pólizas), normalizar el DNI (mayúsculas, sin espacios
  ni guiones), crear compañías y ramos a partir del nombre (exigen `codigo` único) y convertir las
  fechas de Excel. Se puede programar ya con una fila inventada.
- Orden recomendado: importador probado con datos inventados → hosting + subdominio (no hace falta
  comprar dominio) → importar el Excel real en el servidor.
- Por decidir: CSV (sin paquetes nuevos) o .xlsx directo (requiere `phpoffice/phpspreadsheet`, con
  confirmación); comando o botón en la pantalla del admin.

## 5. Sesiones en local `[ ]`

- El `.env` local usa `SESSION_DRIVER=cookie`. Recomendado: `database`.
- Pendiente de que el usuario confirme el cambio.

## 6. Arnés rsc (rsc-harness 3.3.1) `[~]`

- Instalado el 25/10/2026 para Claude Code (flujo: ramas + PR; `main` cerrado para el agente).
  Desactivados: knowledge-sync, permisos git automáticos y auto-update (`.rsc/.no-auto-update`).
  Añadidas skills `laravel` y `php`.
- Fallo de rsc: detecta Python por `vendor/mockery/.../requirements.txt`. Skill `python` quitada;
  el agente `python-reviewer` queda (ligado al plan). Un `rsc sync` futuro podría volver a instalar
  la skill `python`. Impacto bajo: no entra en git (está en `.gitignore`) y solo ocupa contexto y
  puede activarse por error; si vuelve, `rsc uninstall python`.
- [x] Unificar documentación: `docs/` movido a `02-DOCS/wiki/proyecto/` (25/10/2026).
- [x] Constitución v1.0.0 ratificada (25/10/2026): `02-DOCS/wiki/sdd/constitution.md`.
- [x] Quitado el paquete `bootstrap` (sin uso; los estilos son Tailwind).
- [x] Commits del arnés y del trabajo pendiente en la rama `chore/arnes-rsc-y-documentacion`
  (25/10/2026).
- [x] Rama subida y pull request abierto: https://github.com/OscarBarrera89/client-finder/pull/1
- [x] Pull request #1 fusionado en `main` (25/10/2026).
- [x] CI en GitHub Actions: calidad + tests con SQLite, MySQL 8.0 y MariaDB 10.11 (25/10/2026).
- [x] Corregido el orden de la migración de pólizas (fallaba en MySQL/MariaDB).
- [x] Rutas de páginas de Inertia en minúsculas (los tests fallaban en Linux).

## Hecho

- [x] Login obligatorio (buscador, dashboard y `/api/search`).
- [x] Roles admin/empleado y pantalla de gestión de usuarios.
- [x] Registro público eliminado; los empleados no pueden borrar su cuenta.
- [x] Páginas de acceso y "Mi cuenta" pasadas a .jsx.
- [x] Tema claro/oscuro guardado en la cuenta de cada usuario.
- [x] Usuarios de ejemplo (solo local) en `database/seeders/DatabaseSeeder.php`.
- [x] Mensajes de error y validación en español (`lang/es`, `APP_LOCALE=es`).
