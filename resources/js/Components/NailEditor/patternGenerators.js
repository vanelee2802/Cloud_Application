/*
|--------------------------------------------------------------------------
| MUSTER-GENERATOREN
|--------------------------------------------------------------------------
|
| Erzeugt aus Einstellungen (Schiebereglern) die Farbschichten eines Musters.
| Jede Schicht ist eine SVG-Maske (nur die Form), die Farbe kommt aus der
| Farbauswahl. Ergebnis hat dasselbe Format wie "layers" aus der Datenbank:
|
|   [{ name: 'Spitze', mask: 'data:image/svg+xml,…', color: '#FFFFFF' }]
|
| Koordinaten: 200 × 300, Spitze oben, Nagelbett unten.
*/

const W = 200
const H = 300

// SVG-Text → als Bild-URL nutzbar (für mask-image)
function toDataUri(inner) {

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}" preserveAspectRatio="xMidYMid slice">${inner}</svg>`

    return 'data:image/svg+xml,' + encodeURIComponent(svg)

}

// Mittelpunkt für Drehungen
const CX = W / 2
const CY = H / 2


export const GENERATORS = {

    // -----------------------------------------------------------------
    // FRENCH
    // -----------------------------------------------------------------
    french: {
        label: 'French',
        params: [
            { key: 'tiefe',  label: 'Tiefe der Spitze', min: 8,  max: 55, step: 1, unit: '%', default: 26 },
            { key: 'bogen',  label: 'Rundung',          min: 0,  max: 30, step: 1, unit: '',  default: 13 },
            { key: 'schraeg', label: 'Schräge',         min: -20, max: 20, step: 1, unit: '', default: 0 },
        ],
        layers: [
            { name: 'Spitze', color: '#FFFFFF' },
        ],
        build(p) {

            const d  = H * p.tiefe / 100
            const c  = p.bogen * 3
            const sl = p.schraeg * 2      // linke Seite tiefer/höher als rechte

            return [
                `<path d="M0 0 H${W} V${d - sl} C${W * 0.75} ${d - sl + c} ${W * 0.25} ${d + sl + c} 0 ${d + sl} Z" fill="#000"/>`,
            ]

        },
    },

    // -----------------------------------------------------------------
    // STREIFEN
    // -----------------------------------------------------------------
    streifen: {
        label: 'Streifen',
        params: [
            { key: 'breite',  label: 'Streifenbreite', min: 2,   max: 40, step: 1, unit: 'px', default: 8 },
            { key: 'abstand', label: 'Abstand',        min: 4,   max: 60, step: 1, unit: 'px', default: 18 },
            { key: 'winkel',  label: 'Winkel',         min: -90, max: 90, step: 5, unit: '°',  default: 0 },
        ],
        layers: [
            { name: 'Streifen', color: '#FFFFFF' },
        ],
        build(p) {

            const step = p.breite + p.abstand

            return [
                `<defs><pattern id="s" width="${step}" height="${H}" patternUnits="userSpaceOnUse" patternTransform="rotate(${p.winkel} ${CX} ${CY})"><rect width="${p.breite}" height="${H}" fill="#000"/></pattern></defs>` +
                `<rect x="-${W}" y="-${H}" width="${W * 3}" height="${H * 3}" fill="url(#s)"/>`,
            ]

        },
    },

    // -----------------------------------------------------------------
    // PUNKTE
    // -----------------------------------------------------------------
    punkte: {
        label: 'Punkte',
        params: [
            { key: 'groesse', label: 'Punktgröße', min: 2,  max: 24,  step: 1,  unit: 'px', default: 7 },
            { key: 'abstand', label: 'Abstand',    min: 8,  max: 70,  step: 1,  unit: 'px', default: 28 },
            { key: 'versatz', label: 'Versatz',    min: 0,  max: 100, step: 5,  unit: '%',  default: 50 },
        ],
        layers: [
            { name: 'Punkte', color: '#FFFFFF' },
        ],
        build(p) {

            const a  = Math.max(p.abstand, p.groesse * 2 + 2)
            const ox = (a / 2 + a * p.versatz / 100) % a

            // versetzte Reihe: links, Mitte und rechts zeichnen,
            // damit Punkte am Kachelrand nicht halbiert werden
            const reihe2 = [ox - a, ox, ox + a]
                .map((x) => `<circle cx="${x}" cy="${a * 1.5}" r="${p.groesse}" fill="#000"/>`)
                .join('')

            return [
                `<defs><pattern id="d" width="${a}" height="${a * 2}" patternUnits="userSpaceOnUse">` +
                `<circle cx="${a / 2}" cy="${a / 2}" r="${p.groesse}" fill="#000"/>` +
                reihe2 +
                `</pattern></defs>` +
                `<rect width="${W}" height="${H}" fill="url(#d)"/>`,
            ]

        },
    },

    // -----------------------------------------------------------------
    // KARO
    // -----------------------------------------------------------------
    karo: {
        label: 'Karo',
        params: [
            { key: 'raster',  label: 'Rastergröße',     min: 30, max: 140, step: 2,   unit: 'px', default: 80 },
            { key: 'breite',  label: 'Streifenbreite',  min: 4,  max: 50,  step: 1,   unit: 'px', default: 26 },
            { key: 'linien',  label: 'Feine Linien',    min: 0,  max: 4,   step: 1,   unit: '',   default: 2 },
            { key: 'staerke', label: 'Linienstärke',    min: 1,  max: 6,   step: 0.5, unit: 'px', default: 2.5 },
            { key: 'winkel',  label: 'Winkel',          min: -45, max: 45, step: 5,   unit: '°',  default: 0 },
        ],
        layers: [
            { name: 'Streifen', color: '#8E3B1D' },
            { name: 'Linien',   color: '#3B2216' },
        ],
        build(p) {

            const r   = p.raster
            const b   = Math.min(p.breite, r - 4)
            const rot = `patternTransform="rotate(${p.winkel} ${CX} ${CY})"`
            const big = `x="-${W}" y="-${H}" width="${W * 3}" height="${H * 3}"`

            // Feine Linien: gleichmäßig in der Lücke neben dem breiten Streifen
            let linien = ''

            for (let i = 0; i < p.linien; i++) {

                const pos = b + (r - b) * (i + 1) / (p.linien + 1) - p.staerke / 2

                linien +=
                    `<rect x="${pos}" width="${p.staerke}" height="${r}" fill="#000"/>` +
                    `<rect y="${pos}" width="${r}" height="${p.staerke}" fill="#000"/>`

            }

            return [
                // Streifen: halbtransparent → Kreuzungen werden dunkler (typischer Karo-Look)
                `<defs><pattern id="k" width="${r}" height="${r}" patternUnits="userSpaceOnUse" ${rot}>` +
                `<rect width="${b}" height="${r}" fill="#000" fill-opacity=".6"/><rect width="${r}" height="${b}" fill="#000" fill-opacity=".6"/>` +
                `</pattern></defs><rect ${big} fill="url(#k)"/>`,

                `<defs><pattern id="l" width="${r}" height="${r}" patternUnits="userSpaceOnUse" ${rot}>${linien}</pattern></defs>` +
                `<rect ${big} fill="url(#l)"/>`,
            ]

        },
    },

}


/*
|--------------------------------------------------------------------------
| HILFSFUNKTIONEN
|--------------------------------------------------------------------------
*/

// Standardwerte eines Generators, z. B. { tiefe: 26, bogen: 13, schraeg: 0 }
export function defaultParams(type) {

    const gen = GENERATORS[type]

    if (!gen) return {}

    return Object.fromEntries(gen.params.map((p) => [p.key, p.default]))

}


// Schichten berechnen
//   type:   'french' | 'streifen' | 'punkte' | 'karo'
//   params: Einstellungen (fehlende werden mit Standardwerten ergänzt)
//   colors: eigene Farben pro Schicht, z. B. ['#B01C2E']
export function buildLayers(type, params = {}, colors = []) {

    const gen = GENERATORS[type]

    if (!gen) return []

    const p = { ...defaultParams(type), ...params }

    return gen.build(p).map((inner, index) => ({
        name:  gen.layers[index].name,
        mask:  toDataUri(inner),
        color: colors[index] ?? gen.layers[index].color,
    }))

}
