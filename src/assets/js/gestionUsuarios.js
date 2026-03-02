/* ==========================================================================
   GESTIÓN DE USUARIOS - JS (Versión Ultra-Estable)
   ========================================================================== */

const API = window.API_USUARIO;

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
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.style.display = "none";
    modal.style.zIndex = "50";
}

function alternarCamposCargo(cargo, contenedorModal) {
    if (!contenedorModal) return;
    const grupoIns = contenedorModal.querySelector('.grupoInstructor');
    const grupoCoor = contenedorModal.querySelector('.grupoCoordinador');
    
    if (cargo === 'Instructor') {
        grupoIns?.classList.remove('hidden');
        grupoCoor?.classList.add('hidden');
    } else {
        grupoIns?.classList.add('hidden');
        grupoCoor?.classList.remove('hidden');
    }
}

/* =============================================
   2. RENDERIZADO (ICONOS Y SWITCH)
   ============================================= */
async function cargarUsuarios() {
    const res = await apiRequest("listar");
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
                <td class="px-6 py-4">${u.nombre_completo}</td>
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

document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();

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

    // Abrir Modal Nuevo
    document.getElementById('btnAbrirModalUsuario')?.addEventListener('click', (e) => {
        e.preventDefault();
        const modal = document.getElementById('modalNuevoUsuario');
        if (!modal) return;
        modal.querySelector('form').reset();
        alternarCamposCargo('Instructor', modal);
        // Cerrar otros modales por seguridad
        cerrarModal('modalEditarUsuario');
        cerrarModal('modalVerUsuario');
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
        const datos = Object.fromEntries(new FormData(e.target));
        datos.password = datos.numero_documento;
        const res = await apiRequest("crear", "POST", datos);
        if (res.success) {
            cerrarModal('modalNuevoUsuario');
            cargarUsuarios();
        } else {
            alert(res.error || "Error al guardar");
        }
    });

    // Formulario Editar
    document.getElementById('formEditarUsuario')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const datos = Object.fromEntries(new FormData(e.target));
        const res = await apiRequest("actualizar", "POST", datos);
        if (res.success) {
            cerrarModal('modalEditarUsuario');
            cargarUsuarios();
        } else {
            alert(res.error || "Error al actualizar");
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
            form.querySelector('[name="tipo_documento"]').value = u.tipo_documento || '';
            form.querySelector('[name="numero_documento"]').value = u.numero_documento || '';
            form.querySelector('[name="correo_electronico"]').value = u.correo_electronico || '';
            form.querySelector('[name="cargo"]').value = u.cargo || '';

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
            
            if (u.cargo === 'Instructor') {
                const selModalidad = form.querySelector('[name="modalidad"]');
                const selContrato = form.querySelector('[name="tipo_contrato"]');
                if (selModalidad) selModalidad.value = u.tipo_instructor || 'Técnico';
                if (selContrato) selContrato.value = u.tipo_contrato || 'Contratista';
            } else {
                const inputArea = form.querySelector('[name="area_coordinador"]');
                if (inputArea) inputArea.value = u.nombre_area || u.area_coordinador || '';
            }

            abrirModal('modalEditarUsuario');
        } else {
            alert(u?.error || "No se pudo cargar la información del usuario para editar.");
        }
    } catch (e) {
        console.error("Error al preparar edición:", e);
        alert("Ocurrió un error al preparar la edición del usuario.");
    }
}

async function verUsuarioDetalles(id) {
    const modal = document.getElementById('modalVerUsuario');
    if (!modal) {
        console.warn("No se encontró el modal de ver usuario.");
        return;
    }

    const idsCampos = ['verNombre', 'verTipoDoc', 'verNumDoc', 'verCorreo', 'verCargo', 'verTipoIns', 'verContrato', 'verArea'];
    idsCampos.forEach(idEl => {
        const el = document.getElementById(idEl);
        if (el) el.textContent = 'Cargando...';
    });

    abrirModal('modalVerUsuario');

    try {
        const res = await apiRequest("obtener", "GET", { id: id });
        const u = Array.isArray(res) ? res[0] : res;

        if (!u || u.error) {
            alert(res?.error || "No se pudo cargar la información del usuario.");
            cerrarModal('modalVerUsuario');
            return;
        }

        const set = (idEl, val) => {
            const el = document.getElementById(idEl);
            if (el) el.textContent = val ?? '';
        };
        set('verNombre', u.nombre_completo);
        set('verTipoDoc', u.tipo_documento);
        set('verNumDoc', u.numero_documento);
        set('verCorreo', u.correo_electronico);
        set('verCargo', u.cargo);

        alternarCamposCargo(u.cargo, modal);

        if (String(u.cargo || '').toLowerCase() === 'instructor') {
            set('verTipoIns', u.tipo_instructor || 'N/A');
            set('verContrato', u.tipo_contrato || 'N/A');
        } else {
            set('verArea', u.nombre_area || u.area_coordinador || 'No asignada');
        }
    } catch (e) {
        console.error("Error al ver usuario:", e);
        alert("Ocurrió un error al cargar los detalles del usuario.");
        cerrarModal('modalVerUsuario');
    }
}

async function toggleEstado(id, estadoActual) {
    const nuevoEstado = parseInt(estadoActual) === 1 ? 0 : 1;
    const res = await apiRequest('cambiarEstado', 'POST', {
        id_usuario: id,
        estado: nuevoEstado
    });

    if (!res.success) {
        alert("Error al cambiar estado");
    }
    cargarUsuarios(); // Recarga para sincronizar datos y visuales
}