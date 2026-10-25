---
type: spec
title: Spec — Importador de clientes y pólizas desde Excel
description: QUÉ y POR QUÉ del importador .xlsx — problema, objetivos, comportamiento y criterios de aceptación.
tags: [sdd, spec, importacion, excel]
timestamp: 2026-10-25T17:00:00Z
topic: sdd
slug: importador-excel
status: planned
---

# Spec — Importador de clientes y pólizas desde Excel

> Slug: `importador-excel` · Status: planned · Created: 2026-10-25 · Aprobada por Oscar: 2026-10-25
> Inherits: [constitución](../constitution.md)

## Problem & why

La correduría tiene sus clientes y pólizas en un Excel. La aplicación está vacía: el buscador todavía
usa datos inventados. Mientras los datos no estén dentro, la aplicación no sirve para trabajar.
Meterlos a mano, cliente a cliente, es lento y propenso a errores, y el Excel se va a seguir
usando para cargar lotes nuevos.

## Cost of not building it

Sin importador, la carga inicial es manual: cientos de clientes con varias pólizas cada uno,
escritos uno a uno. Eso son días de trabajo y errores de tecleo en DNI y números de póliza, que
son justo los datos por los que se busca. La aplicación no podría usarse con datos reales hasta
terminar esa carga.

## The cheapest alternative

Una carga única por script, sin pantalla, ejecutada por un técnico en el servidor. Resuelve la
primera carga, pero no deja al admin revisar qué entra, no avisa de clientes que ya existen y
obliga a tener a un técnico con acceso al servidor cada vez que haya un lote nuevo. Por eso no basta.

## Goals

- El admin carga un `.xlsx` desde la aplicación y los clientes y pólizas quedan disponibles en el
  buscador.
- Antes de guardar nada, el admin ve una simulación: qué se va a crear, qué ya existe, qué
  cambiaría y qué errores hay.
- El admin decide cliente a cliente (o todos de golpe) qué entra.
- Nunca queda una importación a medias: o entra todo lo aceptado o no entra nada.

## Non-goals / out of scope

- Exportar a Excel.
- Importar desde CSV u otros formatos.
- Historial de importaciones guardado en la base de datos (quién importó qué y cuándo).
- Borrar o dar de baja clientes o pólizas que no aparezcan en el Excel.
- Importar desde la pantalla de un empleado: solo el admin.
- Validar la letra de control del DNI/NIE/CIF.
- Estados de póliza y fechas de vencimiento: el Excel no los trae.

## Users & context

El **admin** (el jefe de la correduría), desde el panel de administración. Tiene el Excel en su
ordenador y quiere volcarlo a la aplicación. No es técnico: necesita mensajes claros, en español,
que digan qué fila falla y por qué. Los **empleados** no importan; ven el resultado en el buscador.

## Behaviour

- Main path:
  1. El admin abre "Importar Excel" en el panel de administración y elige un archivo `.xlsx`.
  2. La aplicación lee la primera hoja. Reconoce las columnas por el texto de la cabecera, sin
     distinguir mayúsculas, tildes ni espacios: Nombre, DNI / NIE / CIF, Matrícula, Compañía, Ramo,
     Nº de póliza, Prima anual, Fecha de contratación, Vencimiento, Teléfono, Email, Dirección.
  3. Agrupa las filas por DNI: un cliente con varias filas es un solo cliente con varias pólizas.
  4. Muestra la **simulación**, sin guardar nada:
     - Un resumen: clientes nuevos, clientes existentes, pólizas nuevas, pólizas que ya existían,
       compañías, ramos y tipos de vencimiento nuevos, y número de errores.
     - Un aviso por cliente con su estado ("Nuevo" o "Ya existe") y una casilla marcada por defecto.
     - Para un cliente que ya existe: sus pólizas nuevas, sus pólizas coincidentes (ya las tenía)
       y las diferencias entre sus datos en la aplicación y en el Excel, campo a campo.
     - La lista de errores, con número de fila de Excel, columna y motivo.
  5. Si hay errores, no se puede importar: el admin corrige el Excel y lo vuelve a subir.
  6. Sin errores, el admin desmarca los clientes que no quiere (o usa "Aceptar todos") y confirma.
  7. Se guarda todo lo aceptado de una vez y se muestra el resultado con los totales.
- Al aceptar un cliente nuevo: se crea el cliente y todas sus pólizas.
- Al aceptar un cliente que ya existe: se actualizan sus datos con los del Excel y se le añaden las
  pólizas que no tenía. Sus pólizas coincidentes no se tocan.
- Al descartar un cliente: no se guarda nada de él (ni datos ni pólizas).
- Compañías, ramos y tipos de vencimiento que no existan se crean con el nombre del Excel. Los que
  ya existen (mismo nombre, sin distinguir mayúsculas ni tildes) se reutilizan.
- Normalización: el DNI y la matrícula se guardan en mayúsculas, sin espacios, guiones ni puntos.
  Los textos se guardan sin espacios sobrantes al principio y al final.
- Edge cases:
  - Archivo sin filas de datos: mensaje "El archivo no tiene filas para importar".
  - Filas totalmente vacías: se ignoran.
  - Columnas extra en el Excel: se ignoran.
  - El mismo DNI en varias filas con nombres distintos: error en esas filas.
  - El mismo DNI en varias filas con teléfono, email o dirección distintos: se usa el primer valor
    no vacío y se avisa en la simulación.
  - El mismo nº de póliza dos veces en el Excel: error en ambas filas.
  - Entre la simulación y la confirmación alguien cambia los datos: al confirmar se vuelve a
    comprobar y, si el resultado cambió, no se guarda nada y se muestra la simulación nueva.
  - El archivo subido no se conserva: se elimina al confirmar, al cancelar o al caducar la
    simulación.
- Error paths (bloquean la importación y se listan por fila):
  - Falta una columna obligatoria en la cabecera: Nombre, DNI / NIE / CIF, Nº de póliza, Compañía
    o Ramo. El mensaje dice cuál falta.
  - Falta un valor obligatorio en una fila: nombre, DNI, nº de póliza, compañía o ramo.
  - Un valor no cabe o no tiene formato válido: email mal formado, prima no numérica, fecha de
    contratación no válida, texto más largo de lo permitido.
  - El nº de póliza ya existe en la aplicación pero es de **otro** cliente.
  - El DNI o el nº de póliza pertenecen a un registro dado de baja en la aplicación.
  - El archivo no es un `.xlsx` válido, o supera el tamaño máximo: mensaje claro, sin detalles
    técnicos.
  - Un usuario que no es admin no ve la opción y no puede usarla.
  - Ningún mensaje, log ni error técnico muestra DNI, nombres ni números de póliza fuera de la
    pantalla del admin (constitución, principio 18).

## Acceptance criteria

- Given un admin y un `.xlsx` con 2 clientes nuevos (uno con 2 pólizas), When lo sube, Then la
  simulación muestra 2 clientes nuevos y 3 pólizas nuevas, y la base de datos no ha cambiado.
- Given esa simulación, When confirma con "Aceptar todos", Then existen los 2 clientes y sus 3
  pólizas, y el buscador los encuentra por nombre, DNI, matrícula y nº de póliza.
- Given un cliente existente con 1 póliza y un Excel con esa póliza y otra nueva suya, When se
  simula, Then sale como "Ya existe" con 1 póliza coincidente y 1 nueva; When se acepta, Then tiene
  2 pólizas y la coincidente no ha cambiado.
- Given un cliente existente cuyo teléfono en el Excel es distinto, When se simula, Then la
  diferencia aparece (valor actual y valor del Excel); When se acepta, Then el teléfono es el del
  Excel.
- Given una simulación con un cliente desmarcado, When se confirma, Then ese cliente y sus pólizas
  no están en la base de datos y los demás sí.
- Given un Excel con una fila sin DNI, When se sube, Then la simulación muestra el error con su
  número de fila y no se puede confirmar.
- Given un nº de póliza del Excel que en la aplicación es de otro cliente, When se sube, Then sale
  como error y no se puede confirmar.
- Given un fallo a mitad del guardado, When se confirma, Then la base de datos queda exactamente
  como antes de confirmar.
- Given un Excel con la compañía "mapfre" y la compañía "Mapfre" ya existente, When se importa,
  Then no se crea una compañía nueva.
- Given un DNI escrito como "12345678-z" en el Excel, When se importa, Then se guarda como
  "12345678Z".
- Given un empleado, When intenta abrir la importación o enviar un archivo, Then recibe un
  acceso denegado.
- Given un archivo que no es `.xlsx`, When se sube, Then se muestra un error claro y no se guarda
  nada.
- Given cualquier importación, When se revisan los logs, Then no contienen DNI, nombres ni números
  de póliza.

## Points to clarify

- **suposición tomada** — Al actualizar un cliente existente, una celda vacía en el Excel no borra
  el dato que ya hay en la aplicación. *Base:* un Excel incompleto no debería vaciar datos buenos.
  *Riesgo:* si el admin quiere borrar un teléfono desde el Excel, no podrá; tendrá que hacerlo en la
  aplicación.
- **suposición tomada** — Los tipos de vencimiento que no existen se crean, igual que compañías y
  ramos. *Base:* coherencia con los otros catálogos. *Riesgo:* erratas en el Excel ("Anaul") crean
  tipos basura que luego hay que desactivar.
- **suposición tomada** — Campos obligatorios por fila: nombre, DNI, nº de póliza, compañía y ramo.
  *Base:* los decididos en [pendientes](../../proyecto/pendientes.md), punto 1. *Riesgo:* si se
  cambian, cambia la validación.
- **suposición tomada** — Tamaño máximo del archivo: 10 MB. *Base:* holgado para miles de filas sin
  imágenes. *Riesgo:* un Excel con imágenes o muchas hojas podría superarlo.
- **suposición tomada** — La simulación caduca a la hora: pasado ese tiempo hay que volver a subir
  el archivo. *Base:* no conservar datos personales más de lo necesario. *Riesgo:* si el admin
  revisa más de una hora, pierde la revisión.
- **suposición tomada** — Las cabeceras exactas del Excel final son las de la lista de la
  documentación. *Base:* [documentación general](../../proyecto/01-client-finder-documentacion.md),
  sección Excel. *Riesgo:* el Excel no está terminado; si cambian, hay que ajustar los nombres
  reconocidos.
- **suposición tomada** — Solo se lee la primera hoja del libro. *Base:* el Excel descrito es una
  única tabla. *Riesgo:* si los datos están repartidos en varias hojas, solo entraría la primera.
- **suposición tomada** — Como el DNI y la matrícula se guardan normalizados, las dos búsquedas
  (buscador público y dashboard) normalizan igual lo que escribe el usuario, dentro de este ciclo.
  *Base:* si no, buscar "1234-ABC" no encontraría "1234ABC". *Riesgo:* toca las dos búsquedas; se
  cambia lo mismo en ambas a propósito, sin unificarlas.
- **suposición tomada** — El número de filas del Excel real no se conoce (respuesta de Oscar, 2026-10-25). *Base:* se diseña para una petición web normal y se mide con un Excel inventado de 5.000 filas. *Riesgo:* si el real es mucho mayor o el servidor más lento, habrá que pasar a segundo plano.
- **decisión diferida** — Historial de importaciones en base de datos: fuera de este ciclo.
