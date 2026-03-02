/* ==========================================================================
   GESTIÓN DE USUARIOS - JS (Versión Ultra-Estable)
   ========================================================================== */

const API = window.API_USUARIO;

/**
 * Petición centralizada
 */
async function apiRequest(accion, method = "GET", body = null) {
    const url = `${API}?accion=${accion}`;
    const config = {
        method,
        headers: { "Content-Type": "application/json" }
    };
    if (body) config.body = JSON.stringify(body);

    try {
        const res = await fetch(url, config);
        return await res.json();
    } catch (error) {
        console.error("Error:", error);
        return { success: false, error: "Error de conexión" };
    }
}

/* =============================================
   1. CONTROL DE MODALES (MEJORADO)
   ============================================= */
function abrirModal(id) {
    // Cerramos cualquier modal que pudiera estar abierto por error
    document.querySelectorAll('.fixed.inset-0').forEach(m => {
        m.classList.add('hidden');
        m.classList.remove('flex');
        m.style.zIndex = "50";
    });

    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.style.zIndex = "60"; // Forzamos que esté arriba
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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
                    <div class="flex items-center justify-end gap-2">
                        <button class="btn-ver p-2 text-gray-400 hover:text-[#0a3a57] rounded-full hover:bg-gray-100" data-id="${u.id_usuario}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button class="btn-editar p-2 text-gray-400 hover:text-blue-600 rounded-full hover:bg-blue-50" data-id="${u.id_usuario}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
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

    // DELEGACIÓN DE EVENTOS EN EL BODY PARA MÁXIMA COMPATIBILIDAD
    document.body.addEventListener("click", (e) => {
        const btnVer = e.target.closest(".btn-ver");
        const btnEditar = e.target.closest(".btn-editar");
        const btnEstado = e.target.closest(".btn-estado");

        if (btnVer) {
            e.preventDefault();
            verUsuarioDetalles(btnVer.dataset.id);
        } else if (btnEditar) {
            e.preventDefault();
            prepararEdicion(btnEditar.dataset.id);
        } else if (btnEstado) {
            // No prevenimos default para dejar que el checkbox cambie visualmente
            toggleEstado(btnEstado.dataset.id, btnEstado.dataset.estado);
        }
    });

    // Abrir Modal Nuevo
    document.getElementById('btnAbrirModalUsuario')?.addEventListener('click', (e) => {
        e.preventDefault();
        const modal = document.getElementById('modalNuevoUsuario');
        modal.querySelector('form').reset();
        alternarCamposCargo('Instructor', modal);
        abrirModal('modalNuevoUsuario');
    });

    // Cierre de modales (delegado para botones X y Cancelar)
    document.addEventListener('click', (e) => {
        if (e.target.closest('#btnCerrarModal, #btnCerrarModalEditar, #btnCancelarNuevo, #btnCancelarEditar, #btnCerrarVerUsuario')) {
            const modal = e.target.closest('.fixed.inset-0');
            if (modal) cerrarModal(modal.id);
        }
    });

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
    const u = await apiRequest(`listar&id=${id}`);
    if (u && !u.error) {
        const form = document.getElementById('formEditarUsuario');
        const modal = document.getElementById('modalEditarUsuario');

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
            form.querySelector('[name="modalidad"]').value = u.tipo_instructor || 'Técnico';
            form.querySelector('[name="tipo_contrato"]').value = u.tipo_contrato || 'Contratista';
        } else {
            const inputArea = form.querySelector('[name="area_coordinador"]');
            if(inputArea) inputArea.value = u.area_coordinador || '';
        }

        abrirModal('modalEditarUsuario');
    }
}

async function verUsuarioDetalles(id) {
    const u = await apiRequest(`listar&id=${id}`);
    if (u && !u.error) {
        document.getElementById('verNombre').textContent = u.nombre_completo;
        document.getElementById('verTipoDoc').textContent = u.tipo_documento;
        document.getElementById('verNumDoc').textContent = u.numero_documento;
        document.getElementById('verCorreo').textContent = u.correo_electronico;
        document.getElementById('verCargo').textContent = u.cargo;

        const gIns = document.getElementById('verGrupoInstructor');
        const gCoor = document.getElementById('verGrupoCoordinador');

        if (u.cargo === 'Instructor') {
            gIns.classList.replace('hidden', 'grid');
            gCoor.classList.add('hidden');
            document.getElementById('verTipoIns').textContent = u.tipo_instructor || 'N/A';
            document.getElementById('verContrato').textContent = u.tipo_contrato || 'N/A';
        } else {
            gIns.classList.add('hidden');
            gCoor.classList.remove('hidden');
            document.getElementById('verArea').textContent = u.area_coordinador || 'No asignada';
        }
        abrirModal('modalVerUsuario');
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