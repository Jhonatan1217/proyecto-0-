/* ==========================================================================
   GESTIÓN DE USUARIOS - JS (Versión Final Adaptada a la Vista)
   ========================================================================== */

const API = window.API_USUARIO;

/**
 * Petición centralizada al controlador
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
        console.error("Error en la petición:", error);
        return { success: false, error: "Error de conexión con el servidor" };
    }
}

/* =============================================
   1. CONTROL DE MODALES
   ============================================= */

function abrirModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.replace('hidden', 'flex');
    }
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.replace('flex', 'hidden');
    }
}

/**
 * Muestra/Oculta campos según si es Instructor o Coordinador
 */
function alternarCamposCargo(cargo, contenedorModal) {
    // Buscamos los grupos dentro del modal específico (Crear o Editar)
    const grupoIns = contenedorModal.querySelector('#grupoInstructor');
    const grupoCoor = contenedorModal.querySelector('#grupoCoordinador');
    
    if (cargo === 'Instructor') {
        grupoIns?.classList.remove('hidden');
        grupoCoor?.classList.add('hidden');
        // Activar requeridos para instructor
        grupoIns?.querySelectorAll('select').forEach(s => s.required = true);
        grupoCoor?.querySelectorAll('input').forEach(i => i.required = false);
    } else if (cargo === 'Coordinador') {
        grupoIns?.classList.add('hidden');
        grupoCoor?.classList.remove('hidden');
        // Activar requeridos para coordinador
        grupoIns?.querySelectorAll('select').forEach(s => s.required = false);
        grupoCoor?.querySelectorAll('input').forEach(i => i.required = true);
    }
}

/* =============================================
   2. RENDERIZADO Y FILTROS
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
    tbody.innerHTML = "";
    
    data.forEach(u => {
        tbody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b border-gray-100">
                <td class="px-6 py-4 font-medium text-gray-700">${u.numero_documento}</td>
                <td class="px-6 py-4">${u.nombre_completo}</td>
                <td class="px-6 py-4 text-gray-500">${u.correo_electronico}</td>
                <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                    <button onclick="verUsuarioDetalles(${u.id_usuario})" class="text-[#0a3a57] hover:underline font-medium">Ver</button>
                    <button onclick="prepararEdicion(${u.id_usuario})" class="text-blue-600 hover:underline font-medium">Editar</button>
                    <button onclick="toggleEstado(${u.id_usuario}, ${u.estado})" 
                            class="${u.estado == 1 ? 'text-red-600' : 'text-green-600'} hover:underline font-medium">
                        ${u.estado == 1 ? 'Inhabilitar' : 'Activar'}
                    </button>
                </td>
            </tr>`;
    });
}

/* =============================================
   3. INICIALIZACIÓN DE EVENTOS (DOM CONTENT LOADED)
   ============================================= */

document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();

    // --- Abrir Modal Nuevo Usuario ---
    document.getElementById('btnAbrirModalUsuario')?.addEventListener('click', () => {
        const modal = document.getElementById('modalNuevoUsuario');
        modal.querySelector('form').reset();
        alternarCamposCargo('Instructor', modal); // Default
        abrirModal('modalNuevoUsuario');
    });

    // --- Detectar cambio de cargo en modales ---
    document.querySelectorAll('#selectCargoModal').forEach(select => {
        select.addEventListener('change', (e) => {
            const modalContenedor = e.target.closest('.fixed');
            alternarCamposCargo(e.target.value, modalContenedor);
        });
    });

    // --- Cierre de Modales (Botones X y Cancelar) ---
    document.querySelectorAll('#btnCerrarModal, #btnCerrarModalEditar, #btnCancelar').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.fixed');
            cerrarModal(modal.id);
        });
    });

    // --- Envío Formulario Crear ---
    document.getElementById('formUsuario')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const datos = Object.fromEntries(new FormData(e.target));
        
        // Adaptación para el Controller PHP
        datos.password = datos.numero_documento; // Contraseña = Documento
        // El controller espera 'id_area' pero tú usas 'area_coordinador', 
        // si tu modelo no usa ID sino texto, envíalo tal cual.

        const res = await apiRequest("crear", "POST", datos);
        if (res.success) {
            alert("Usuario guardado. Contraseña: " + datos.password);
            cerrarModal('modalNuevoUsuario');
            cargarUsuarios();
            e.target.reset();
        } else {
            alert("Error: " + (res.error || "No se pudo guardar"));
        }
    });

    // --- Envío Formulario Editar ---
    document.getElementById('formEditarUsuario')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const datos = Object.fromEntries(new FormData(e.target));
        
        const res = await apiRequest("actualizar", "POST", datos);
        if (res.success) {
            alert("Usuario actualizado correctamente");
            cerrarModal('modalEditarUsuario');
            cargarUsuarios();
        } else {
            alert("Error: " + (res.error || "No se pudo actualizar"));
        }
    });

    // --- Filtros (Opcional, si el controlador los soporta) ---
    document.getElementById('filtroCargos')?.addEventListener('change', async (e) => {
        const cargo = e.target.value;
        const res = await apiRequest(`listar${cargo ? '&cargo=' + cargo.toUpperCase() : ''}`);
        renderTabla(res);
    });
});

/* =============================================
   4. FUNCIONES GLOBALES (ACCIONES DE TABLA)
   ============================================= */

async function prepararEdicion(id) {
    const u = await apiRequest(`listar&id=${id}`);
    if (u && !u.error) {
        const modal = document.getElementById('modalEditarUsuario');
        const form = document.getElementById('formEditarUsuario');

        // Llenar campos
        form.querySelector('[name="nombre_completo"]').value = u.nombre_completo;
        form.querySelector('[name="tipo_documento"]').value = u.tipo_documento;
        form.querySelector('[name="numero_documento"]').value = u.numero_documento;
        form.querySelector('[name="correo_electronico"]').value = u.correo_electronico;
        form.querySelector('[name="cargo"]').value = u.cargo;

        // Si no tienes el input hidden de id_usuario en el HTML, lo creamos
        let hiddenId = form.querySelector('[name="id_usuario"]');
        if(!hiddenId){
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
            gIns.classList.replace('grid', 'hidden');
            gCoor.classList.remove('hidden');
            document.getElementById('verArea').textContent = u.area_coordinador || 'No asignada';
        }
        abrirModal('modalVerUsuario');
    }
}

async function toggleEstado(id, estadoActual) {
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    const res = await apiRequest('cambiarEstado', 'POST', { id_usuario: id, estado: nuevoEstado });
    if (res.success) {
        cargarUsuarios();
    } else {
        alert("Error al cambiar estado");
    }
}