# Client Finder — Documentación de instalación y estado actual

## 1. Descripción

**Client Finder** es una aplicación web responsive para buscar rápidamente clientes y sus pólizas de seguros.

El buscador permitirá localizar información principalmente mediante:

- Nombre
- DNI / NIE / CIF
- Matrícula
- Número de póliza

Los datos iniciales proceden de Excel. Un mismo cliente puede tener varias pólizas.

El desarrollo se realiza inicialmente en local y se probará primero en el entorno de la oficina. Posteriormente se podrá estudiar el acceso desde otros equipos, móviles y desde fuera de la oficina.

---

## 2. Stack tecnológico

### Backend
- PHP 8.2.4
- Laravel 12.63.0
- Composer 2.8.6

### Frontend
- React
- JavaScript / JSX
- Inertia.js
- Vite 6.4.3
- Tailwind CSS

### Base de datos
- SQLite inicialmente
- MySQL como posible opción futura si el despliegue lo requiere

### Control de versiones
- Git 2.47.0.windows.1
- GitHub

Repositorio:

`https://github.com/OscarBarrera89/client-finder.git`

---

## 3. Sistema operativo y ubicación

Desarrollo:

**Windows 11**

Proyecto:

`C:\Proyectos\client-finder`

PHP actual:

`C:\xampp\php\php.exe`

No es necesario arrancar MySQL de XAMPP para utilizar SQLite.

---

## 4. Versiones comprobadas

| Herramienta | Versión |
|---|---|
| PHP | 8.2.4 |
| Composer | 2.8.6 |
| Node.js | 22.11.0 |
| npm | 10.9.0 |
| Git | 2.47.0.windows.1 |
| Laravel Installer | 5.14.1 |
| Laravel | 12.63.0 |
| Vite | 6.4.3 |
| laravel-vite-plugin | 1.2.0 |

Comprobación:

```powershell
php -v
composer --version
node -v
npm -v
git --version
laravel --version
php artisan --version
```

---

## 5. Decisión: JavaScript, no TypeScript

El proyecto se inició con Laravel + Inertia + React usando TypeScript.

La decisión definitiva es utilizar:

- `.js`
- `.jsx`

y no:

- `.ts`
- `.tsx`

Por tanto, las referencias de Vite e Inertia deben apuntar a archivos `.jsx`.

---

## 6. Vite

`vite.config.js` debe utilizar:

```js
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/main.jsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
});
```

Vite funciona correctamente con:

```powershell
npm run dev
```

Actualmente muestra:

```text
VITE v6.4.3 ready
Local: http://localhost:5173/
LARAVEL v12.63.0 plugin v1.2.0
```

`http://localhost:5173` es el servidor de desarrollo de Vite.

La aplicación Laravel se abre normalmente en:

`http://localhost:8000`

---

## 7. Inertia.js

El proyecto utiliza Inertia.js para conectar Laravel y React.

No se utilizará React Router para la navegación principal.

Flujo:

```text
Navegador
    ↓
GET /
    ↓
Laravel
    ↓
Inertia::render('BuscadorPage')
    ↓
resources/js/pages/BuscadorPage.jsx
```

---

## 8. React Router

Se instaló inicialmente `react-router`, pero se decidió no utilizarlo porque Inertia gestiona la navegación entre páginas.

Se ejecutó:

```powershell
npm uninstall react-router
```

No es necesario instalar `react-router-dom` actualmente.

---

## 9. Punto de entrada React

Archivo:

`resources/js/main.jsx`

Configuración actual:

```jsx
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { route as routeFn } from 'ziggy-js';
// import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.jsx`,
            import.meta.glob('./pages/**/*.jsx')
        ),

    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },

    progress: {
        color: '#4B5563',
    },
});

// initializeTheme();
```

El sistema de apariencia está comentado temporalmente. Se puede recuperar posteriormente.

---

## 10. Página inicial

Archivo:

`resources/js/pages/BuscadorPage.jsx`

Contenido actual:

```jsx
function BuscadorPage() {
    return (
        <div className="min-h-screen bg-gray-100">
            <div className="container mx-auto px-4 py-10">

                <div className="text-center">
                    <h1 className="text-4xl font-bold">
                        Client Finder
                    </h1>

                    <p className="mt-3 text-gray-600">
                        Busca clientes y pólizas rápidamente
                    </p>
                </div>

            </div>
        </div>
    );
}

export default BuscadorPage;
```

---

## 11. Ruta principal

`routes/web.php`:

```php
<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('BuscadorPage');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
```

Comprobar rutas:

```powershell
php artisan route:list
```

---

## 12. Estructura frontend prevista

```text
resources/
└── js/
    ├── components/
    ├── layouts/
    ├── pages/
    │   └── BuscadorPage.jsx
    ├── services/
    │   └── api.js
    ├── hooks/
    └── main.jsx
```

`App.jsx` puede permanecer vacío/no utilizarse porque la aplicación utiliza Inertia.

`services/api.js` puede permanecer vacío hasta conectar el frontend con el backend.

---

## 13. Base de datos

Se utilizará **SQLite inicialmente**.

Ventajas para este proyecto:

- No necesita servidor de base de datos.
- No necesita MySQL funcionando.
- Es sencilla para trabajar desde un portátil.
- Laravel tiene soporte directo.
- Es suficiente para la primera fase y las pruebas.

Posiblemente se valore MySQL si posteriormente se despliega en un servidor.

---

## 14. Crear SQLite

Desde la raíz:

```powershell
New-Item database\database.sqlite -ItemType File
```

La base de datos será:

`database/database.sqlite`

---

## 15. Archivo `.env`

El `.env` no debe subirse a GitHub.

Si no existe:

```powershell
Copy-Item .env.example .env
```

Después:

```powershell
php artisan key:generate
```

Configuración mínima de SQLite:

```env
DB_CONNECTION=sqlite
```

No se necesita configurar servidor, puerto, usuario ni contraseña de MySQL.

Limpiar configuración:

```powershell
php artisan config:clear
```

---

## 16. APP_KEY

Laravel necesita una `APP_KEY`.

Si aparece:

```text
No application encryption key has been specified.
```

usar:

```powershell
php artisan key:generate
```

Si aparece un error indicando que `.env` no existe:

```powershell
Copy-Item .env.example .env
```

y repetir:

```powershell
php artisan key:generate
```

Nunca publicar la `APP_KEY`.

---

## 17. Base de datos y relaciones

Tablas principales:

```text
tbl_clientes
tbl_polizas
tbl_companias
tbl_ramos
tbl_tipos_vencimiento
```

Relación principal:

```text
tbl_clientes
      │
      │ 1:N
      ▼
tbl_polizas
```

Un cliente puede tener varias pólizas.

---

## 18. Convenciones de base de datos

Todo se mantiene en español.

Las tablas utilizan el prefijo:

`tbl_`

Ejemplos:

```text
tbl_clientes
tbl_polizas
tbl_companias
tbl_ramos
tbl_tipos_vencimiento
```

Las claves primarias utilizan el `id` estándar de Laravel.

En `tbl_polizas`, la referencia al cliente es:

`cliente_id`

Los códigos de compañías, ramos y tipos de vencimiento utilizan:

`codigo`

El número de póliza utiliza:

`numero_poliza`

---

## 19. `tbl_clientes`

Campos:

```text
id
nombre
identificacion
telefono
email
direccion
activo
deleted_at
created_at
updated_at
```

Características:

- `nombre` indexado.
- `identificacion` única.
- `telefono` nullable.
- `email` nullable.
- `direccion` nullable.
- `activo` por defecto `true`.
- Soft Deletes.
- Timestamps.

---

## 20. `tbl_polizas`

Campos:

```text
id
numero_poliza
cliente_id
compania_id
ramo_id
tipo_vencimiento_id
matricula
prima_anual
fecha_contratacion
activo
deleted_at
created_at
updated_at
```

Características:

- `numero_poliza` es único.
- `cliente_id` referencia a `tbl_clientes`.
- `compania_id` referencia a `tbl_companias`.
- `ramo_id` referencia a `tbl_ramos`.
- `tipo_vencimiento_id` referencia a `tbl_tipos_vencimiento`.
- `matricula` nullable e indexada.
- Cliente: `cascadeOnDelete()`.
- Compañía: `restrictOnDelete()`.
- Ramo: `restrictOnDelete()`.
- Tipo de vencimiento: `nullOnDelete()`.
- Soft Deletes.
- Timestamps.

---

## 21. `tbl_companias`

Campos:

```text
id
codigo
nombre
activo
deleted_at
created_at
updated_at
```

`codigo` es único.

---

## 22. `tbl_ramos`

Campos:

```text
id
codigo
nombre
activo
deleted_at
created_at
updated_at
```

`codigo` es único.

---

## 23. `tbl_tipos_vencimiento`

Campos:

```text
id
codigo
nombre
activo
created_at
updated_at
deleted_at
```

Tipos previstos:

- Anual
- Semestral
- Trimestral
- Mensual

---

## 24. Excel

Los datos de origen incluyen:

```text
Nombre
DNI / NIE / CIF
Matrícula
Compañía
Ramo
Nº de póliza
Prima anual
Fecha de contratación
Vencimiento
Teléfono
Email
Dirección
```

El Excel se está completando para posteriormente importarlo a SQLite.

Un mismo cliente puede tener diferentes pólizas.

---

## 25. Buscador

Búsquedas principales:

```text
Nombre
DNI / NIE / CIF
Matrícula
Número de póliza
```

Flujo previsto:

```text
Usuario
   ↓
Introduce dato
   ↓
React
   ↓
Laravel
   ↓
SQLite
   ↓
Resultados
   ↓
Cliente + pólizas
```

---

## 26. Responsive

La interfaz se diseñará desde el principio para:

- Ordenador
- Portátil
- Tablet
- Móvil

No se dejará la adaptación móvil para el final.

---

## 27. Arranque del proyecto

Abrir dos terminales en:

`C:\Proyectos\client-finder`

### Terminal 1

```powershell
php artisan serve
```

Aplicación:

`http://localhost:8000`

### Terminal 2

```powershell
npm run dev
```

Vite:

`http://localhost:5173`

La aplicación se utiliza desde:

`http://localhost:8000`

---

## 28. Migraciones

Después de configurar SQLite:

```powershell
php artisan migrate
```

Comprobar estado:

```powershell
php artisan migrate:status
```

---

## 29. Git y GitHub

Repositorio:

`client-finder`

Remote:

`https://github.com/OscarBarrera89/client-finder.git`

Comandos habituales:

```powershell
git status
```

```powershell
git add .
```

```powershell
git commit -m "Descripción del cambio"
```

```powershell
git push
```

No utilizar `git push --force` como solución habitual.

---

## 30. Archivos que no deben subirse

Especialmente:

```text
.env
/vendor
/node_modules
```

El `.env` contiene configuración privada y nunca debe subirse.

Debe mantenerse `.env.example` para documentar las variables necesarias.

---

## 31. npm audit

Inicialmente:

```text
19 vulnerabilities
```

Se ejecutó:

```powershell
npm audit fix
```

y quedaron:

```text
1 high severity vulnerability
```

Relacionada con:

`brace-expansion`

No ejecutar `npm audit fix --force` sin revisar antes las actualizaciones porque puede introducir incompatibilidades.

La vulnerabilidad pendiente no impide continuar con el desarrollo local y debe revisarse posteriormente.

---

## 32. Arquitectura

```text
┌─────────────────────────────┐
│          USUARIO            │
│ Ordenador / Tablet / Móvil  │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│         Laravel 12          │
│ Routes / Controllers / Models│
└──────────────┬──────────────┘
               │
               │ Inertia
               ▼
┌─────────────────────────────┐
│      React + JavaScript     │
│      Tailwind CSS           │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│           SQLite            │
│ Clientes / Pólizas / Datos  │
└─────────────────────────────┘
```

---

## 33. Plan de desarrollo

### Fase 1 — Entorno

- [x] Crear proyecto Laravel.
- [x] Crear repositorio GitHub.
- [x] Configurar Git.
- [x] Comprobar PHP.
- [x] Comprobar Composer.
- [x] Comprobar Node.
- [x] Comprobar npm.
- [x] Comprobar Git.
- [x] Comprobar Laravel.
- [x] Configurar Vite.
- [x] Decidir JavaScript en lugar de TypeScript.
- [x] Eliminar React Router.
- [ ] Crear `.env`.
- [ ] Generar `APP_KEY`.
- [ ] Crear SQLite.
- [ ] Configurar SQLite.
- [ ] Ejecutar migraciones.

### Fase 2 — Frontend

- [x] Crear `main.jsx`.
- [x] Configurar Inertia para `.jsx`.
- [x] Crear `BuscadorPage.jsx`.
- [ ] Diseñar buscador.
- [ ] Hacer responsive.
- [ ] Crear componentes reutilizables.
- [ ] Crear resultados.
- [ ] Crear vista de cliente.
- [ ] Crear vista de pólizas.

### Fase 3 — Backend

- [ ] Crear modelos.
- [ ] Crear relaciones.
- [ ] Crear controladores.
- [ ] Crear consultas de búsqueda.
- [ ] Crear validaciones.

### Fase 4 — Datos

- [ ] Terminar Excel.
- [ ] Revisar columnas.
- [ ] Limpiar datos.
- [ ] Crear importador.
- [ ] Importar clientes.
- [ ] Importar pólizas.
- [ ] Comprobar duplicados.
- [ ] Validar relaciones.

### Fase 5 — Pruebas

- [ ] Buscar por nombre.
- [ ] Buscar por DNI/NIE/CIF.
- [ ] Buscar por matrícula.
- [ ] Buscar por número de póliza.
- [ ] Comprobar clientes con varias pólizas.
- [ ] Comprobar móvil.
- [ ] Comprobar tablet.
- [ ] Comprobar ordenador.

### Fase 6 — Despliegue

Primera opción:

```text
Ordenador de la oficina
        ↓
Aplicación local
```

Más adelante se decidirá cómo permitir acceso desde otros equipos, móviles o desde fuera de la oficina.

---

## 34. Siguiente paso inmediato

Antes de continuar con la interfaz:

1. Crear `.env` si no existe.
2. Generar `APP_KEY`.
3. Crear `database/database.sqlite`.
4. Configurar `DB_CONNECTION=sqlite`.
5. Ejecutar `php artisan migrate`.
6. Comprobar las tablas.
7. Volver al frontend y construir el buscador visual.

---

## 35. Comandos rápidos de instalación/arranque

```powershell
Copy-Item .env.example .env
```

```powershell
php artisan key:generate
```

```powershell
New-Item database\database.sqlite -ItemType File
```

Configurar:

```env
DB_CONNECTION=sqlite
```

Migrar:

```powershell
php artisan migrate
```

Arrancar Laravel:

```powershell
php artisan serve
```

En otra terminal:

```powershell
npm run dev
```

Abrir:

```text
http://localhost:8000
```

---

## 36. Principios de desarrollo

- Código sencillo y mantenible.
- Nombres de base de datos en español.
- Tablas con prefijo `tbl_`.
- Separación clara de responsabilidades.
- Componentes React reutilizables.
- Responsive desde el principio.
- Validación de datos.
- Seguridad.
- Commits claros.
- No subir secretos al repositorio.
- Evitar dependencias innecesarias.
- No complicar la arquitectura hasta que sea necesario.
- Construir primero una versión funcional y después añadir mejoras.
