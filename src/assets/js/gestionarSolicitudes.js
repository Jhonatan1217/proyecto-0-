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
function verSolicitud(id) {
    const solicitud = todasLasSolicitudes.find(s => s.id_solicitud == id);

    if (!solicitud) {
        console.error("Solicitud no encontrada");
        return;
    }

    // Guardar datos actuales
    solicitudActualId = id;
    solicitudActualTipo = solicitud.tipo_solicitud ? solicitud.tipo_solicitud.toLowerCase() : "";

    // Inicializar variables para los detalles
    let nombreAnterior = "", nombreNuevo = "";
    let docAnterior = "", docNuevo = "";
    let horarioAnterior = "", horarioNuevo = "";

    // Procesar los detalles si existen
    if (solicitud.detalles && solicitud.detalles.length > 0) {
        solicitud.detalles.forEach(detalle => {
            const campo = detalle.campo_modificado ? detalle.campo_modificado.toLowerCase() : "";
            
            if (campo.includes("nombre") || campo.includes("nombres")) {
                nombreAnterior = detalle.valor_anterior || "";
                nombreNuevo = detalle.valor_nuevo || "";
            }
            else if (campo.includes("documento") || campo.includes("doc")) {
                docAnterior = detalle.valor_anterior || "";
                docNuevo = detalle.valor_nuevo || "";
            }
            else if (campo.includes("horario")) {
                horarioAnterior = detalle.valor_anterior || "";
                horarioNuevo = detalle.valor_nuevo || "";
            }
        });
    }

    // Mapear datos al formato que usa el modal
    const dataModal = {
        id: solicitud.id_solicitud,
        codigo: solicitud.codigo_solicitud || solicitud.id_solicitud,
        solicitante: solicitud.nombre_instructor || "N/A",
        programa: solicitud.programa || "N/A",
        fecha: solicitud.fecha_solicitud,
        estado: solicitud.estado,
        tipo: solicitudActualTipo,
        motivoDevolucion: solicitud.observacion_respuesta || "",
        nombreAnterior: nombreAnterior,
        nombreNuevo: nombreNuevo,
        docAnterior: docAnterior,
        docNuevo: docNuevo,
        horarioAnterior: horarioAnterior,
        horarioNuevo: horarioNuevo
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
    document.getElementById("modalCodigo").textContent = data.codigo || "";
    document.getElementById("modalSolicitante").textContent = data.solicitante || "";
    document.getElementById("modalPrograma").textContent = data.programa || "";
    document.getElementById("modalFecha").textContent = data.fecha || "";

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
            <div class="bg-gray-50 rounded-xl p-6 mt-6" style="border: 1px solid #d1d5db !important;">
                <!-- Borde forzado con estilo inline -->

                <div class="flex items-center gap-2 mb-4 text-[#39A900] font-semibold">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    Cambio de datos personales
                </div>

                <div class="space-y-6 text-sm">
                    <!-- Nombre -->
                    <div>
                        <p class="text-gray-500 mb-2">Nombre, apellido, etc</p>
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="bg-white rounded px-4 py-2 text-gray-700 min-w-[180px]" style="border: 1px solid #d1d5db !important;">
                                ${data.nombreAnterior || "<span class='text-gray-400'>No especificado</span>"}
                            </div>

                            <span class="text-gray-400 font-semibold">→</span>

                            <div class="bg-green-50 rounded px-4 py-2 text-green-800 min-w-[180px]" style="border: 1px solid #10b981 !important;">
                                ${data.nombreNuevo || "<span class='text-gray-400'>No especificado</span>"}
                            </div>
                        </div>
                    </div>

                    <!-- Documento -->
                    <div>
                        <p class="text-gray-500 mb-2">Documento</p>
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="bg-white rounded px-4 py-2 text-gray-700 min-w-[180px]" style="border: 1px solid #d1d5db !important;">
                                ${data.docAnterior || "<span class='text-gray-400'>No especificado</span>"}
                            </div>

                            <span class="text-gray-400 font-semibold">→</span>

                            <div class="bg-green-50 rounded px-4 py-2 text-green-800 min-w-[180px]" style="border: 1px solid #10b981 !important;">
                                ${data.docNuevo || "<span class='text-gray-400'>No especificado</span>"}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        lucide.createIcons();

    } else if (data.tipo === "horario") {
        contenedor.innerHTML = `
            <div class="bg-gray-50 rounded-xl p-6 mt-6 border border-gray-200">

                <div class="flex items-center gap-2 mb-4 text-[#39A900] font-semibold">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    Cambio de horario
                </div>

                <div class="flex items-center justify-between gap-3 flex-wrap">

                    <div class="bg-white rounded-lg px-4 py-2 text-gray-700 min-w-[180px] border border-gray-300">
                        ${data.horarioAnterior || "<span class='text-gray-400'>No especificado</span>"}
                    </div>

                    <span class="text-gray-400 font-semibold">→</span>

                    <div class="bg-green-100 rounded-lg px-4 py-2 text-green-800 min-w-[180px] border border-green-300 font-medium">
                        ${data.horarioNuevo || "<span class='text-gray-400'>No especificado</span>"}
                    </div>

                </div>
            </div>
        `;
        lucide.createIcons();

    } else {
        contenedor.innerHTML = `<p class="text-gray-500 p-4">No hay información adicional para este tipo de solicitud</p>`;
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
        const response = await fetch(`${API_URL}?accion=responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id_solicitud: solicitudActualId,
                estado: 'APROBADO',
                observacion_respuesta: '',
                id_coordinador_aprobador: 1 // Reemplazar con el ID del coordinador actual
            })
        });

        const resultado = await response.json();
        
        if (resultado.status === 'success') {
            alert('✅ Solicitud aprobada correctamente');
            cerrarModalConfirmar();
            cerrarModal();
            // Recargar la lista
            location.reload(); // Recarga simple, o puedes llamar a cargarSolicitudes()
        } else {
            alert('❌ Error al aprobar: ' + resultado.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
    }
}

async function devolverSolicitud() {
    if (!solicitudActualId) return;

    const motivo = document.getElementById("motivoDevolucionInput").value.trim();
    
    if (!motivo) {
        alert('⚠️ Debes ingresar un motivo de devolución');
        return;
    }

    try {
        const response = await fetch(`${API_URL}?accion=responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id_solicitud: solicitudActualId,
                estado: 'DEVUELTO',
                observacion_respuesta: motivo,
                id_coordinador_aprobador: 1 // Reemplazar con el ID del coordinador actual
            })
        });

        const resultado = await response.json();
        
        if (resultado.status === 'success') {
            alert('✅ Solicitud devuelta correctamente');
            cerrarModalDevolver();
            cerrarModal();
            // Recargar la lista
            location.reload(); // Recarga simple, o puedes llamar a cargarSolicitudes()
        } else {
            alert('❌ Error al devolver: ' + resultado.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error de conexión');
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