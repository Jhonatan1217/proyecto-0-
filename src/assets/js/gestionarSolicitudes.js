const API_URL = 'src/controllers/SolicitudController.php';

let todasLasSolicitudes = [];
let solicitudActualId = null;
let solicitudActualTipo = null;
let solicitudActualData = null;

document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const botones = document.querySelectorAll(".filter-btn");
    const tablaBody = document.getElementById("tablaSolicitudes");
    const buscador = document.getElementById("searchInput");
    const totalElement = document.getElementById("total");
    const mostrandoElement = document.getElementById("mostrando");
    
    // Variables de estado
    let estadoActivo = "Todas";
    let solicitudesFiltradas = [];

    // Configurar event listeners de los modales
    document.getElementById("btnAprobar")?.addEventListener("click", () => {
        abrirModalConfirmar();
    });

    document.getElementById("btnDevolver")?.addEventListener("click", () => {
        abrirModalDevolver();
    });

    document.getElementById("btnConfirmarAprobar")?.addEventListener("click", () => {
        aprobarSolicitud();
    });

    document.getElementById("btnConfirmarDevolver")?.addEventListener("click", () => {
        devolverSolicitud();
    });

    // Botón de cerrar del modal principal
    document.getElementById("cerrarModalDetalle")?.addEventListener("click", () => {
        const modal = document.getElementById("modalDetalle");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        solicitudActualId   = null;
        solicitudActualTipo = null;
        solicitudActualData = null;
    });


    // ================================
    // CARGAR DATOS DESDE EL BACKEND
    // ================================
    async function cargarSolicitudes() {
        try {
            mostrarLoading(true);
            
            let url = API_URL;
            
            // Determinar qué endpoint llamar según el estado activo
            switch(estadoActivo) {
                case "Pendiente":
                    url += "?accion=listar_pendientes";
                    break;
                case "Aprobada":
                    url += "?accion=listar_aprobadas";
                    break;
                case "Devuelto":
                    url += "?accion=listar_devueltas";
                    break;
                default: // "Todas"
                    url += "?accion=listar";
            }
            
            console.log("Cargando datos desde:", url);
            
            const response = await fetch(url);
            const resultado = await response.json();
            
            console.log("Datos recibidos:", resultado);
            
            if (resultado.status === "success") {
                todasLasSolicitudes = resultado.data || [];
                aplicarFiltros();
            } else {
                console.error("Error al cargar datos:", resultado.message);
                mostrarError(resultado.message || "Error al cargar las solicitudes");
            }
            
        } catch (error) {
            console.error("Error de conexión:", error);
            mostrarError("Error de conexión con el servidor");
        } finally {
            mostrarLoading(false);
        }
    }

    // ================================
    // APLICAR FILTROS (BÚSQUEDA)
    // ================================
    function aplicarFiltros() {
        const textoBusqueda = buscador.value.toLowerCase();
        
        solicitudesFiltradas = todasLasSolicitudes.filter(solicitud => {
            if (!textoBusqueda) return true;
            
            const coincideEnCampos = 
                (solicitud.codigo_solicitud && solicitud.codigo_solicitud.toLowerCase().includes(textoBusqueda)) ||
                (solicitud.nombre_instructor && solicitud.nombre_instructor.toLowerCase().includes(textoBusqueda)) ||
                (solicitud.tipo_solicitud && solicitud.tipo_solicitud.toLowerCase().includes(textoBusqueda));
            
            return coincideEnCampos;
        });
        
        renderizarTabla();
        actualizarContadores();
    }

    // ================================
    // RENDERIZAR TABLA
    // ================================
    function renderizarTabla() {
        if (!tablaBody) return;
        
        if (solicitudesFiltradas.length === 0) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        No hay solicitudes para mostrar
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = "";
        
        solicitudesFiltradas.forEach(sol => {
            const fecha = sol.fecha_solicitud ? new Date(sol.fecha_solicitud).toLocaleDateString('es-CO') : 'N/A';
            
            let badgeColor = "";
            let estadoTexto = sol.estado || "PENDIENTE";
            
            switch(estadoTexto) {
                case "PENDIENTE":
                    badgeColor = "bg-amber-100 text-amber-800";
                    break;
                case "APROBADO":
                    badgeColor = "bg-emerald-100 text-emerald-800";
                    break;
                case "DEVUELTO":
                    badgeColor = "bg-rose-100 text-rose-800";
                    break;
                default:
                    badgeColor = "bg-gray-100 text-gray-800";
            }
            
            html += `
                <tr data-estado="${estadoTexto}" class="hover:bg-gray-50">
                    <td class="px-4 py-3">${sol.codigo_solicitud || sol.id_solicitud}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">${sol.nombre_instructor || 'N/A'}</div>
                        <div class="text-xs text-gray-500">${sol.correo_instructor || ''}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${badgeColor}">
                            ${estadoTexto}
                        </span>
                    </td>
                    <td class="px-4 py-3">${sol.tipo_solicitud || 'N/A'}</td>
                    <td class="px-4 py-3">${fecha}</td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="verSolicitud(${sol.id_solicitud})" 
                                class="text-blue-600 hover:text-blue-800 transition-colors">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tablaBody.innerHTML = html;
    }

    // ================================
    // ACTUALIZAR CONTADORES
    // ================================
    function actualizarContadores() {
        if (totalElement) {
            totalElement.textContent = todasLasSolicitudes.length;
        }
        if (mostrandoElement) {
            mostrandoElement.textContent = solicitudesFiltradas.length;
        }
    }

    // ================================
    // FUNCIONES DE INTERFAZ
    // ================================
    function mostrarLoading(mostrar) {
        if (mostrar) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center">
                        <div class="flex justify-center items-center">
                            <svg class="animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-2 text-gray-600">Cargando solicitudes...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function mostrarError(mensaje) {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-red-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ${mensaje}
                </td>
            </tr>
        `;
    }

    // ================================
    // CAMBIAR ESTADO ACTIVO
    // ================================
    function quitarActivo() {
        botones.forEach(b => {
            b.classList.remove('boton-activo');
        });
    }

    function activarBoton(estado) {
        quitarActivo();
        const botonActivo = document.querySelector(`[data-estado="${estado}"]`);
        if (botonActivo) {
            botonActivo.classList.add('boton-activo');
        }
    }

    // ================================
    // EVENT LISTENERS
    // ================================
    botones.forEach(btn => {
        btn.addEventListener("click", async () => {
            estadoActivo = btn.dataset.estado;
            activarBoton(estadoActivo);
            await cargarSolicitudes();
        });
    });

    buscador.addEventListener("input", () => {
        aplicarFiltros();
    });

    // Cargar datos iniciales
    activarBoton("Todas");
    cargarSolicitudes();
});

// ================================
// FUNCIÓN PARA VER DETALLES
// ================================
async function verSolicitud(id) {
    const solicitud = todasLasSolicitudes.find(s => s.id_solicitud == id);
    if (!solicitud) { console.error("Solicitud no encontrada"); return; }

    solicitudActualId = id;

    // Normalizar tipo: acepta "Horario", "HORARIO", "Cambio de horario", etc.
    const tipoRaw = (solicitud.tipo_solicitud || "").toLowerCase();
    if (tipoRaw.includes("horario")) {
        solicitudActualTipo = "horario";
    } else if (tipoRaw.includes("dato") || tipoRaw.includes("personal")) {
        solicitudActualTipo = "datos";
    } else {
        solicitudActualTipo = tipoRaw;
    }

    let nombreAnterior = "", nombreNuevo = "";
    let docAnterior    = "", docNuevo    = "";
    let horarioAnterior = "", horarioNuevo = "";

    // Detalles que pueden venir en el listado
    let detalles = solicitud.detalles || [];

    // Si NO vienen, pedirlos al backend por separado
    if (detalles.length === 0) {
        try {
            const resp = await fetch(`${window.API_URL}?accion=detalle&id=${id}`);
            const result = await resp.json();
            if (result.status === "success" && result.data) {
                detalles = result.data.detalles || (Array.isArray(result.data) ? result.data : []);
                // Campos planos que algunos backends devuelven directamente
                if (result.data.horario_anterior !== undefined) horarioAnterior = result.data.horario_anterior || "";
                if (result.data.horario_nuevo    !== undefined) horarioNuevo    = result.data.horario_nuevo    || "";
                if (result.data.nombre_anterior  !== undefined) nombreAnterior  = result.data.nombre_anterior  || "";
                if (result.data.nombre_nuevo     !== undefined) nombreNuevo     = result.data.nombre_nuevo     || "";
            }
        } catch (e) {
            console.warn("No se pudieron cargar detalles del backend:", e);
        }
    }

    // Procesar array detalles (campo_modificado / valor_anterior / valor_nuevo)
    detalles.forEach(detalle => {
        const campo = (detalle.campo_modificado || "").toLowerCase();
        if (campo.includes("horario")) {
            horarioAnterior = detalle.valor_anterior || horarioAnterior;
            horarioNuevo    = detalle.valor_nuevo    || horarioNuevo;
        } else if (campo.includes("nombre") || campo.includes("nombres")) {
            nombreAnterior = detalle.valor_anterior || nombreAnterior;
            nombreNuevo    = detalle.valor_nuevo    || nombreNuevo;
        } else if (campo.includes("documento") || campo.includes("doc")) {
            docAnterior = detalle.valor_anterior || docAnterior;
            docNuevo    = detalle.valor_nuevo    || docNuevo;
        }
    });

    // Fallback: leer campos planos de la solicitud si aún están vacíos
    if (solicitudActualTipo === "horario" && !horarioAnterior && !horarioNuevo) {
        horarioAnterior = solicitud.horario_anterior || solicitud.valor_anterior || "";
        horarioNuevo    = solicitud.horario_nuevo    || solicitud.valor_nuevo    || "";
    }

    const dataModal = {
        id:               solicitud.id_solicitud,
        codigo:           solicitud.codigo_solicitud || `S-${solicitud.id_solicitud}`,
        solicitante:      solicitud.nombre_instructor || "N/A",
        programa:         solicitud.programa || solicitud.nombre_programa || "N/A",
        fecha:            solicitud.fecha_solicitud || "",
        estado:           (solicitud.estado || "PENDIENTE").toUpperCase(),
        tipo:             solicitudActualTipo,
        tipoRaw:          solicitud.tipo_solicitud || "",   // valor original del backend
        motivoDevolucion: solicitud.observacion_respuesta || "",
        nombreAnterior, nombreNuevo,
        docAnterior,    docNuevo,
        horarioAnterior, horarioNuevo,
    };

    console.log("📦 DATOS MAPEADOS:", dataModal);
    solicitudActualData = dataModal;
    abrirModal(dataModal);
}

// ================================
// ABRIR MODAL PRINCIPAL
// ================================
function abrirModal(data) {
    console.log("Abriendo modal con:", data);

    const modal = document.getElementById("modalDetalle");
    modal.classList.remove("hidden");
    modal.classList.add("flex");

    // Datos básicos
    document.getElementById("modalCodigo").textContent      = data.codigo      || "";
    document.getElementById("modalSolicitante").textContent = data.solicitante  || "";
    document.getElementById("modalPrograma").textContent    = data.programa     || "";
    document.getElementById("modalFecha").textContent       = data.fecha        || "";

    // Badge de tipo — usa el valor normalizado o el raw del backend directamente
    const tipoBadge = document.getElementById("modalTipoBadge");
    if (tipoBadge) {
        const tipoTexto = data.tipo === "horario"  ? "Horario"
                        : data.tipo === "datos"    ? "Datos personales"
                        : data.tipoRaw             ? data.tipoRaw   // valor original del backend
                        : data.tipo                ? data.tipo
                        : "";
        tipoBadge.textContent = tipoTexto;
        tipoBadge.style.display = tipoTexto ? "inline-block" : "none";
    }

    // Guardar ID de la solicitud actual para usarlo en los botones
    modal.dataset.solicitudId = data.id;

    // Estado con colores
    const estado = document.getElementById("modalEstado");
    estado.textContent = data.estado || "";

    if (data.estado === "PENDIENTE") {
        estado.className = "px-3 py-1 rounded-full text-sm font-medium";
        estado.style.cssText = 'background-color: #fef3c7 !important; color: #92400e !important;';
    } else if (data.estado === "APROBADO") {
        estado.className = "px-3 py-1 rounded-full text-sm font-medium";
        estado.style.cssText = 'background-color: #C5E7B5 !important; color: #166534 !important;';
    } else if (data.estado === "DEVUELTO") {
        estado.className = "px-3 py-1 rounded-full text-sm font-medium";
        estado.style.cssText = 'background-color: #ffe4e6 !important; color: #9b1c1c !important;';
    } else {
        estado.className = "px-3 py-1 rounded-full text-sm font-medium";
        estado.style.cssText = 'background-color: #f3f4f6 !important; color: #1f2937 !important;';
    }

    // Mostrar/ocultar motivo de devolución según el estado
    const motivoDiv = document.getElementById("motivoDevolucion");
    const motivoTexto = document.getElementById("textoMotivoDevolucion");
    
    if (data.estado === "DEVUELTO" && data.motivoDevolucion) {
        motivoDiv.classList.remove("hidden");
        motivoTexto.textContent = data.motivoDevolucion;
    } else {
        motivoDiv.classList.add("hidden");
    }

    // Mostrar/ocultar botones de acción según el estado
    const botonesDiv = document.getElementById("botonesAccion");
    if (data.estado === "PENDIENTE") {
        botonesDiv.classList.remove("hidden");
    } else {
        botonesDiv.classList.add("hidden");
    }

    // Contenido dinámico
    const contenedor = document.getElementById("modalContenido");

    if (data.tipo === "datos") {
        contenedor.innerHTML = `
            <div class="bg-gray-50 rounded-xl p-4" style="border:1px solid #d1d5db;">
                <div class="flex items-center gap-2 mb-4 font-semibold" style="color:#39A900;">
                    <i data-lucide="user" style="width:16px;height:16px;"></i>
                    Cambio de datos personales
                </div>
                <div style="display:flex;flex-direction:column;gap:16px;font-size:14px;">
                    <div>
                        <p style="color:#6b7280;margin-bottom:6px;">Nombre</p>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <div style="padding:6px 14px;background:#f9fafb;border:1px solid #d1d5db;border-radius:6px;color:#374151;">
                                ${data.nombreAnterior || "<span style='color:#9ca3af'>No especificado</span>"}
                            </div>
                            <span style="color:#9ca3af;">→</span>
                            <div style="padding:6px 14px;background:#f0fdf4;border:1px solid #10b981;border-radius:6px;color:#065f46;font-weight:600;">
                                ${data.nombreNuevo || "<span style='color:#9ca3af'>No especificado</span>"}
                            </div>
                        </div>
                    </div>
                    <div>
                        <p style="color:#6b7280;margin-bottom:6px;">Documento</p>
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <div style="padding:6px 14px;background:#f9fafb;border:1px solid #d1d5db;border-radius:6px;color:#374151;">
                                ${data.docAnterior || "<span style='color:#9ca3af'>No especificado</span>"}
                            </div>
                            <span style="color:#9ca3af;">→</span>
                            <div style="padding:6px 14px;background:#f0fdf4;border:1px solid #10b981;border-radius:6px;color:#065f46;font-weight:600;">
                                ${data.docNuevo || "<span style='color:#9ca3af'>No especificado</span>"}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        lucide.createIcons();

    } else {
        // Default: bloque de horario (cubre "horario" y cualquier otro tipo)
        contenedor.innerHTML = `
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:14px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;color:#16a34a;font-weight:600;font-size:14px;">
                    <i data-lucide="clock" style="width:16px;height:16px;"></i>
                    Cambio de horario
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <div style="padding:5px 14px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;color:#4b5563;">
                        ${data.horarioAnterior || "No especificado"}
                    </div>
                    <span style="color:#9ca3af;font-size:16px;">→</span>
                    <div style="padding:5px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;font-size:13px;color:#15803d;font-weight:600;">
                        ${data.horarioNuevo || "No especificado"}
                    </div>
                </div>
            </div>
        `;
        lucide.createIcons();
    }
}

// ================================
// FUNCIONES PARA MODALES DE CONFIRMACIÓN
// ================================
function abrirModalConfirmar() {

    if (!solicitudActualData) return;

    const data = solicitudActualData;

    // Datos básicos
    document.getElementById("modalCodigoConfirmacion").textContent = data.codigo;
    document.getElementById("modalSolicitanteConfirmacion").textContent = data.solicitante;

    const contenedor = document.getElementById("contenidoConfirmacion");

    // Render dinámico según tipo
    if (data.tipo === "datos") {

        contenedor.innerHTML = `
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">

                <div class="flex items-center gap-2 mb-4 text-gray-700 font-medium">
                    <i data-lucide="user" class="w-5 h-5 text-green-600"></i>
                    Cambio de datos
                </div>

                <div class="flex items-center justify-between gap-3 flex-wrap">

                    <div class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 min-w-[150px]">
                        ${data.nombreAnterior || "No especificado"}
                    </div>

                    <span class="text-gray-400 font-semibold">→</span>

                    <div class="bg-green-100 border border-green-400 rounded-lg px-4 py-2 text-sm text-green-800 font-medium min-w-[150px]">
                        ${data.nombreNuevo || "No especificado"}
                    </div>

                </div>
            </div>
        `;

    } else if (data.tipo === "horario") {

        contenedor.innerHTML = `
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">

                <div class="flex items-center gap-2 mb-4 text-gray-700 font-medium">
                    <i data-lucide="clock" class="w-5 h-5 text-green-600"></i>
                    Cambio de horario
                </div>

                <div class="flex items-center justify-between gap-3 flex-wrap">

                    <div class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 min-w-[150px]">
                        ${data.horarioAnterior || "No especificado"}
                    </div>

                    <span class="text-gray-400 font-semibold">→</span>

                    <div class="bg-green-100 border border-green-400 rounded-lg px-4 py-2 text-sm text-green-800 font-medium min-w-[150px]">
                        ${data.horarioNuevo || "No especificado"}
                    </div>

                </div>
            </div>
        `;
    }

    document.getElementById("modalConfirmarAprobacion").classList.remove("hidden");
    document.getElementById("modalConfirmarAprobacion").classList.add("flex");

    lucide.createIcons();
}

function cerrarModalConfirmar() {
    document.getElementById("modalConfirmarAprobacion").classList.add("hidden");
    document.getElementById("modalConfirmarAprobacion").classList.remove("flex");
}

function abrirModalDevolver() {
    document.getElementById("modalDevolverMotivo").classList.remove("hidden");
    document.getElementById("modalDevolverMotivo").classList.add("flex");
    document.getElementById("motivoDevolucionInput").value = "";
}

function cerrarModalDevolver() {
    document.getElementById("modalDevolverMotivo").classList.add("hidden");
    document.getElementById("modalDevolverMotivo").classList.remove("flex");
}

// ================================
// FUNCIONES PARA APROBAR/DEVOLVER
// ================================
async function aprobarSolicitud() {
    if (!solicitudActualId) return;

    try {
        const id_coordinador = window.USUARIO_ID || 1;
        const response = await fetch(`${API_URL}?accion=responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id_solicitud: solicitudActualId,
                estado: 'APROBADO',
                observacion_respuesta: '',
                id_coordinador_aprobador: id_coordinador
            })
        });

        const resultado = await response.json();
        
        if (resultado.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '✅ Solicitud aprobada correctamente',
                timer: 2000
            });
            cerrarModalConfirmar();
            cerrarModal();
            // Recargar la lista sin refrescar la página completa
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            Swal.fire({
                icon: 'error',
                title: '❌ Error al aprobar',
                text: resultado.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '❌ Error de conexión'
        });
    }
}

async function devolverSolicitud() {
    if (!solicitudActualId) return;

    const motivo = document.getElementById("motivoDevolucionInput").value.trim();
    
    if (!motivo) {
        Swal.fire({
            icon: 'warning',
            title: '⚠️ Debes ingresar un motivo de devolución'
        });
        return;
    }

    try {
        const id_coordinador = window.USUARIO_ID || 1;
        const response = await fetch(`${API_URL}?accion=responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id_solicitud: solicitudActualId,
                estado: 'DEVUELTO',
                observacion_respuesta: motivo,
                id_coordinador_aprobador: id_coordinador
            })
        });

        const resultado = await response.json();
        
        if (resultado.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '✅ Solicitud devuelta correctamente',
                timer: 2000
            });
            cerrarModalDevolver();
            cerrarModal();
            // Recargar la lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            Swal.fire({
                icon: 'error',
                title: '❌ Error al devolver',
                text: resultado.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '❌ Error de conexión'
        });
    }
}

// ================================
// CERRAR MODAL PRINCIPAL
// ================================
function cerrarModal() {
    const modal = document.getElementById("modalDetalle");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    
    // Limpiar datos
    solicitudActualId = null;
    solicitudActualTipo = null;
}