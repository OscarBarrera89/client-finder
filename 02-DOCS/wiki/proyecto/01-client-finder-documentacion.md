# Client Finder — Visión general

## Qué es

Aplicación web responsive para buscar rápidamente clientes y sus pólizas de seguros. Uso interno de
la oficina, con login obligatorio.

Se busca por:

- Nombre
- DNI / NIE / CIF
- Matrícula
- Número de póliza

Un mismo cliente puede tener varias pólizas. Los datos de origen vienen de un Excel, que se carga con
el importador del panel de admin.

## Dónde está cada cosa

| Tema | Página |
|---|---|
| Tablas, campos y relaciones | `02-base-de-datos.md` |
| Decisiones y su motivo | `03-decisiones.md` |
| Rutas, búsquedas, roles, importador y frontend | `04-arquitectura.md` |
| Instalar y arrancar en local | `05-instalacion.md` |
| Qué falta por hacer | `pendientes.md` |
| Reglas no negociables | `../sdd/constitution.md` |

## Datos del Excel

Columnas de origen previstas: nombre, DNI/NIE/CIF, matrícula, compañía, ramo, n.º de póliza, prima
anual, fecha de contratación, vencimiento, teléfono, email y dirección. Las cabeceras exactas que
reconoce el importador están en `../sdd/specs/importador-excel.md`.

## Flujo de una búsqueda

```text
Usuario escribe ──▶ React ──▶ Laravel ──▶ Base de datos ──▶ Cliente + sus pólizas
```

## Responsive

La interfaz se diseña desde el principio para ordenador, portátil, tablet y móvil. La adaptación al
móvil no se deja para el final.

## Despliegue

Servidor en Hostinger (centro de datos en la UE) con MySQL/MariaDB y un subdominio. Pendiente de
contratar: ver `pendientes.md`, punto 2.

## Principios de desarrollo

- Código sencillo y mantenible.
- Nombres de base de datos en español, tablas con prefijo `tbl_`.
- Separación clara de responsabilidades.
- Componentes React reutilizables.
- Responsive desde el principio.
- Validación de datos y seguridad.
- Commits claros. No subir secretos ni datos reales.
- Evitar dependencias innecesarias.
- No complicar la arquitectura hasta que sea necesario.
- Primero una versión funcional; después, mejoras.
