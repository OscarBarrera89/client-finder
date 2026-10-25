# Instalación y arranque en local

Entorno de desarrollo: Windows 11, proyecto en `C:\Proyectos\client-finder`. Necesita PHP 8.2 o
superior, Composer, Node.js 22 y Git. No hace falta MySQL: en local se usa SQLite.

## Primera vez

```powershell
composer install
npm install
Copy-Item .env.example .env          # el .env nunca se sube a GitHub
php artisan key:generate             # genera APP_KEY; nunca publicarla
New-Item database\database.sqlite -ItemType File
php artisan migrate
php artisan usuario:crear-admin      # crea el administrador
```

`.env.example` ya trae `DB_CONNECTION=sqlite`, `APP_LOCALE=es` y `SESSION_DRIVER=database`.

Opcional, solo en local: `php artisan db:seed` crea usuarios de ejemplo con datos ficticios.

## Arrancar

Todo junto (servidor, cola y Vite):

```powershell
composer run dev
```

O por separado, en dos terminales:

```powershell
php artisan serve    # aplicación: http://localhost:8000
npm run dev          # Vite: http://localhost:5173 (solo sirve los assets)
```

La aplicación se abre siempre en `http://localhost:8000`.

## Problemas habituales

| Mensaje | Solución |
|---|---|
| `No application encryption key has been specified.` | `php artisan key:generate` |
| Falta el `.env` | `Copy-Item .env.example .env` y repetir `key:generate` |
| Cambios del `.env` que no se aplican | `php artisan config:clear` |

## Comprobar antes de subir un cambio

```powershell
vendor/bin/pest
vendor/bin/pint --test
npm run lint
npm run format:check
npm run build
```

Lo mismo lo ejecuta el CI en cada pull request.

## No subir nunca

`.env`, `/vendor`, `/node_modules` y `database/database.sqlite`. Ni datos reales de clientes.
