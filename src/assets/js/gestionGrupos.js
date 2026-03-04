const API = window.API_FICHA;
let grupos = [];
let programas = [];
let lideres = [];

function toast(msg, type = "success") {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: type,
            title: msg,
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
        });
    } else {
        alert((type === "error" ? "❌ " : type === "warning" ? "⚠ " : "✅ ") + msg);
    }
}

async function apiRequest(accion, method = "GET", body = null, queryParams = null) {
    const config = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (body) config.body = JSON.stringify(body);

    let url = `${API}?accion=${encodeURIComponent(accion)}`;
    if (queryParams && typeof queryParams === 'object') {
        const q = new URLSearchParams();
        Object.keys(queryParams).forEach(k => {
            if (queryParams[k] != null && String(queryParams[k]).trim() !== '')
                q.set(k, queryParams[k]);
        });
        const qs = q.toString();
        if (qs) url += '&' + qs;
    }
    const res = await fetch(url, config);
    return await res.json();
}

/* =========================
   CARGAR SELECTORES
========================= */
async function cargarSelectores() {
    const response = await apiRequest("obtener_datos_selectores");

    if (response.status !== "success") return;

    programas = response.data.programas ?? [];
    lideres = response.data.lideres ?? response.data.instructores ?? [];

    const selectPrograma = document.getElementById("selectProgramaModal");
    const selectLider = document.getElementById("selectLiderModal");
    const filtroPrograma = document.getElementById("filtroPrograma");

    programas.forEach(p => {
        const opt = new Option(p.nombre_programa, p.id_programa);
        selectPrograma?.appendChild(opt);
        filtroPrograma?.appendChild(opt.cloneNode(true));
    });

    lideres.forEach(l => {
        const nombre = l.nombre_instructor ?? l.nombre_completo ?? '';
        const id = l.id_instructor ?? l.id_usuario ?? l.id;
        const opt = new Option(nombre, id);
        selectLider?.appendChild(opt);
    });

    enhanceSelectsWithCustomDropdown();
}

/* =========================
   LISTAR GRUPOS (con filtro y búsqueda)
========================= */
function getFiltrosActuales() {
    const buscador = document.getElementById("buscadorGrupo");
    const filtroPrograma = document.getElementById("filtroPrograma");
    return {
        buscar: (buscador?.value ?? "").trim(),
        id_programa: (filtroPrograma?.value ?? "").trim()
    };
}

async function cargarGrupos() {
    const filtros = getFiltrosActuales();
    const response = await apiRequest("listar", "GET", null, filtros);
    if (response.status === "success") {
        grupos = response.data;
        renderTabla(grupos);
    }
}

const ICON_PENCIL_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>';

function renderTabla(data) {
    const tbody = document.getElementById("tbodyGrupos");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-gray-500 text-center">No hay grupos registrados</td></tr>`;
        return;
    }

    const fmtJornada = (j) => { if (!j) return ''; const x = String(j).toUpperCase(); return x === 'DIURNA' ? 'Diurna' : x === 'NOCTURNA' ? 'Nocturna' : x === 'MIXTA' ? 'Mixta' : j; };
    const fmtModalidad = (m) => { if (!m) return ''; const x = String(m).toUpperCase(); return x === 'A DISTANCIA' ? 'A Distancia' : (m.charAt(0).toUpperCase() + m.slice(1).toLowerCase()); };

    data.forEach(g => {
        const activo = String(g.estado ?? 1) === "1";
        const tr = document.createElement("tr");
        tr.className = "border-b hover:bg-gray-50 transition";
        tr.dataset.id = g.id_ficha ?? "";
        const programa = (g.nombre_programa ?? '').trim();
        const programaTitle = programa.replace(/"/g, '&quot;');
        const nivel = String(g.nivel ?? '').trim().toUpperCase();
        const jornada = fmtJornada(g.jornada);
        const modalidad = fmtModalidad(g.modalidad);
        const lider = (g.nombre_lider ?? '').trim().toUpperCase();
        tr.innerHTML = `
            <td class="col-numero"><span class="cell-numero">${g.numero_ficha ?? ''}</span></td>
            <td class="col-programa"><span class="cell-programa cell-programa-wrap" title="${programaTitle}">${programa || '—'}</span></td>
            <td class="col-nivel"><span class="cell-nivel cell-nivel-tag tag-pill">${nivel || '—'}</span></td>
            <td class="col-jornada"><span class="cell-jornada tag-pill">${jornada || '—'}</span></td>
            <td class="col-modalidad"><span class="cell-modalidad tag-pill">${modalidad || '—'}</span></td>
            <td class="col-lider"><span class="cell-lider cell-lider-wrap tag-pill">${lider || '—'}</span></td>
            <td class="col-acciones text-right">
                <div class="flex justify-end items-center gap-3 acciones-grupo">
                    <button type="button" class="btn-editar-grupo p-2 border rounded-lg hover:bg-gray-50 transition text-gray-600 hover:text-[#39A900]" title="Editar">
                        <span class="inline-block w-5 h-5">${ICON_PENCIL_SVG}</span>
                    </button>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer switch-estado-grupo" ${activo ? "checked" : ""} data-id="${g.id_ficha ?? ''}">
                        <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
                        <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                    </label>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    bindEventosTabla();
}

function bindEventosTabla() {
    const tbody = document.getElementById("tbodyGrupos");
    if (!tbody) return;

    tbody.querySelectorAll(".switch-estado-grupo").forEach(sw => {
        sw.replaceWith(sw.cloneNode(true));
    });
    tbody.querySelectorAll(".switch-estado-grupo").forEach(sw => {
        sw.addEventListener("change", async (e) => {
            const id = e.target.dataset.id;
            if (!id) return;
            const nuevoEstado = e.target.checked ? 1 : 0;
            try {
                const res = await apiRequest("cambiarEstado", "POST", { id_ficha: id, estado: nuevoEstado });
                if (res.status === "success") {
                    toast(nuevoEstado === 1 ? "Grupo activado correctamente" : "Grupo desactivado correctamente");
                    cargarGrupos();
                } else {
                    if (res.message) toast(res.message, "error");
                    e.target.checked = !e.target.checked;
                }
            } catch (err) {
                e.target.checked = !e.target.checked;
                toast("Error al cambiar estado", "error");
            }
        });
    });

    tbody.querySelectorAll(".btn-editar-grupo").forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    tbody.querySelectorAll(".btn-editar-grupo").forEach(btn => {
        btn.addEventListener("click", (e) => entrarModoEdicion(e));
    });
}

function entrarModoEdicion(e) {
    const row = e.target.closest("tr[data-id]");
    if (!row || row.classList.contains("editando")) return;
    const id = row.dataset.id;
    const g = grupos.find(x => String(x.id_ficha) === String(id));
    if (!g) return;

    row.classList.add("editando", "bg-gray-50");
    const optsPrograma = programas.map(p => `<option value="${p.id_programa}" ${p.id_programa == g.id_programa ? 'selected' : ''}>${p.nombre_programa || ''}</option>`).join("");
    const optsJornada = ["DIURNA", "NOCTURNA", "MIXTA"].map(j => `<option value="${j}" ${j === g.jornada ? 'selected' : ''}>${j === 'DIURNA' ? 'Diurna' : j === 'NOCTURNA' ? 'Nocturna' : 'Mixta'}</option>`).join("");
    const optsModalidad = ["PRESENCIAL", "VIRTUAL", "A DISTANCIA"].map(m => `<option value="${m}" ${m === g.modalidad ? 'selected' : ''}>${m === 'A DISTANCIA' ? 'A Distancia' : m.charAt(0) + m.slice(1).toLowerCase()}</option>`).join("");
    const optsLider = lideres.map(l => {
        const nombre = l.nombre_instructor ?? l.nombre_completo ?? '';
        const idL = l.id_instructor ?? l.id_usuario ?? l.id;
        return `<option value="${idL}" ${idL == g.id_lider_grupo ? 'selected' : ''}>${nombre}</option>`;
    }).join("");

    row.innerHTML = `
        <td class="col-numero">
            <div class="cell-edit-wrap"><input type="number" class="cell-edit numero input-enterprise w-full" value="${g.numero_ficha ?? ''}" min="1" max="999999999" /></div>
        </td>
        <td class="col-programa">
            <div class="cell-edit-wrap"><select class="cell-edit programa select-grupo input-enterprise w-full py-2.5 text-sm">${optsPrograma}</select></div>
        </td>
        <td class="col-nivel"><span class="tag-pill cell-nivel-tag">${(g.nivel ?? '—').toString().trim().toUpperCase()}</span></td>
        <td class="col-jornada">
            <div class="cell-edit-wrap"><select class="cell-edit jornada select-grupo input-enterprise w-full py-2.5 text-sm">${optsJornada}</select></div>
        </td>
        <td class="col-modalidad">
            <div class="cell-edit-wrap"><select class="cell-edit modalidad select-grupo input-enterprise w-full py-2.5 text-sm">${optsModalidad}</select></div>
        </td>
        <td class="col-lider">
            <div class="cell-edit-wrap"><select class="cell-edit lider select-grupo input-enterprise w-full py-2.5 text-sm">${optsLider}</select></div>
        </td>
        <td class="col-acciones text-right">
            <div class="acciones-edit flex items-center justify-end gap-1">
                <button type="button" class="btn-guardar-grupo btn-icon-check p-2 rounded-lg transition" title="Guardar" aria-label="Guardar">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
                <button type="button" class="btn-cancelar-grupo btn-icon-x p-2 rounded-lg transition" title="Cancelar" aria-label="Cancelar">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </td>
    `;

    row.querySelector(".btn-cancelar-grupo").addEventListener("click", () => cargarGrupos());
    enhanceSelectsWithCustomDropdown();
    row.querySelector(".btn-guardar-grupo").addEventListener("click", async () => {
        const numero = row.querySelector('.cell-edit.numero').value.trim();
        const idPrograma = row.querySelector('.cell-edit.programa').value;
        const jornada = row.querySelector('.cell-edit.jornada').value;
        const modalidad = row.querySelector('.cell-edit.modalidad').value;
        const idLider = row.querySelector('.cell-edit.lider').value;

        if (!numero || !idPrograma || !jornada || !modalidad || !idLider) {
            alert("Complete todos los campos");
            return;
        }

        try {
            const res = await apiRequest("actualizar", "POST", {
                id_ficha: id,
                numero_ficha: numero,
                id_programa: idPrograma,
                jornada,
                modalidad,
                id_lider_grupo: idLider
            });
            if (res.status === "success") {
                cargarGrupos();
            } else {
                alert(res.message || "Error al actualizar");
            }
        } catch (err) {
            alert("Error al actualizar el grupo");
        }
    });
}


/* =========================
   CUSTOM SELECT DROPDOWN
========================= */
function enhanceSelectsWithCustomDropdown() {
    document.querySelectorAll('.select-grupo').forEach(select => {
        if (select.dataset.customDropdown) return;
        select.dataset.customDropdown = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        const arrowSib = wrapper.nextElementSibling;
        if (arrowSib && (arrowSib.classList?.contains('pointer-events-none') || arrowSib.matches?.('svg.absolute'))) arrowSib.style.display = 'none';

        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger ' + (select.classList.contains('py-2.5') ? 'py-2.5 text-sm' : 'py-3') + ' w-full border border-gray-300 rounded-xl bg-white text-gray-700 cursor-pointer hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900]';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('aria-haspopup', 'listbox');

        const dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown hidden';
        dropdown.setAttribute('role', 'listbox');

        const span = document.createElement('span');
        span.className = 'truncate';
        trigger.appendChild(span);
        const arrow = document.createElement('span');
        arrow.className = 'shrink-0 text-gray-400';
        arrow.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
        trigger.appendChild(arrow);

        const updateTrigger = () => {
            const opt = select.options[select.selectedIndex];
            span.textContent = opt ? opt.textContent : '';
        };

        [...select.options].forEach((opt, i) => {
            const div = document.createElement('div');
            div.className = 'custom-option' + (opt.value === select.value ? ' selected' : '');
            div.textContent = opt.textContent;
            div.dataset.value = opt.value;
            div.dataset.index = String(i);
            div.setAttribute('role', 'option');
            div.addEventListener('click', (e) => {
                e.stopPropagation();
                select.value = opt.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                dropdown.querySelectorAll('.custom-option').forEach(o => o.classList.remove('selected'));
                div.classList.add('selected');
                updateTrigger();
                dropdown.classList.add('hidden');
            });
            dropdown.appendChild(div);
        });

        updateTrigger();

        select.classList.add('sr-only', 'absolute', 'inset-0', 'w-full', 'h-full', 'opacity-0', 'pointer-events-none');
        wrapper.appendChild(trigger);
        wrapper.appendChild(dropdown);

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const open = !dropdown.classList.contains('hidden');
            document.querySelectorAll('.custom-select-dropdown').forEach(d => d.classList.add('hidden'));
            if (!open) {
                const rect = wrapper.getBoundingClientRect();
                const dropdownMaxH = 220;
                const margin = 12;
                const modalBox = wrapper.closest('.modal-grupo-box, .modal-usuario-box');
                const spaceBelow = modalBox
                    ? (modalBox.getBoundingClientRect().bottom - rect.bottom)
                    : (window.innerHeight - rect.bottom);
                const needsUp = spaceBelow < dropdownMaxH + margin;
                dropdown.classList.toggle('dropdown-up', needsUp);
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.remove('dropdown-up');
            }
        });

        select.addEventListener('change', () => {
            updateTrigger();
            dropdown.querySelectorAll('.custom-option').forEach(o => {
                o.classList.toggle('selected', o.dataset.value === select.value);
            });
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) dropdown.classList.add('hidden');
        }, true);
    });
}

function syncCustomSelectsAfterReset(form) {
    if (!form) return;
    form.querySelectorAll('.select-grupo').forEach(sel => {
        sel.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

/* =========================
   MODAL
========================= */
function togglerModal(show = true) {
    const modal = document.getElementById("modalGrupo");
    if (show) {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.style.overflow = "hidden";
    } else {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.style.overflow = "auto";
        const form = document.getElementById("formGrupo");
        if (form) {
            form.reset();
            syncCustomSelectsAfterReset(form);
        }
    }
}


/* =========================
   INIT
========================= */
document.addEventListener("DOMContentLoaded", async () => {
    await cargarSelectores();
    await cargarGrupos();

    const buscadorGrupo = document.getElementById("buscadorGrupo");
    const filtroPrograma = document.getElementById("filtroPrograma");

    let debounceTimer = null;
    if (buscadorGrupo) {
        buscadorGrupo.addEventListener("input", () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => cargarGrupos(), 300);
        });
    }
    if (filtroPrograma) {
        filtroPrograma.addEventListener("change", () => cargarGrupos());
    }

    document.getElementById("btnAbrirModalGrupo")?.addEventListener("click", () => togglerModal(true));
    document.getElementById("btnCerrarModal")?.addEventListener("click", () => togglerModal(false));
    document.getElementById("btnCancelar")?.addEventListener("click", () => togglerModal(false));

    const inputNumero = document.getElementById("inputNumeroFicha");
    const errorText = document.getElementById("errorNumeroFicha");

    inputNumero?.addEventListener("input", () => {
        inputNumero.value = inputNumero.value.replace(/\D/g, '');
        if (inputNumero.value.length > 9) inputNumero.value = inputNumero.value.slice(0, 9);
        inputNumero.classList.remove("border-red-500", "focus:ring-red-200");
        inputNumero.classList.add("border-gray-300");
        errorText.classList.add("hidden");
    });

    document.getElementById("formGrupo")?.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = Object.fromEntries(new FormData(e.target).entries());
        const response = await apiRequest("crear", "POST", data);

        if (response.status === "success") {
            togglerModal(false);
            toast("Grupo creado correctamente");
            cargarGrupos();
        } else {

            if (response.message.includes("Ya existe una ficha")) {

                inputNumero.classList.remove("border-gray-300");
                inputNumero.classList.add("border-red-500", "focus:ring-red-200");

                errorText.textContent = response.message;
                errorText.classList.remove("hidden");

            } else {
                alert(response.message);
            }
        }
    });
});