/* ==========================================================================
   GESTIÓN DE USUARIOS - JS (Versión Ultra-Estable)
   ========================================================================== */

const API = window.API_USUARIO;
/** ID del rol funcional "Encargado de trimestralización" (se rellena al cargar roles disponibles). */
let idRolTrimestralizacion = null;

// Mapeo tipo_documento (DB: CC, CE, TI, PASAPORTE) <-> etiqueta legible
const TIPO_DOC_LABELS = {
    'CC': 'Cédula de Ciudadanía',
    'CE': 'Cédula de Extranjería',
    'PASAPORTE': 'Pasaporte'
};
const TIPO_DOC_FROM_LABEL = {};
Object.keys(TIPO_DOC_LABELS).forEach(k => { TIPO_DOC_FROM_LABEL[TIPO_DOC_LABELS[k].toLowerCase()] = k; });

const CAMPO_LABELS = {
    nombre_completo: 'Nombre completo',
    tipo_documento: 'Tipo de documento',
    numero_documento: 'Número de documento',
    correo_electronico: 'Correo electrónico',
    cargo: 'Cargo',
    modalidad: 'Tipo de instructor',
    tipo_contrato: 'Tipo de contrato',
    area_coordinador: 'Área del coordinador'
};

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

function mensajeErrorAmigable(mensaje, campo) {
    if (!mensaje) return mensaje;
    const label = CAMPO_LABELS[campo] || campo;
    return String(mensaje)
        .replace(/^El campo "([^"]+)" es obligatorio\.?$/i, `Por favor seleccione o complete: ${label}`)
        .replace(/"([^"]+)" (es obligatorio\.?)/gi, (_, f) => `${CAMPO_LABELS[f] || f} es requerido`);
}

function mostrarError(formOrContainer, mensaje, campo) {
    const cont = typeof formOrContainer === 'string' ? document.getElementById(formOrContainer) : formOrContainer;
    if (!cont) return;
    const mensajeFinal = mensajeErrorAmigable(mensaje, campo) || mensaje;
    if (cont.id && cont.id.startsWith('error') && !cont.querySelector('form')) {
        const span = cont.querySelector('.alert-error-text');
        if (span) span.textContent = mensajeFinal;
        else cont.textContent = mensajeFinal;
        cont.classList.remove('hidden');
        return;
    }
    const errGeneral = cont.querySelector('[id^="errorForm"], [id^="errorModal"], [id^="errorTabla"]');
    const errCampo = campo && cont.querySelector(`.error-input[data-field="${campo}"]`);
    cont.querySelectorAll('.error-input').forEach(el => { el.classList.add('hidden'); el.textContent = ''; });
    if (errGeneral) {
        errGeneral.classList.add('hidden');
        const span = errGeneral.querySelector('.alert-error-text');
        if (span) span.textContent = '';
        else errGeneral.textContent = '';
    }
    if (errCampo) {
        errCampo.textContent = mensajeFinal;
        errCampo.classList.remove('hidden');
        const input = cont.querySelector(`[name="${campo}"]`);
        if (input) {
            input.classList.add('border-red-500');
            input.focus();
            input.addEventListener('input', () => { input.classList.remove('border-red-500'); errCampo.classList.add('hidden'); }, { once: true });
        }
    } else if (errGeneral) {
        const span = errGeneral.querySelector('.alert-error-text');
        if (span) span.textContent = mensajeFinal;
        else errGeneral.textContent = mensajeFinal;
        errGeneral.classList.remove('hidden');
    }
}

function limpiarErrores(formOrContainer) {
    const cont = typeof formOrContainer === 'string' ? document.getElementById(formOrContainer) : formOrContainer;
    if (!cont) return;
    if (cont.id && cont.id.startsWith('error') && !cont.querySelector('form')) {
        cont.classList.add('hidden');
        cont.textContent = '';
        return;
    }
    cont.querySelectorAll('.error-input').forEach(el => { el.classList.add('hidden'); el.textContent = ''; });
    cont.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
    const errGeneral = cont.querySelector('[id^="errorForm"], [id^="errorModal"], [id^="errorTabla"]');
    if (errGeneral) {
        errGeneral.classList.add('hidden');
        const span = errGeneral.querySelector('.alert-error-text');
        if (span) span.textContent = '';
        else errGeneral.textContent = '';
    }
}

/** Devuelve el value exacto de una opción del select que coincida con val (para mostrar el que ya tenía el usuario). */
function normalizarOpcionSelect(select, val) {
    if (!select || val == null || val === '') return '';
    const v = String(val).trim();
    if (!v) return '';
    const opts = [...select.options].filter(o => o.value !== '');
    const exact = opts.find(o => o.value === v);
    if (exact) return exact.value;
    const ci = opts.find(o => o.value.toLowerCase() === v.toLowerCase());
    if (ci) return ci.value;
    const partial = opts.find(o => o.value.toLowerCase().includes(v.toLowerCase()) || v.toLowerCase().includes(o.value.toLowerCase()));
    if (partial) return partial.value;
    return opts[0] ? opts[0].value : v;
}

function setSelectValue(select, val) {
    if (!select || val == null || val === '') return;
    const v = String(val).trim();
    if (!v) return;
    const opts = [...select.options];
    const exact = opts.find(o => o.value === v);
    if (exact) { select.value = v; select.dispatchEvent(new Event('change', { bubbles: true })); return; }
    const ci = opts.find(o => o.value.toLowerCase() === v.toLowerCase());
    if (ci) { select.value = ci.value; select.dispatchEvent(new Event('change', { bubbles: true })); return; }
    const partial = opts.find(o => o.value.toLowerCase().includes(v.toLowerCase()) || v.toLowerCase().includes(o.value.toLowerCase()));
    if (partial) { select.value = partial.value; select.dispatchEvent(new Event('change', { bubbles: true })); return; }
}

function campoDesdeError(msg) {
    if (!msg || typeof msg !== 'string') return null;
    const m = msg.toLowerCase();
    if (m.includes('número') || (m.includes('numero') && m.includes('documento'))) return 'numero_documento';
    if (m.includes('tipo') && m.includes('documento')) return 'tipo_documento';
    if (m.includes('correo')) return 'correo_electronico';
    if (m.includes('nombre')) return 'nombre_completo';
    if (m.includes('modalidad') || m.includes('tipo de instructor')) return 'modalidad';
    if (m.includes('tipo de contrato') || m.includes('contrato')) return 'tipo_contrato';
    if (m.includes('área') || (m.includes('area') && m.includes('coordinador'))) return 'area_coordinador';
    if (m.includes('cargo')) return 'cargo';
    return null;
}

function validarFormulario(form) {
    limpiarErrores(form);
    const required = form.querySelectorAll('[required]');
    const visible = [...required].filter(el => {
        const parent = el.closest('.grupoInstructor, .grupoCoordinador');
        return !parent || !parent.classList.contains('hidden');
    });
    for (const el of visible) {
        const val = (el.value || '').trim();
        if (!val) {
            const field = el.getAttribute('name');
            const label = el.closest('div')?.querySelector('label')?.textContent?.trim() || CAMPO_LABELS[field] || field;
            mostrarError(form, `Por favor, complete o seleccione: ${label}`, field);
            el.focus();
            return false;
        }
        if (el.name === 'numero_documento') {
            const num = parseInt(val, 10);
            if (isNaN(num) || num < 1 || num > 999999999999) {
                mostrarError(form, 'El número de documento debe tener máximo 12 dígitos.', 'numero_documento');
                el.focus();
                return false;
            }
        }
    }
    return true;
}

/**
 * Petición centralizada
 */
async function apiRequest(accion, method = "GET", body = null) {
    let url = `${API}?accion=${accion}`;
    const config = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (body && method === "GET") {
        const params = new URLSearchParams(body);
        url += "&" + params.toString();
    } else if (body) {
        config.body = JSON.stringify(body);
    }

    try {
        const res = await fetch(url, config);
        return await res.json();
    } catch (error) {
        console.error("Error:", error);
        return { success: false, error: "Error de conexión" };
    }
}

/* =============================================
   1. CONTROL DE MODALES
   ============================================= */

function abrirModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
        console.warn("No se encontró el modal con id:", id);
        return;
    }
    const parent = modal.parentElement;
    // Cerrar todos los modales antes de abrir uno para evitar superposición
    cerrarModal('modalNuevoUsuario');
    cerrarModal('modalEditarUsuario');
    cerrarModal('modalVerUsuario');
    if (parent && getComputedStyle(parent).display === 'none') {
        document.body.appendChild(modal);
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.display = "flex";
    modal.style.zIndex = "60";
    document.body.style.overflow = 'hidden';
    // Evitar que el wheel dispare scroll en la tabla de atrás: capturar wheel en el overlay
    if (!modal._wheelCapture) {
        modal._wheelCapture = function (e) {
            var box = modal.querySelector('.modal-usuario-box');
            var scrollable = box ? (box.querySelector('.modal-usuario-body') || box.querySelector('.flex-1.overflow-y-auto') || box.querySelector('[class*="overflow-y-auto"]')) : null;
            var target = e.target;
            if (scrollable && (scrollable === target || scrollable.contains(target))) {
                scrollable.scrollTop += e.deltaY;
            }
            e.preventDefault();
            e.stopPropagation();
        };
        modal.addEventListener('wheel', modal._wheelCapture, { passive: false });
    }
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.style.display = "none";
    modal.style.zIndex = "50";
    var anyOpen = document.querySelector('#modalNuevoUsuario.flex, #modalEditarUsuario.flex, #modalVerUsuario.flex');
    if (!anyOpen) {
        document.body.style.overflow = '';
    }
}

function alternarCamposCargo(cargo, contenedorModal) {
    if (!contenedorModal) return;
    const grupoIns = contenedorModal.querySelector('.grupoInstructor');
    const grupoCoor = contenedorModal.querySelector('.grupoCoordinador');
    const modalidad = contenedorModal.querySelector('[name="modalidad"]');
    const tipoContrato = contenedorModal.querySelector('[name="tipo_contrato"]');
    const areaCoord = contenedorModal.querySelector('[name="area_coordinador"]');

    const cargoStr = String(cargo || '').trim().toLowerCase();

    if (cargoStr.includes('instructor')) {
        grupoIns?.classList.remove('hidden');
        grupoCoor?.classList.add('hidden');
        if (modalidad) { modalidad.required = true; }
        if (tipoContrato) { tipoContrato.required = true; }
        if (areaCoord) { areaCoord.required = false; areaCoord.value = ''; }
    } else if (cargoStr.includes('coordinador')) {
        grupoIns?.classList.add('hidden');
        grupoCoor?.classList.remove('hidden');
        if (modalidad) { modalidad.required = false; modalidad.value = ''; modalidad.dispatchEvent(new Event('change', { bubbles: true })); }
        if (tipoContrato) { tipoContrato.required = false; tipoContrato.value = ''; tipoContrato.dispatchEvent(new Event('change', { bubbles: true })); }
        if (areaCoord) { areaCoord.required = true; }
    } else {
        grupoIns?.classList.add('hidden');
        grupoCoor?.classList.add('hidden');
        if (modalidad) { modalidad.required = false; modalidad.value = ''; modalidad.dispatchEvent(new Event('change', { bubbles: true })); }
        if (tipoContrato) { tipoContrato.required = false; tipoContrato.value = ''; tipoContrato.dispatchEvent(new Event('change', { bubbles: true })); }
        if (areaCoord) { areaCoord.required = false; areaCoord.value = ''; }
    }
}

/* =============================================
   2. RENDERIZADO (ICONOS Y SWITCH)
   ============================================= */
function getFiltrosUsuarios() {
    const filtroCargos = document.getElementById('filtroCargos');
    const filtroRoles = document.getElementById('filtroRoles');
    const buscador = document.getElementById('buscadorUsuario');
    const cargo = (filtroCargos?.value || '').trim();
    const esInstructor = /instructor/i.test(cargo);
    return {
        cargo,
        id_rol_funcional: esInstructor && filtroRoles?.value ? filtroRoles.value : '',
        buscar: (buscador?.value || '').trim()
    };
}

async function cargarUsuarios() {
    const filtros = getFiltrosUsuarios();
    const params = {};
    if (filtros.cargo) params.cargo = filtros.cargo;
    if (filtros.id_rol_funcional) params.id_rol_funcional = filtros.id_rol_funcional;
    if (filtros.buscar) params.buscar = filtros.buscar;
    const res = await apiRequest("listar", "GET", Object.keys(params).length ? params : null);
    if (Array.isArray(res)) {
        renderTabla(res);
    }
}

function renderTabla(data) {
    const tbody = document.getElementById("tbodyUsuarios");
    if (!tbody) return;

    let filas = "";
    data.forEach(u => {
        filas += `
            <tr class="hover:bg-gray-50 border-b border-gray-100 transition-colors">
                <td class="px-6 py-4 font-medium text-gray-700">${u.numero_documento}</td>
                <td class="px-6 py-4"><span class="cell-nombre-wrap">${u.nombre_completo || ''}</span></td>
                <td class="px-6 py-4 text-gray-600">${u.cargo || '—'}</td>
                <td class="px-6 py-4 text-gray-500">${u.correo_electronico}</td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2 shrink-0">
                        <button type="button" class="btn-editar p-2 border rounded-xl text-gray-500 hover:text-[#39A900] hover:bg-gray-50 transition shrink-0" data-id="${u.id_usuario}" title="Editar usuario">
                            <img class="w-5 h-5 pointer-events-none" src="${window.ICON_EDIT_USUARIO}" alt="Editar" />
                        </button>
                        <button type="button" class="btn-ver p-2 text-gray-400 hover:text-[#0a3a57] rounded-full hover:bg-gray-100 shrink-0" data-id="${u.id_usuario}" title="Ver usuario">
                            <img class="w-5 h-5 pointer-events-none" src="${window.ICON_VER_USUARIO || ''}" alt="Ver" />
                        </button>
                        <label class="relative inline-flex items-center cursor-pointer select-none ml-2">
                            <input 
                                type="checkbox" 
                                class="sr-only peer btn-estado" 
                                data-id="${u.id_usuario}" 
                                data-estado="${u.estado}" 
                                ${u.estado == 1 ? 'checked' : ''}
                                aria-checked="${u.estado == 1 ? 'true' : 'false'}"
                                aria-label="Cambiar estado de ${u.nombre_completo}"
                            >
                            <div
                                class="w-11 h-6 rounded-full bg-gray-200 transition
                                       peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-[#39A900]/60
                                       peer-checked:bg-[#39A900] peer-disabled:opacity-60">
                            </div>
                            <div
                                class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow
                                       transition-transform duration-200 ease-out
                                       peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                </td>
            </tr>`;
    });
    tbody.innerHTML = filas;
}

/* =============================================
   3. INICIALIZACIÓN (ELIMINACIÓN DE BUGS)
   ============================================= */

function enhanceSelectsWithCustomDropdown() {
    document.querySelectorAll('.select-usuario').forEach(select => {
        if (select.dataset.customDropdown) return;
        select.dataset.customDropdown = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        const arrowSib = wrapper.nextElementSibling;
        if (arrowSib && (arrowSib.classList?.contains('pointer-events-none') || arrowSib.matches?.('svg.absolute'))) arrowSib.style.display = 'none';

        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger ' + (select.classList.contains('py-2.5') ? 'py-2.5 text-sm' : 'py-3') + ' w-full border border-gray-300 rounded-xl px-4 pr-10 bg-white text-gray-700 flex items-center justify-between cursor-pointer hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900]';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('aria-haspopup', 'listbox');

        const dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown hidden';
        dropdown.setAttribute('role', 'listbox');

        const updateTrigger = () => {
            const opt = select.options[select.selectedIndex];
            span.textContent = opt ? opt.textContent : '';
        };

        const span = document.createElement('span');
        span.className = 'truncate';
        trigger.appendChild(span);
        const arrow = document.createElement('span');
        arrow.className = 'shrink-0 text-gray-400';
        arrow.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
        trigger.appendChild(arrow);

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
            if (select.disabled) return;
            const open = !dropdown.classList.contains('hidden');
            document.querySelectorAll('.custom-select-dropdown').forEach(d => d.classList.add('hidden'));
            if (!open) {
                const rect = wrapper.getBoundingClientRect();
                const dropdownMaxH = 220;
                const margin = 12;
                const modal = wrapper.closest('.modal-usuario-box');
                const spaceBelow = modal
                    ? (modal.getBoundingClientRect().bottom - rect.bottom)
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

function aplicarEstadoFiltroRoles() {
    const filtroCargos = document.getElementById('filtroCargos');
    const filtroRoles = document.getElementById('filtroRoles');
    if (!filtroRoles) return;
    const esInstructor = /instructor/i.test((filtroCargos?.value || '').trim());
    filtroRoles.disabled = !esInstructor;
    var wrapper = filtroRoles.closest('.custom-select-wrapper');
    if (wrapper) {
        if (esInstructor) {
            wrapper.classList.remove('filtro-rol-disabled');
        } else {
            wrapper.classList.add('filtro-rol-disabled');
            filtroRoles.value = '';
            var triggerSpan = wrapper.querySelector('.custom-select-trigger span:first-child');
            if (triggerSpan) triggerSpan.textContent = 'Todos los roles';
        }
    } else if (!esInstructor) {
        filtroRoles.value = '';
    }
}

async function cargarRolesDisponibles() {
    try {
        const roles = await apiRequest("rolesDisponibles");
        if (!Array.isArray(roles)) return;
        const rTrim = roles.find(r => (r.nombre_rol || r.nombreRol || '').toLowerCase().includes('trimestraliz'));
        idRolTrimestralizacion = rTrim ? (rTrim.id_rol != null ? parseInt(rTrim.id_rol, 10) : null) : null;
        const sel = document.getElementById('filtroRoles');
        if (!sel) return;
        const valorActual = sel.value;
        sel.innerHTML = '<option value="">Todos los roles</option>';
        roles.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id_rol;
            const nombre = (r.nombre_rol || r.nombreRol || '').trim();
            opt.textContent = nombre.toLowerCase().includes('trimestraliz') ? 'Encargado de trimestralización' : (nombre || ('Rol ' + r.id_rol));
            sel.appendChild(opt);
        });
        if (valorActual) sel.value = valorActual;
        aplicarEstadoFiltroRoles();
        if (sel.closest('.custom-select-wrapper')) refreshCustomDropdownOptions(sel);
    } catch (e) {
        console.warn('No se pudieron cargar roles para el filtro:', e);
    }
}

function refreshCustomDropdownOptions(select) {
    const wrapper = select.closest('.custom-select-wrapper');
    if (!wrapper) return;
    const dropdown = wrapper.querySelector('.custom-select-dropdown');
    const triggerSpan = wrapper.querySelector('.custom-select-trigger span:first-child');
    if (!dropdown || !triggerSpan) return;
    dropdown.innerHTML = '';
    [...select.options].forEach((opt, i) => {
        const div = document.createElement('div');
        div.className = 'custom-option' + (opt.value === select.value ? ' selected' : '');
        div.textContent = opt.textContent;
        div.dataset.value = opt.value;
        div.dataset.index = String(i);
        div.setAttribute('role', 'option');
        div.addEventListener('click', function(e) {
            e.stopPropagation();
            select.value = opt.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            dropdown.querySelectorAll('.custom-option').forEach(function(o) { o.classList.remove('selected'); });
            div.classList.add('selected');
            triggerSpan.textContent = opt ? opt.textContent : '';
            dropdown.classList.add('hidden');
        });
        dropdown.appendChild(div);
    });
    const opt = select.options[select.selectedIndex];
    triggerSpan.textContent = opt ? opt.textContent : '';
}

function debounce(fn, ms) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), ms);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    aplicarEstadoFiltroRoles();
    cargarUsuarios();
    cargarRolesDisponibles();
    enhanceSelectsWithCustomDropdown();

    const filtroCargos = document.getElementById('filtroCargos');
    const filtroRoles = document.getElementById('filtroRoles');
    const buscadorUsuario = document.getElementById('buscadorUsuario');
    if (filtroCargos) {
        filtroCargos.addEventListener('change', () => {
            aplicarEstadoFiltroRoles();
            cargarUsuarios();
        });
    }
    if (filtroRoles) {
        filtroRoles.addEventListener('change', () => cargarUsuarios());
    }
    if (buscadorUsuario) {
        buscadorUsuario.addEventListener('input', debounce(() => cargarUsuarios(), 300));
    }

    // Única delegación de eventos en body para .btn-editar, .btn-ver y .btn-estado
    document.body.addEventListener("click", (e) => {
        const btnVer = e.target.closest(".btn-ver");
        if (btnVer) {
            e.preventDefault();
            const id = btnVer.dataset.id;
            if (id) verUsuarioDetalles(id);
            return;
        }
        const btnEditar = e.target.closest(".btn-editar");
        if (btnEditar) {
            e.preventDefault();
            const id = btnEditar.dataset.id;
            if (id) prepararEdicion(id);
            return;
        }
        const btnEstado = e.target.closest(".btn-estado");
        if (btnEstado) {
            toggleEstado(btnEstado.dataset.id, btnEstado.dataset.estado);
        }
    });

    // Limitar número de documento a 12 dígitos (modales Crear y Editar)
    const limitarNumDoc = (e) => {
        const inp = e.target;
        const val = inp.value.replace(/\D/g, '');
        if (val.length > 12) inp.value = val.slice(0, 12);
    };
    document.querySelector('#modalNuevoUsuario [name="numero_documento"]')?.addEventListener('input', limitarNumDoc);
    document.querySelector('#modalEditarUsuario [name="numero_documento"]')?.addEventListener('input', limitarNumDoc);

    // Sincroniza los custom dropdowns tras form.reset() (el reset no dispara change)
    function syncCustomSelectsAfterReset(form) {
        if (!form) return;
        form.querySelectorAll('.select-usuario').forEach(sel => {
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // Abrir Modal Nuevo
    document.getElementById('btnAbrirModalUsuario')?.addEventListener('click', (e) => {
        e.preventDefault();
        const modal = document.getElementById('modalNuevoUsuario');
        if (!modal) return;
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            syncCustomSelectsAfterReset(form);
            limpiarErrores(form);
        }
        alternarCamposCargo('', modal);
        cerrarModal('modalEditarUsuario');
        cerrarModal('modalVerUsuario');
        limpiarErrores('errorTablaUsuarios');
        abrirModal('modalNuevoUsuario');
    });

    // Cierre de modales (delegado para botones X y Cancelar)
    document.addEventListener('click', (e) => {
        if (e.target.closest('#btnCerrarModal, #btnCerrarModalEditar, #btnCancelarNuevo, #btnCancelarEditar, #btnCerrarVerUsuario')) {
            const modal = e.target.closest('.fixed.inset-0');
            if (modal) cerrarModal(modal.id);
        }
    });

    document.getElementById('btnCerrarVerUsuario')?.addEventListener('click', () => cerrarModal('modalVerUsuario'));

    // Cambio de cargo
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('selectCargoModal')) {
            alternarCamposCargo(e.target.value, e.target.closest('.fixed'));
        }
    });

    // Formulario Crear
    document.getElementById('formUsuario')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        if (!validarFormulario(form)) return;
        limpiarErrores(form);
        const datos = Object.fromEntries(new FormData(form));
        datos.password = datos.numero_documento;
        if (datos.modalidad) { datos.tipo_instructor = datos.modalidad; delete datos.modalidad; }
        if (String(datos.cargo || '').toLowerCase().includes('coordinador')) {
            datos.tipo_instructor = null;
            datos.tipo_contrato = null;
            const idArea = form.querySelector('[name="id_area"]')?.value;
            datos.id_area = idArea ? parseInt(idArea, 10) : null;
        } else {
            datos.id_area = null;
        }
        const res = await apiRequest("crear", "POST", datos);
        if (res.success) {
            const idUsuario = res.id_usuario != null ? parseInt(res.id_usuario, 10) : null;
            const cbTrim = form.querySelector('#rol_trimestralizacion_nuevo');
            if (idUsuario && idRolTrimestralizacion && cbTrim?.checked && (window.USUARIO_ID || 0) > 0) {
                const rRol = await apiRequest("asignarRol", "POST", {
                    id_usuario: idUsuario,
                    id_rol: idRolTrimestralizacion,
                    asignado_por: window.USUARIO_ID
                });
                if (!rRol.success) toast(rRol.error || "Error al asignar rol de trimestralización", "error");
            }
            cerrarModal('modalNuevoUsuario');
            toast("Usuario creado satisfactoriamente");
            cargarUsuarios();
        } else {
            mostrarError(form, res.error || "Error al guardar", campoDesdeError(res.error));
        }
    });

    // Formulario Editar
    document.getElementById('formEditarUsuario')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        if (!validarFormulario(form)) return;
        limpiarErrores(form);
        const datos = Object.fromEntries(new FormData(form));
        datos.tipo_documento = datos.tipo_documento || form.querySelector('[name="tipo_documento"]')?.value || '';
        if (datos.modalidad) { datos.tipo_instructor = datos.modalidad; delete datos.modalidad; }
        if (String(datos.cargo || '').toLowerCase().includes('coordinador')) {
            datos.tipo_instructor = null;
            datos.tipo_contrato = null;
        } else {
            datos.area_coordinador = '';
        }
        const res = await apiRequest("actualizar", "POST", datos);
        if (res.success) {
            const idUsuario = form.querySelector('[name="id_usuario"]')?.value;
            const cargo = datos.cargo || '';
            const cbTrim = form.querySelector('#rol_trimestralizacion_editar');
            const teniaRol = form.dataset.teniaRolTrimestralizacion === '1';
            if (String(cargo).toLowerCase().includes('instructor') && idRolTrimestralizacion && idUsuario && (window.USUARIO_ID || 0) > 0) {
                if (cbTrim?.checked && !teniaRol) {
                    const rRol = await apiRequest("asignarRol", "POST", {
                        id_usuario: idUsuario,
                        id_rol: idRolTrimestralizacion,
                        asignado_por: window.USUARIO_ID
                    });
                    if (!rRol.success) toast(rRol.error || "Error al asignar rol", "error");
                } else if (!cbTrim?.checked && teniaRol) {
                    const rRol = await apiRequest("quitarRol", "POST", { id_usuario: idUsuario, id_rol: idRolTrimestralizacion });
                    if (!rRol.success) toast(rRol.error || "Error al quitar rol", "error");
                }
            }
            cerrarModal('modalEditarUsuario');
            toast("Usuario actualizado correctamente");
            cargarUsuarios();
        } else {
            mostrarError(form, res.error || "Error al actualizar", campoDesdeError(res.error));
        }
    });
});

/* =============================================
   4. LOGICA DE CARGA DE DATOS
   ============================================= */

async function prepararEdicion(id) {
    try {
        const u = await apiRequest(`listar&id=${id}`);
        if (u && !u.error) {
            const form = document.getElementById('formEditarUsuario');
            const modal = document.getElementById('modalEditarUsuario');

            if (!form || !modal) {
                console.warn("No se encontró el formulario o el modal de edición.");
                return;
            }

            // Poblar campos
            form.querySelector('[name="nombre_completo"]').value = u.nombre_completo || '';
            const tdVal = (u.tipo_documento || '').toString().trim();
            const tdForSelect = /^(CC|CE|PASAPORTE)$/i.test(tdVal) ? tdVal.toUpperCase() : (TIPO_DOC_FROM_LABEL[tdVal.toLowerCase()] || tdVal || 'CC');
            setSelectValue(form.querySelector('[name="tipo_documento"]'), tdForSelect);
            form.querySelector('[name="numero_documento"]').value = u.numero_documento || '';
            form.querySelector('[name="correo_electronico"]').value = u.correo_electronico || '';
            setSelectValue(form.querySelector('[name="cargo"]'), u.cargo);

            // ID invisible
            let hiddenId = form.querySelector('[name="id_usuario"]');
            if(!hiddenId) {
                hiddenId = document.createElement('input');
                hiddenId.type = 'hidden';
                hiddenId.name = 'id_usuario';
                form.appendChild(hiddenId);
            }
            hiddenId.value = u.id_usuario;

            alternarCamposCargo(u.cargo, modal);
            limpiarErrores(form);

            if (String(u.cargo || '').toLowerCase().includes('instructor')) {
                const selModalidad = form.querySelector('[name="modalidad"]');
                const selContrato = form.querySelector('[name="tipo_contrato"]');
                const rawTipoInstructor = (u.tipo_instructor ?? u.modalidad ?? u.tipoInstructor ?? '').toString().trim();
                const rawTipoContrato = (u.tipo_contrato ?? u.tipoContrato ?? '').toString().trim();
                const valorModalidad = rawTipoInstructor ? normalizarOpcionSelect(selModalidad, rawTipoInstructor) : 'Técnico';
                const valorContrato = rawTipoContrato ? normalizarOpcionSelect(selContrato, rawTipoContrato) : 'Contratista';
                setSelectValue(selModalidad, valorModalidad);
                setSelectValue(selContrato, valorContrato);
                selModalidad?.dispatchEvent(new Event('change', { bubbles: true }));
                selContrato?.dispatchEvent(new Event('change', { bubbles: true }));
                const cbTrim = form.querySelector('#rol_trimestralizacion_editar');
                if (cbTrim) {
                    let rolesUsuario = [];
                    try {
                        rolesUsuario = await apiRequest("listarRolesUsuario", "GET", { id_usuario: u.id_usuario }) || [];
                    } catch (_) {}
                    const tieneTrim = Array.isArray(rolesUsuario) && rolesUsuario.some(r => (String(r.nombre_rol || r.nombreRol || '').toLowerCase().includes('trimestraliz')));
                    cbTrim.checked = tieneTrim;
                    form.dataset.teniaRolTrimestralizacion = tieneTrim ? '1' : '0';
                }
            } else {
                const inputArea = form.querySelector('[name="area_coordinador"]');
                if (inputArea) inputArea.value = u.nombre_area || u.area_coordinador || '';
            }

            abrirModal('modalEditarUsuario');
        } else {
            mostrarError('errorTablaUsuarios', u?.error || "No se pudo cargar la información del usuario.", null);
        }
    } catch (e) {
        console.error("Error al preparar edición:", e);
        mostrarError('errorTablaUsuarios', "Ocurrió un error al preparar la edición del usuario.", null);
    }
}

async function verUsuarioDetalles(id) {
    const modal = document.getElementById('modalVerUsuario');
    if (!modal) {
        console.warn("No se encontró el modal de ver usuario.");
        return;
    }

    limpiarErrores(document.getElementById('modalVerUsuario'));
    var loadingEl = document.getElementById('verUsuarioLoading');
    var contentEl = document.getElementById('verUsuarioContent');
    if (loadingEl) {
        loadingEl.classList.remove('hidden');
        loadingEl.classList.add('flex');
    }
    if (contentEl) contentEl.classList.add('hidden');

    abrirModal('modalVerUsuario');

    try {
        const res = await apiRequest("obtener", "GET", { id: id });
        const u = Array.isArray(res) ? res[0] : res;

        if (loadingEl) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
        }

        if (!u || u.error) {
            mostrarError('modalVerUsuario', res?.error || "No se pudo cargar la información del usuario.", null);
            if (contentEl) contentEl.classList.add('hidden');
            return;
        }

        if (contentEl) contentEl.classList.remove('hidden');

        const set = (idEl, val) => {
            const el = document.getElementById(idEl);
            if (el) el.textContent = (val != null && val !== '') ? String(val) : '—';
        };
        const nombre = u.nombre_completo || '';
        set('verNombre', nombre);
        const avatar = document.getElementById('verAvatar');
        if (avatar) avatar.textContent = nombre ? nombre.trim().split(/\s+/).map(s => s[0]).slice(0, 2).join('').toUpperCase() || '—' : '—';
        set('verCargo', u.cargo || '—');
        const esActivo = (u.estado == 1 || u.estado === '1');
        set('verEstado', esActivo ? 'Activo' : 'Inactivo');
        const estadoEl = document.getElementById('verEstado');
        if (estadoEl) {
            estadoEl.removeAttribute('style');
            estadoEl.className = 'inline-block px-3 py-1.5 rounded-full text-xs font-semibold';
            if (esActivo) {
                estadoEl.style.backgroundColor = '#C5E7B5';
                estadoEl.style.color = '#39A900';
            } else {
                estadoEl.style.backgroundColor = '#E5E7EB';
                estadoEl.style.color = '#4B5563';
            }
        }
        const tdRaw = (u.tipo_documento ?? u.tipoDocumento ?? u.tipo_doc ?? '').toString().trim();
        const td = TIPO_DOC_LABELS[tdRaw.toUpperCase()] || TIPO_DOC_LABELS[(TIPO_DOC_FROM_LABEL[tdRaw.toLowerCase()] || '').toUpperCase()] || (tdRaw || '—');
        const ti = u.tipo_instructor ?? u.modalidad ?? u.tipoInstructor ?? '';
        const tc = u.tipo_contrato ?? u.tipoContrato ?? '';
        set('verTipoDoc', td);
        set('verNumDoc', u.numero_documento ?? '');
        set('verCorreo', u.correo_electronico ?? '');

        alternarCamposCargo(u.cargo, modal);

        if (String(u.cargo || '').toLowerCase().includes('instructor')) {
            set('verTipoIns', ti);
            set('verContrato', tc);
            let rolesUsuario = [];
            try {
                rolesUsuario = await apiRequest("listarRolesUsuario", "GET", { id_usuario: id }) || [];
            } catch (_) {}
            const tieneTrim = Array.isArray(rolesUsuario) && rolesUsuario.some(r => String(r.nombre_rol || r.nombreRol || '').toLowerCase().includes('trimestraliz'));
            const verBlock = document.getElementById('verRolTrimestralizacion');
            if (verBlock) {
                verBlock.classList.toggle('hidden', !tieneTrim);
            }
        } else {
            set('verArea', u.nombre_area || u.area_coordinador || 'No asignada');
            const verBlock = document.getElementById('verRolTrimestralizacion');
            if (verBlock) verBlock.classList.add('hidden');
        }

        const progEl = document.getElementById('verProgramas');
        if (progEl) {
            progEl.innerHTML = '';
            const programas = u.programas || u.programas_vinculados || [];
            if (Array.isArray(programas) && programas.length > 0) {
                programas.forEach(p => {
                    const span = document.createElement('span');
                    span.className = 'block';
                    span.textContent = typeof p === 'string' ? p : (p.nombre_programa || p.nombre || '—');
                    progEl.appendChild(span);
                });
            } else {
                progEl.textContent = 'No hay programas vinculados';
            }
        }
    } catch (e) {
        console.error("Error al ver usuario:", e);
        if (loadingEl) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
        }
        if (contentEl) contentEl.classList.add('hidden');
        mostrarError('modalVerUsuario', "Ocurrió un error al cargar los detalles del usuario.", null);
    }
}

async function toggleEstado(id, estadoActual) {
    limpiarErrores('errorTablaUsuarios');
    const nuevoEstado = parseInt(estadoActual) === 1 ? 0 : 1;
    const res = await apiRequest('cambiarEstado', 'POST', {
        id_usuario: id,
        estado: nuevoEstado
    });

    if (!res.success) {
        mostrarError('errorTablaUsuarios', res.error || "Error al cambiar estado", null);
        toast(res.error || "Error al cambiar estado", "error");
    } else {
        toast(nuevoEstado === 1 ? "Usuario activado" : "Usuario desactivado");
    }
    cargarUsuarios(); // Recarga para sincronizar datos y visuales
}