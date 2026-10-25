// Tema claro/oscuro. El tema elegido se guarda en la cuenta del usuario;
// en el navegador solo se recuerda el último para pintar bien el login.

const CLAVE_LOCAL = 'tema';
const consultaOscuro = window.matchMedia('(prefers-color-scheme: dark)');

let temaActual = 'sistema';

function leerTemaLocal() {
    try {
        return localStorage.getItem(CLAVE_LOCAL) || 'sistema';
    } catch {
        return 'sistema';
    }
}

function guardarTemaLocal(tema) {
    try {
        localStorage.setItem(CLAVE_LOCAL, tema);
    } catch {
        // Sin acceso a localStorage (modo privado, etc.): no pasa nada
    }
}

function pintar() {
    const oscuro = temaActual === 'oscuro' || (temaActual === 'sistema' && consultaOscuro.matches);
    document.documentElement.classList.toggle('dark', oscuro);
}

/**
 * Aplica el tema del usuario autenticado, o el último recordado si no hay sesión.
 */
export function aplicarTemaDeUsuario(usuario) {
    if (usuario?.tema) {
        temaActual = usuario.tema;
        guardarTemaLocal(usuario.tema);
    } else {
        temaActual = leerTemaLocal();
    }

    pintar();
}

// Si el tema es "sistema", seguir los cambios del dispositivo en tiempo real
consultaOscuro.addEventListener('change', pintar);
