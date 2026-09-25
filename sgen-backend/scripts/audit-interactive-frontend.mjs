#!/usr/bin/env node
/**
 * Auditoría estática de interactividad del frontend.
 *
 * Detecta DEUDA FUNCIONAL por construcción (no depende de opiniones):
 *  1. <button> sin @click, sin type="submit" y sin href → botón muerto.
 *  2. Handlers @click/@submit que referencian funciones inexistentes en <script>.
 *  3. Handlers registrados con cuerpo vacío o solo console.log/alert → acción simulada.
 *  4. <a> con href="#", "" o "javascript:void(0)" → enlace muerto.
 *  5. <form> sin @submit.
 *  6. console.log residuales y TODO/FIXME/HACK pendientes.
 *
 * Uso: npm run audit:ui  (exit 1 si hay hallazgos críticos: útil en CI)
 */
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const JS_DIR = join(ROOT, 'resources', 'js');

/** @returns {string[]} */
function walk(dir) {
    return readdirSync(dir).flatMap((entry) => {
        const full = join(dir, entry);
        return statSync(full).isDirectory() ? walk(full) : full.endsWith('.vue') ? [full] : [];
    });
}

const issues = [];
let stats = { files: 0, buttons: 0, links: 0, forms: 0, handlers: 0 };

/**
 * Escáner de tags consciente de comillas: no corta el tag en los '>'
 * que aparezcan dentro de expresiones de atributos (v-if, v-bind, etc.).
 */
function extractTags(template, tagName) {
    const tags = [];
    const open = `<${tagName}`;
    let i = 0;
    while ((i = template.indexOf(open, i)) !== -1) {
        let j = i + open.length;
        let quote = null;
        while (j < template.length) {
            const ch = template[j];
            if (quote) {
                if (ch === quote) quote = null;
            } else if (ch === '"' || ch === "'") {
                quote = ch;
            } else if (ch === '>') {
                break;
            }
            j++;
        }
        tags.push({ text: template.slice(i, j + 1), index: i });
        i = j + 1;
    }
    return tags;
}

for (const file of walk(JS_DIR)) {
    stats.files++;
    const src = readFileSync(file, 'utf8');
    const rel = relative(ROOT, file).replaceAll('\\', '/');

    // El <template> SFC contiene slots anidados (<template #trigger>...);
    // capturar hasta el ÚLTIMO </template>, no el primero.
    const tplStart = src.search(/<template>\s*/i);
    const tplEnd = src.lastIndexOf('</template>');
    const template = tplStart !== -1 && tplEnd > tplStart ? src.slice(tplStart, tplEnd) : '';
    const script = /<script[^>]*setup[^>]*>([\s\S]*?)<\/script>/i.exec(src)?.[1] ?? '';

    // 1) Botones muertos (tag completo, consciente de comillas en atributos).
    //    Exento: botones dentro de slots con scope (<template #trigger>...)
    //    porque el componente anfitrión (BaseDropdown, etc.) los cablea.
    const slotRanges = [...template.matchAll(/<template\s+#[\w-]+(?:="[^"]*")?\s*>[\s\S]*?<\/template>/g)].map((m) => [m.index, m.index + m[0].length]);
    const insideSlot = (idx) => slotRanges.some(([a, b]) => idx >= a && idx <= b);

    for (const { text: tag, index: tagIndex } of extractTags(template, 'button')) {
        stats.buttons++;
        if (insideSlot(tagIndex)) continue;

        const hasClick = /@click|v-on:click/.test(tag);
        const isSubmit = /type="submit"|type='submit'/.test(tag);
        const hasHref = /\bhref=/.test(tag);
        const isDisabled = /\bdisabled\b/.test(tag);
        if (!hasClick && !isSubmit && !hasHref) {
            if (isDisabled) {
                issues.push({ file: rel, nivel: 'BAJO', detalle: `Funcionalidad anunciada pero deshabilitada: ${tag.replace(/\s+/g, ' ').slice(0, 120)}` });
            } else {
                issues.push({ file: rel, nivel: 'CRITICO', detalle: `Botón sin acción: ${tag.replace(/\s+/g, ' ').slice(0, 120)}` });
            }
        }
    }

    // 2) Handlers referenciados pero no mencionados en ninguna parte del <script>
    //    (vue-tsc valida la existencia; aquí verificamos también bindings por
    //    destructuring de composables, props e imports renombrados).
    const handlerRefs = [...template.matchAll(/@(?:click|submit|change|input|keydown)(?:\.[\w.]+)?="([A-Za-z_]\w*)[\s("]/g)].map((m) => m[1]);
    const scriptHasReferences = new Set([...script.matchAll(/\b[A-Za-z_]\w*\b/g)].map((m) => m[0]));
    // Nombres expuestos por slots con scope: <template #trigger="{ toggle }">
    const slotBoundNames = new Set();
    for (const slotMatch of template.matchAll(/<template\s+#[\w-]+="[^"]*\{([^}]*)\}[^"]*"/g)) {
        for (const nameMatch of slotMatch[1].matchAll(/[A-Za-z_]\w*/g)) {
            slotBoundNames.add(nameMatch[0]);
        }
    }
    for (const handler of handlerRefs) {
        stats.handlers++;
        if (!scriptHasReferences.has(handler) && !slotBoundNames.has(handler)) {
            issues.push({ file: rel, nivel: 'CRITICO', detalle: `Handler referencia "${handler}" ausente por completo en <script setup>` });
        }
    }

    // 3) Handlers vacíos o simulados
    const noopHandlers = [...script.matchAll(/(?:const|function)\s+([A-Za-z_]\w*)\s*=\s*(?:\(\s*\)|[A-Za-z_$][\w$]*)\s*=>\s*\{\s*(?:console\.log\([^)]*\)|alert\([^)]*\))?\s*\}/gs)];
    for (const [, name] of noopHandlers) {
        if (new RegExp(`@${'\\w+'}(\\.[\\w.]+)?="${name}[(\\s"]`).test(template)) {
            issues.push({ file: rel, nivel: 'ALTO', detalle: `Handler "${name}" con cuerpo vacío/simulado (no hace nada)` });
        }
    }

    // 4) Enlaces muertos
    for (const { text: m } of extractTags(template, 'a').concat(extractTags(template, 'Link'))) {
        stats.links++;
        if (/href\s*=\s*["'](#|javascript:void\(0\))?["']/.test(m)) {
            issues.push({ file: rel, nivel: 'ALTO', detalle: `Enlace muerto: ${m.slice(0, 100)}` });
        }
    }

    // 5) Formularios sin submit
    for (const { text: formTag } of extractTags(template, 'form')) {
        stats.forms++;
        if (!/@submit|v-on:submit/.test(formTag)) {
            issues.push({ file: rel, nivel: 'ALTO', detalle: `Formulario sin @submit: ${formTag.replace(/\s+/g, ' ').slice(0, 100)}` });
        }
    }

    // 6) Residuos de depuración
    const logs = src.match(/console\.log\(/g) ?? [];
    if (logs.length) issues.push({ file: rel, nivel: 'MEDIO', detalle: `${logs.length} console.log residual(es)` });

    const todos = src.match(/\b(TODO|FIXME|HACK)\b/g) ?? [];
    if (todos.length) issues.push({ file: rel, nivel: 'BAJO', detalle: `${todos.length} TODO/FIXME pendiente(s)` });
}

const porNivel = { CRITICO: 0, ALTO: 0, MEDIO: 0, BAJO: 0 };
for (const i of issues) porNivel[i.nivel]++;

console.log('╔══════════════════════════════════════════════════════════════╗');
console.log('║   AUDITORÍA DE INTERACTIVIDAD FRONTEND — SGEN Support        ║');
console.log('╚══════════════════════════════════════════════════════════════╝');
console.log(`Archivos .vue: ${stats.files} · botones: ${stats.buttons} · enlaces: ${stats.links} · forms: ${stats.forms} · handlers: ${stats.handlers}`);
console.log('');
for (const nivel of ['CRITICO', 'ALTO', 'MEDIO', 'BAJO']) {
    const list = issues.filter((i) => i.nivel === nivel);
    console.log(`── ${nivel} (${list.length}) ──`);
    for (const i of list) console.log(`   ${i.file}\n     └─ ${i.detalle}`);
}
console.log('');
console.log(`TOTAL: ${issues.length} hallazgos (${porNivel.CRITICO} críticos, ${porNivel.ALTO} altos, ${porNivel.MEDIO} medios, ${porNivel.BAJO} bajos)`);

process.exitCode = porNivel.CRITICO + porNivel.ALTO > 0 ? 1 : 0;
