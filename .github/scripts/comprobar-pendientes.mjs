// Comprueba que pendientes.md sigue el formato común v1 (02-DOCS/wiki/harness/formato-pendientes.md).
// La fábrica de software leerá este archivo con un programa: si el formato se rompe, el CI falla.
// Uso: node .github/scripts/comprobar-pendientes.mjs [ruta]   (desde la raíz del repositorio)
import { existsSync, readFileSync } from 'node:fs';
import process from 'node:process';

const ruta = process.argv[2] ?? '02-DOCS/wiki/proyecto/pendientes.md';
const CAMPOS = ['estado', 'prioridad', 'tipo', 'bloqueado-por', 'doc', 'actualizado'];
const VALORES = {
    estado: ['pendiente', 'en-curso', 'en-espera', 'bloqueado'],
    prioridad: ['alta', 'media', 'baja'],
    tipo: ['feature', 'bug', 'infra', 'docs', 'decision', 'harness'],
};
const FECHA = /^\d{4}-\d{2}-\d{2}$/;

const errores = [];
const texto = readFileSync(ruta, 'utf8').replace(/\r\n/g, '\n');

// Cabecera
const cabecera = texto.match(/^---\n([\s\S]*?)\n---\n/);
const meta = {};
for (const linea of cabecera ? cabecera[1].split('\n') : []) {
    const [clave, ...resto] = linea.split(':');
    meta[clave.trim()] = resto.join(':').trim();
}
if (!cabecera) errores.push('Falta la cabecera entre ---');
for (const clave of ['proyecto', 'prefijo', 'formato', 'actualizado']) {
    if (!meta[clave]) errores.push(`Cabecera: falta «${clave}»`);
}
if (meta.formato && meta.formato !== '1') errores.push(`Cabecera: formato ${meta.formato} desconocido (se espera 1)`);
if (meta.actualizado && !FECHA.test(meta.actualizado)) errores.push(`Cabecera: fecha «${meta.actualizado}» no es AAAA-MM-DD`);
if (meta.prefijo && !/^[A-Z]{2,3}$/.test(meta.prefijo)) errores.push(`Cabecera: prefijo «${meta.prefijo}» debe ser 2-3 mayúsculas`);

const prefijo = meta.prefijo ?? '';
const CODIGO = new RegExp(`\\b${prefijo}-[0-9]{3}\\b`, 'g');
const secciones = texto.split(/\n(?=## )/).slice(1);
const ultima = secciones.at(-1) ?? '';
if (ultima.split('\n')[0] !== '## Hecho') errores.push('«## Hecho» debe ser la última sección');

// Fichas abiertas
const abiertos = [];
const referencias = [];
for (const seccion of secciones.slice(0, -1)) {
    const [titulo, ...cuerpo] = seccion.split('\n');
    const m = titulo.match(new RegExp(`^## (${prefijo}-[0-9]{3}) · \\S`));
    if (!m) {
        errores.push(`Título no válido: «${titulo}» (se espera «## ${prefijo}-NNN · Título»)`);
        continue;
    }
    const codigo = m[1];
    abiertos.push(codigo);
    const lineas = cuerpo.filter((l) => l.trim() !== '').slice(0, CAMPOS.length);
    CAMPOS.forEach((campo, i) => {
        const lm = lineas[i]?.match(/^- ([a-z-]+): (.+)$/);
        if (!lm || lm[1] !== campo) {
            errores.push(`${codigo}: el campo ${i + 1} debe ser «${campo}»`);
            return;
        }
        const valor = lm[2].trim();
        if (VALORES[campo] && !VALORES[campo].includes(valor)) {
            errores.push(`${codigo}: ${campo} «${valor}» no válido (${VALORES[campo].join(', ')})`);
        }
        if (campo === 'actualizado' && !FECHA.test(valor)) errores.push(`${codigo}: fecha «${valor}» no es AAAA-MM-DD`);
        if (campo === 'bloqueado-por') referencias.push(...[...valor.matchAll(CODIGO)].map((r) => [codigo, r[0]]));
        if (campo === 'doc' && valor !== '—') {
            const doc = valor.replace(/`/g, '');
            if (!existsSync(doc)) errores.push(`${codigo}: el doc «${doc}» no existe`);
        }
    });
}

// Hecho: una línea por pendiente terminado («- CF-007 · Título — AAAA-MM-DD — ...»)
const hechos = [];
for (const linea of ultima.split('\n')) {
    const m = linea.match(new RegExp(`^- (${prefijo}-[0-9]{3}) · `));
    if (!m) continue;
    hechos.push(m[1]);
    if (!/ — \d{4}-\d{2}-\d{2} — /.test(linea)) errores.push(`Hecho ${m[1]}: falta « — AAAA-MM-DD — » tras el título`);
}

// Códigos
const todos = [...abiertos, ...hechos];
const repetidos = [...new Set(todos.filter((c, i) => todos.indexOf(c) !== i))];
if (repetidos.length) errores.push(`Códigos repetidos: ${repetidos.join(', ')}`);
for (let i = 1; i < abiertos.length; i++) {
    if (abiertos[i] <= abiertos[i - 1]) errores.push(`Orden: ${abiertos[i]} va después de ${abiertos[i - 1]}`);
}
for (const [desde, hacia] of referencias) {
    if (!todos.includes(hacia)) errores.push(`${desde}: bloqueado-por apunta a ${hacia}, que no existe`);
}

if (errores.length) {
    console.error(`${ruta} no sigue el formato v1 (02-DOCS/wiki/harness/formato-pendientes.md):`);
    for (const e of errores) console.error(`  - ${e}`);
    process.exit(1);
}
console.log(`${ruta}: formato v1 correcto. ${abiertos.length} abiertos, ${hechos.length} hechos con código.`);
