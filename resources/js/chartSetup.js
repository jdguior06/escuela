import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';

ChartJS.register(
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

// Paleta de marca fija (independiente del tema niños/jóvenes/adultos elegido,
// ver resources/css/app.css) más tintes claros para series con varios
// segmentos (doughnut/pie): los gráficos necesitan varios tonos distintos a
// la vez, no un solo color de acento.
export const paletaMarca = ['#8b5a2b', '#5f6b3f', '#4d3a28', '#c08a4f', '#8a9a63', '#7a6249'];

function colorTema(variable, fallback) {
    if (typeof window === 'undefined') return fallback;

    const valor = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();

    return valor || fallback;
}

/** Opciones base compartidas: respetan modo claro/oscuro y tipografía de marca. */
export function opcionesBase(extra = {}) {
    const textoMuted = colorTema('--color-text-muted', '#6b7280');

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: textoMuted, font: { family: 'Figtree' }, boxWidth: 12 },
            },
            tooltip: {
                titleFont: { family: 'Figtree' },
                bodyFont: { family: 'Figtree' },
            },
            ...extra.plugins,
        },
        ...extra,
    };
}

/** Escalas x/y con grillas y ticks que respetan el tema activo (para Bar/Line). */
export function escalasBase() {
    const textoMuted = colorTema('--color-text-muted', '#6b7280');
    const borde = colorTema('--color-border', '#e5e7eb');

    return {
        x: {
            ticks: { color: textoMuted, font: { family: 'Figtree' } },
            grid: { color: borde },
        },
        y: {
            ticks: { color: textoMuted, font: { family: 'Figtree' } },
            grid: { color: borde },
            beginAtZero: true,
        },
    };
}
