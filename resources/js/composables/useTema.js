import { computed, reactive, ref, watch, watchEffect } from 'vue';

const STORAGE_KEY = 'tema-autoescuela';

const estado = reactive({
    tema: 'adultos',
    modo: 'auto',
    tamano: 'normal',
    contraste: 'normal',
});

// Nombres de tema de versiones anteriores (paleta genérica de Fase 5, y luego
// los colores de marca dorado/oliva/bronce), mapeados a los 3 temas actuales
// por público (niños/jóvenes/adultos) para no romper localStorage ya guardado.
const TEMAS_LEGADO = {
    indigo: 'adultos',
    esmeralda: 'jovenes',
    ambar: 'ninos',
    dorado: 'adultos',
    oliva: 'jovenes',
    bronce: 'ninos',
};

// Reasignar el mismo valor a una propiedad reactiva no dispara los computed que dependen de
// ella (Vue omite la notificación si el valor no cambió), así que el reloj de "modo auto" usa
// este contador aparte para forzar la reevaluación periódica de `modoEfectivo`.
const relojTick = ref(0);

function cargarPreferencias() {
    try {
        const guardado = JSON.parse(localStorage.getItem(STORAGE_KEY));
        if (guardado) {
            if (guardado.tema in TEMAS_LEGADO) guardado.tema = TEMAS_LEGADO[guardado.tema];
            Object.assign(estado, guardado);
        }
    } catch {
        // localStorage vacío o corrupto: se mantienen los valores por defecto
    }
}

const modoEfectivo = computed(() => {
    void relojTick.value;

    if (estado.modo !== 'auto') return estado.modo;

    const hora = new Date().getHours();
    return hora >= 6 && hora < 19 ? 'claro' : 'oscuro';
});

// IMPORTANTE: este bloque de inicialización corre una sola vez, al evaluarse
// el módulo (import), NO dentro de `useTema()`. Si se registran `watch`/
// `watchEffect` dentro de una función llamada desde el setup() de un
// componente, Vue los ata al "effect scope" de ESE componente y los detiene
// automáticamente si esa instancia se descarta (p. ej. durante la resolución
// asíncrona de la página de Inertia) — quedaban vivos solo hasta el primer
// repintado y nunca volvían a reaccionar a cambios posteriores de `estado`.
// A nivel de módulo no hay ningún componente dueño, así que nunca se cancelan.
if (typeof window !== 'undefined') {
    cargarPreferencias();

    watch(estado, () => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(estado));
    });

    watchEffect(() => {
        const html = document.documentElement;
        html.setAttribute('data-tema', estado.tema);
        html.setAttribute('data-modo-efectivo', modoEfectivo.value);
        html.setAttribute('data-tamano', estado.tamano);
        html.setAttribute('data-contraste', estado.contraste);
    });

    setInterval(() => {
        relojTick.value++;
    }, 5 * 60 * 1000);
}

export function useTema() {
    return { estado, modoEfectivo };
}
