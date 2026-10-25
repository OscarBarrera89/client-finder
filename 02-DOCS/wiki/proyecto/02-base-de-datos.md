# Base de datos

## Descripción

La aplicación permitirá buscar clientes mediante nombre, identificación (DNI/NIE/CIF), matrícula, compañía o número de póliza.

Un cliente puede tener varias pólizas, por lo que la base de datos se divide en dos tablas relacionadas.

---

## Tabla: clients

Representa a una persona o empresa.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | Clave primaria |
| name | string | Nombre completo del cliente |
| identification | string | DNI, NIE o CIF |
| phone | string | Teléfono |
| email | string | Correo electrónico |
| address | string | Dirección |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Fecha de modificación |

---

## Tabla: policies

Representa una póliza contratada por un cliente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | Clave primaria |
| client_id | foreignId | Relación con clients |
| policy_number | string | Número de póliza |
| insurance_company | string | Compañía aseguradora |
| branch | string | Ramo (Auto, Hogar, Vida...) |
| license_plate | string | Matrícula (si existe) |
| annual_premium | decimal | Prima anual |
| contract_date | date | Fecha de contratación |
| expiration_type | enum | Anual, Semestral, Trimestral o Mensual |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Fecha de modificación |

---

## Relaciones

clients

1 ------ N

policies

Un cliente puede tener varias pólizas.

Cada póliza pertenece a un único cliente.

---

## Búsquedas

La aplicación deberá permitir buscar por:

- Nombre
- DNI / NIE / CIF
- Matrícula
- Número de póliza
- Compañía aseguradora

---

## Tecnologías

- Laravel 12
- React
- Bootstrap 5
- SQLite