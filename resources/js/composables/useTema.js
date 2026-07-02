import { computed, reactive, ref, watch, watchEffect } from 'vue';

const STORAGE_KEY = 'tema-autoescuela';

const estado = reactive({
    tema: 'dorado',
    modo: 'auto',
    tamano: 'normal',
    contraste: 'normal',
});

// Nombres de tema de la paleta genérica anterior (Fase 5), mapeados a su
// equivalente de marca para no romper localStorage de navegadores ya abiertos.
const TEMAS_LEGADO = {
    indigo: 'dorado',
    esmeralda: 'oliva',
    ambar: 'bronce',
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

let inicializado = false;

export function useTema() {
    if (!inicializado) {
        inicializado = true;
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

    return { estado, modoEfectivo };
}
