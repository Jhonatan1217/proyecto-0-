document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const botones = document.querySelectorAll(".filter-btn");
    const tablaBody = document.getElementById("tablaSolicitudes");
    const buscador = document.getElementById("searchInput");
    const totalElement = document.getElementById("total");
    const mostrandoElement = document.getElementById("mostrando");
    
    // Variables de estado
    let estadoActivo = "Todas";
    let todasLasSolicitudes = [];
    let solicitudesFiltradas = [];

    // ================================
    // CARGAR DATOS DESDE EL BACKEND
    // ================================
    async function cargarSolicitudes() {
        try {
            mostrarLoading(true);
            
            let url = window.API_URL;
            
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
            // Si no hay texto de búsqueda, mostrar todas
            if (!textoBusqueda) return true;
            
            // Buscar en los campos relevantes
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
            // Formatear fecha
            const fecha = sol.fecha_solicitud ? new Date(sol.fecha_solicitud).toLocaleDateString('es-CO') : 'N/A';
            
            // Determinar color del badge según estado
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
            
            // Recargar datos según el nuevo estado
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
// FUNCIÓN GLOBAL PARA VER DETALLES
// ================================
function verSolicitud(id) {
    console.log("Ver solicitud:", id);
    // Aquí puedes redirigir a una página de detalle o abrir un modal
    window.location.href = `detalleSolicitud.php?id=${id}`;
}