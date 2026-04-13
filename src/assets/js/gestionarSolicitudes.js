const API_URL_FALLBACK = 'src/controllers/SolicitudController.php';

function usuarioPuedeResponderSolicitudes() {
    if (Number(window.USUARIO_ES_SISTEMA || 0) === 1) return true;
    return String(window.USUARIO_CARGO || "").trim().toUpperCase() === "COORDINADOR";
}

/** Aprobar / devolver: coordinador o es_sistema, y nunca la propia solicitud si es solo coordinador */
function usuarioPuedeResponderSolicitudPendiente(data) {
    if (!data || String(data.estado || "").toUpperCase() !== "PENDIENTE") return false;
    if (!usuarioPuedeResponderSolicitudes()) return false;
    if (Number(window.USUARIO_ES_SISTEMA || 0) === 1) return true;
    const cargo = String(window.USUARIO_CARGO || "").trim().toUpperCase();
    if (cargo !== "COORDINADOR") return false;
    const uid = Number(window.USUARIO_ID || 0);
    const idSol = Number(data.id_instructor_solicitante);
    if (!idSol || idSol === uid) return false;
    return true;
}

function usuarioEsCoordinadorNoSistema() {
    if (Number(window.USUARIO_ES_SISTEMA || 0) === 1) return false;
    return String(window.USUARIO_CARGO || "").trim().toUpperCase() === "COORDINADOR";
}

function getSolicitudApiUrl() {
    return typeof window !== 'undefined' && window.API_URL ? window.API_URL : API_URL_FALLBACK;
}

/** Misma lógica que gestionPerfil.js (verPerfilAvatar) para iniciales del nombre */
function inicialesDesdeNombreCompleto(nombre) {
    const n = (nombre || "").trim();
    if (!n || n === "N/A") return "—";
    return n
        .split(/\s+/)
        .map((s) => s[0])
        .filter(Boolean)
        .slice(0, 2)
        .join("")
        .toUpperCase();
}

function escapeHtmlSolicitud(str) {
    const s = String(str ?? "");
    return s
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function solClaseBadgeEstado(estado) {
    const e = (estado || "PENDIENTE").toUpperCase();
    const base = "sol-badge-estado";
    if (e === "PENDIENTE") return `${base} sol-badge-estado--pendiente`;
    if (e === "APROBADO") return `${base} sol-badge-estado--aprobado`;
    if (e === "DEVUELTO") return `${base} sol-badge-estado--devuelto`;
    return `${base} sol-badge-estado--otro`;
}

function solClaseBadgeTipo(tipoRaw) {
    const t = (tipoRaw || "").toUpperCase();
    const base = "sol-badge-tipo";
    if (t === "DATOS" || t.includes("DATO")) return `${base} sol-badge-tipo--datos`;
    if (t === "HORARIO" || t.includes("HORARIO")) return `${base} sol-badge-tipo--horario`;
    return `${base} sol-badge-tipo--otro`;
}

function parseHorarioJson(raw) {
    if (!raw) return null;
    if (typeof raw === "object") return raw;
    try {
        return JSON.parse(String(raw));
    } catch (_) {
        return null;
    }
}

function construirCambiosHorarioHumanos(anterior, nuevo) {
    if (!anterior || !nuevo) return [];
    const cambios = [];
    if (anterior.dia !== nuevo.dia) {
        cambios.push({ etiqueta: "Dia", anterior: anterior.dia || "No especificado", nuevo: nuevo.dia || "No especificado" });
    }
    if (anterior.hora_inicio !== nuevo.hora_inicio || anterior.hora_fin !== nuevo.hora_fin) {
        cambios.push({
            etiqueta: "Horario",
            anterior: `${anterior.hora_inicio || "?"} - ${anterior.hora_fin || "?"}`,
            nuevo: `${nuevo.hora_inicio || "?"} - ${nuevo.hora_fin || "?"}`,
        });
    }
    if (String(anterior.numero_ficha ?? "") !== String(nuevo.numero_ficha ?? "")) {
        cambios.push({ etiqueta: "Ficha", anterior: anterior.numero_ficha || "No especificada", nuevo: nuevo.numero_ficha || "No especificada" });
    }
    if (String(anterior.id_instructor ?? "") !== String(nuevo.id_instructor ?? "")) {
        cambios.push({ etiqueta: "Instructor", anterior: anterior.id_instructor || "No especificado", nuevo: nuevo.id_instructor || "No especificado" });
    }
    if (String(anterior.id_competencia ?? "") !== String(nuevo.id_competencia ?? "")) {
        cambios.push({ etiqueta: "Competencia", anterior: anterior.id_competencia || "No especificada", nuevo: nuevo.id_competencia || "No especificada" });
    }
    const aDesc = String(anterior.descripcion_jornada ?? "").trim();
    const nDesc = String(nuevo.descripcion_jornada ?? "").trim();
    if (aDesc !== nDesc) {
        cambios.push({ etiqueta: "Descripcion jornada", anterior: aDesc || "Sin descripcion", nuevo: nDesc || "Sin descripcion" });
    }
    const aRaes = (anterior.raes || []).join(", ");
    const nRaes = (nuevo.raes || []).join(", ");
    if (aRaes !== nRaes) {
        cambios.push({ etiqueta: "RAEs", anterior: aRaes || "Sin RAEs", nuevo: nRaes || "Sin RAEs" });
    }
    return cambios;
}

function capitalizarDiaSemana(d) {
    const s = String(d || "").trim().toLowerCase();
    if (!s) return "";
    return s.charAt(0).toUpperCase() + s.slice(1);
}

/** Título del bloque: día y franja (sustituye "Horario ID …"). */
function tituloBloqueDesdeHorarios(hAnt, hNue, esNuevo) {
    const n = hNue || hAnt;
    if (!n || typeof n !== "object") return "Cambio de horario";
    const dia = capitalizarDiaSemana(n.dia);
    const hi = String(n.hora_inicio || "").trim();
    const hf = String(n.hora_fin || "").trim();
    const franja = hi && hf ? `${hi} – ${hf}` : hi || hf || "";
    if (esNuevo) {
        if (dia && franja) return `Nuevo · ${dia} · ${franja}`;
        return dia || franja || "Nuevo horario";
    }
    if (dia && franja) return `${dia} · ${franja}`;
    return dia || franja || "Cambio de horario";
}

const __catAreaZona = {
    areas: new Map(),
    zonas: new Map(),
    ready: false,
    loading: null,
};

function getBaseUrlSolicitudes() {
    const u = typeof window !== "undefined" && window.BASE_URL ? String(window.BASE_URL) : "";
    return u.replace(/\/+$/, "/");
}

/** Carga nombres de áreas y zonas para mostrar en lista y modal. */
function asegurarCatalogosAreaZona() {
    if (__catAreaZona.ready) return Promise.resolve();
    if (__catAreaZona.loading) return __catAreaZona.loading;
    const base = getBaseUrlSolicitudes();
    __catAreaZona.loading = (async () => {
        try {
            const [rA, rZ] = await Promise.all([
                fetch(`${base}src/controllers/UsuarioController.php?accion=areas`, { credentials: "same-origin" }),
                fetch(`${base}src/controllers/ZonaController.php?accion=listar`, { credentials: "same-origin" }),
            ]);
            const areasJson = await rA.json().catch(() => []);
            const zonasWrap = await rZ.json().catch(() => ({}));
            const arrAreas = Array.isArray(areasJson) ? areasJson : [];
            const arrZonas = Array.isArray(zonasWrap.data) ? zonasWrap.data : Array.isArray(zonasWrap) ? zonasWrap : [];
            arrAreas.forEach((a) => {
                const id = a.id_area != null ? String(a.id_area) : "";
                if (id) __catAreaZona.areas.set(id, String(a.nombre_area || "").trim() || `Área ${id}`);
            });
            arrZonas.forEach((z) => {
                const id = z.id_zona != null ? String(z.id_zona) : "";
                if (id) __catAreaZona.zonas.set(id, String(z.nombre_zona || "").trim() || `Zona ${id}`);
            });
        } catch (_) {
            /* mapas vacíos; se muestran IDs */
        }
        __catAreaZona.ready = true;
    })();
    return __catAreaZona.loading;
}

function nombreAreaPorId(id) {
    if (id == null || id === "") return "";
    const k = String(id);
    return __catAreaZona.areas.get(k) || "";
}

function nombreZonaPorId(id) {
    if (id == null || id === "") return "";
    const k = String(id);
    return __catAreaZona.zonas.get(k) || "";
}

/** Recorre detalles HORARIO_JSON y devuelve payloads parseados (valor_nuevo). */
function extraerPayloadsHorarioDesdeDetalles(detalles) {
    const out = [];
    if (!Array.isArray(detalles)) return out;
    detalles.forEach((d) => {
        const c = String(d.campo_modificado || "").toLowerCase();
        if (!c.includes("horario")) return;
        const nue = parseHorarioJson(d.valor_nuevo);
        if (nue && typeof nue === "object") out.push(nue);
    });
    return out;
}

/**
 * Resume área/zona para cabecera de solicitud de horario.
 * @returns {{ lineas: { label: string, valor: string }[], varias: boolean }}
 */
function resolverUbicacionDesdePayloads(payloads) {
    const vacio = { lineas: [], varias: false };
    if (!payloads || !payloads.length) return vacio;
    const pares = [];
    payloads.forEach((p) => {
        const ia = p.id_area != null && p.id_area !== "" ? String(p.id_area) : "";
        const iz = p.id_zona != null && p.id_zona !== "" ? String(p.id_zona) : "";
        pares.push({ ia, iz });
    });
    const uniq = new Set(pares.map((x) => `${x.ia}|${x.iz}`));
    if (uniq.size > 1) {
        return {
            varias: true,
            lineas: [
                { label: "Ámbito", valor: "Varias ubicaciones en esta solicitud (revise cada bloque)." },
            ],
        };
    }
    const { ia, iz } = pares[0] || { ia: "", iz: "" };
    const lineas = [];
    if (ia) {
        const na = nombreAreaPorId(ia);
        lineas.push({ label: "Área", valor: na ? `${na} (ID ${ia})` : `ID ${ia}` });
    }
    if (iz) {
        const nz = nombreZonaPorId(iz);
        lineas.push({ label: "Zona", valor: nz ? `${nz} (ID ${iz})` : `ID ${iz}` });
    }
    if (!lineas.length && payloads.length) {
        const mod = String(payloads[0].modalidad || "").trim().toUpperCase();
        if (mod === "VIRTUAL" || mod === "MIXTO") {
            lineas.push({ label: "Ubicación", valor: "Modalidad sin sede física (virtual/mixto)." });
        }
    }
    return { lineas, varias: false };
}

/** Subtítulo para fila de tabla (solicitud de horario). */
function lineaUbicacionTablaDesdeSolicitud(sol) {
    const tipo = String(sol.tipo_solicitud || "").toLowerCase();
    if (!tipo.includes("horario")) return "";
    const payloads = extraerPayloadsHorarioDesdeDetalles(sol.detalles || []);
    const u = resolverUbicacionDesdePayloads(payloads);
    if (!u.lineas.length) return "";
    return u.lineas.map((l) => `${l.label}: ${l.valor}`).join(" · ");
}

/** Texto legible para solicitudes de horario nuevo (sin valor anterior en BD). */
function construirNuevoHorarioHumano(h, opts) {
    opts = opts || {};
    const omitirDiaYFranja = !!opts.omitirDiaYFranja;
    const omitirUbicacionIds = !!opts.omitirUbicacionIds;
    if (!h || typeof h !== "object") return [];
    const vacio = "—";
    const rows = [];
    if (!omitirDiaYFranja) {
        const dia = String(h.dia || "").trim();
        if (dia) {
            const cap = capitalizarDiaSemana(dia);
            rows.push({ etiqueta: "Día", anterior: vacio, nuevo: cap });
        }
        const hi = String(h.hora_inicio || "").trim();
        const hf = String(h.hora_fin || "").trim();
        if (hi || hf) {
            rows.push({
                etiqueta: "Franja horaria",
                anterior: vacio,
                nuevo: `${hi || "?"} – ${hf || "?"}`,
            });
        }
    }
    const ficha = String(h.numero_ficha ?? "").trim();
    if (ficha) rows.push({ etiqueta: "Número de ficha", anterior: vacio, nuevo: ficha });
    const ins = String(h.id_instructor ?? "").trim();
    if (ins) rows.push({ etiqueta: "Instructor (ID)", anterior: vacio, nuevo: ins });
    const comp = String(h.id_competencia ?? "").trim();
    if (comp) rows.push({ etiqueta: "Competencia (ID)", anterior: vacio, nuevo: comp });
    if (!omitirUbicacionIds) {
        const zona = String(h.id_zona ?? "").trim();
        if (zona) rows.push({ etiqueta: "Zona (ID)", anterior: vacio, nuevo: zona });
        const area = String(h.id_area ?? "").trim();
        if (area) rows.push({ etiqueta: "Área (ID)", anterior: vacio, nuevo: area });
    }
    const mod = String(h.modalidad ?? "").trim();
    if (mod) rows.push({ etiqueta: "Modalidad", anterior: vacio, nuevo: mod });
    const dj = String(h.descripcion_jornada ?? "").trim();
    if (dj) rows.push({ etiqueta: "Descripción de la jornada", anterior: vacio, nuevo: dj });
    const raes = h.raes;
    if (Array.isArray(raes) && raes.length) {
        rows.push({ etiqueta: "RAEs (ID)", anterior: vacio, nuevo: raes.map((x) => String(x).trim()).filter(Boolean).join(", ") });
    }
    if (!rows.length) {
        rows.push({
            etiqueta: "Detalle",
            anterior: vacio,
            nuevo: "Nuevo horario (sin datos adicionales en la solicitud).",
        });
    }
    return rows;
}

let todasLasSolicitudes = [];
let solicitudActualId = null;
let solicitudActualTipo = null;
let solicitudActualData = null;

document.addEventListener("DOMContentLoaded", () => {
    const botones = document.querySelectorAll(".filter-btn");
    const tablaBody = document.getElementById("tablaSolicitudes");
    const buscador = document.getElementById("searchInput");
    const paginacionWrap = document.getElementById("solicitudesPagination");
    const solPrev = document.getElementById("solPrev");
    const solNext = document.getElementById("solNext");

    const ITEMS_PER_PAGE = 8;

    let estadoActivo = "Todas";
    let solicitudesFiltradas = [];
    let paginaActual = 1;

    function aplicarEstilosFiltros(estado) {
        botones.forEach((btn) => {
            btn.classList.toggle("solicitud-filter--active", btn.dataset.estado === estado);
        });
    }

    // Configurar event listeners de los modales
    document.getElementById("btnAprobar")?.addEventListener("click", () => {
        aprobarSolicitud();
    });

    document.getElementById("btnDevolver")?.addEventListener("click", () => {
        abrirModalDevolver();
    });

    document.getElementById("btnConfirmarDevolver")?.addEventListener("click", () => {
        devolverSolicitud();
    });

    // Botón de cerrar del modal principal
    document.getElementById("cerrarModalDetalle")?.addEventListener("click", () => {
        cerrarModal();
    });


    // ================================
    // CARGAR DATOS DESDE EL BACKEND
    // ================================
    async function cargarSolicitudes() {
        try {
            mostrarLoading(true);
            await asegurarCatalogosAreaZona();

            let url = getSolicitudApiUrl();
            
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
            
            const response = await fetch(url, { credentials: "same-origin" });
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
        paginaActual = 1;
        const textoBusqueda = (buscador?.value || "").toLowerCase().trim();

        solicitudesFiltradas = todasLasSolicitudes.filter((solicitud) => {
            if (!textoBusqueda) return true;

            const idStr = solicitud.id_solicitud != null ? String(solicitud.id_solicitud) : "";
            const programa =
                (solicitud.programa && String(solicitud.programa)) ||
                (solicitud.nombre_programa && String(solicitud.nombre_programa)) ||
                "";

            const ubicTxt = lineaUbicacionTablaDesdeSolicitud(solicitud);

            return (
                (solicitud.codigo_solicitud && solicitud.codigo_solicitud.toLowerCase().includes(textoBusqueda)) ||
                (idStr && idStr.includes(textoBusqueda)) ||
                (solicitud.nombre_instructor && solicitud.nombre_instructor.toLowerCase().includes(textoBusqueda)) ||
                (solicitud.tipo_solicitud && solicitud.tipo_solicitud.toLowerCase().includes(textoBusqueda)) ||
                (programa && programa.toLowerCase().includes(textoBusqueda)) ||
                (ubicTxt && ubicTxt.toLowerCase().includes(textoBusqueda))
            );
        });

        renderizarTabla();
    }

    // ================================
    // RENDERIZAR TABLA
    // ================================
    function actualizarPaginacion() {
        if (!paginacionWrap || !solPrev || !solNext) return;

        const total = solicitudesFiltradas.length;
        if (total === 0) {
            paginacionWrap.classList.add("hidden");
            return;
        }

        const totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
        if (paginaActual > totalPages) paginaActual = totalPages;
        if (paginaActual < 1) paginaActual = 1;

        paginacionWrap.classList.remove("hidden");

        const info = document.getElementById("solPageInfo");
        if (info) {
            const itemLabel = total === 1 ? "ítem" : "ítems";
            info.textContent = `Página ${paginaActual} de ${totalPages} · ${total} ${itemLabel}`;
        }

        solPrev.disabled = paginaActual <= 1;
        solNext.disabled = paginaActual >= totalPages;
    }

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
            actualizarPaginacion();
            return;
        }

        const totalPages = Math.max(1, Math.ceil(solicitudesFiltradas.length / ITEMS_PER_PAGE));
        if (paginaActual > totalPages) paginaActual = totalPages;

        const inicio = (paginaActual - 1) * ITEMS_PER_PAGE;
        const paginaDatos = solicitudesFiltradas.slice(inicio, inicio + ITEMS_PER_PAGE);

        let html = "";

        paginaDatos.forEach((sol) => {
            const fecha = sol.fecha_solicitud ? new Date(sol.fecha_solicitud).toLocaleDateString('es-CO') : 'N/A';

            const estadoTexto = (sol.estado || "PENDIENTE").toUpperCase();
            const tipoTexto = sol.tipo_solicitud || "N/A";
            const clsEstado = solClaseBadgeEstado(estadoTexto);
            const clsTipo = solClaseBadgeTipo(tipoTexto);

            html += `
                <tr data-estado="${estadoTexto}" class="hover:bg-gray-50">
                    <td class="px-4 py-3">${escapeHtmlSolicitud(sol.codigo_solicitud || sol.id_solicitud)}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">${escapeHtmlSolicitud(sol.nombre_instructor || "N/A")}</div>
                        <div class="text-xs text-gray-500">${escapeHtmlSolicitud(sol.correo_instructor || "")}</div>
                    </td>
                    <td class="px-4 py-3 text-left align-middle">
                        <span class="${clsEstado}">${escapeHtmlSolicitud(estadoTexto)}</span>
                    </td>
                    <td class="px-4 py-3 text-left align-middle">
                        <span class="${clsTipo}">${escapeHtmlSolicitud(tipoTexto)}</span>
                    </td>
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
        actualizarPaginacion();
    }

    // ================================
    // FUNCIONES DE INTERFAZ
    // ================================
    function mostrarLoading(mostrar) {
        if (mostrar && paginacionWrap) {
            paginacionWrap.classList.add("hidden");
        }
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
        if (paginacionWrap) paginacionWrap.classList.add("hidden");
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
    // EVENT LISTENERS
    // ================================
    botones.forEach((btn) => {
        btn.addEventListener("click", async () => {
            estadoActivo = btn.dataset.estado;
            aplicarEstilosFiltros(estadoActivo);
            await cargarSolicitudes();
        });
    });

    buscador?.addEventListener("input", () => {
        aplicarFiltros();
    });

    document.getElementById("btnRefrescarSolicitudes")?.addEventListener("click", () => {
        cargarSolicitudes();
    });

    solPrev?.addEventListener("click", () => {
        if (paginaActual > 1) {
            paginaActual -= 1;
            renderizarTabla();
        }
    });

    solNext?.addEventListener("click", () => {
        const totalPages = Math.max(1, Math.ceil(solicitudesFiltradas.length / ITEMS_PER_PAGE));
        if (paginaActual < totalPages) {
            paginaActual += 1;
            renderizarTabla();
        }
    });

    aplicarEstilosFiltros("Todas");
    cargarSolicitudes();

    window.recargarModuloSolicitudes = function () {
        cargarSolicitudes();
    };
});

// ================================
// FUNCIÓN PARA VER DETALLES
// ================================
async function verSolicitud(id) {
    const solicitud = todasLasSolicitudes.find(s => s.id_solicitud == id);
    if (!solicitud) { console.error("Solicitud no encontrada"); return; }

    await asegurarCatalogosAreaZona();

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
    let horarioAnterior = "", horarioNuevo = "";
    let cambiosHorario = [];
    let bloquesHorario = [];

    const etiquetaCampoDatos = {
        nombre_completo: "Nombre",
        tipo_documento: "Tipo de documento",
        numero_documento: "Documento",
        correo_electronico: "Correo electrónico",
        tipo_instructor: "Tipo instructor",
        tipo_contrato: "Tipo contrato",
    };
    const ordenCamposDatos = [
        "nombre_completo",
        "tipo_documento",
        "numero_documento",
        "correo_electronico",
        "tipo_instructor",
        "tipo_contrato",
    ];
    const cambiosDatos = [];

    // Detalles que pueden venir en el listado
    let detalles = solicitud.detalles || [];

    // Si NO vienen, pedirlos al backend por separado
    if (detalles.length === 0) {
        try {
            const resp = await fetch(`${getSolicitudApiUrl()}?accion=obtener&id_solicitud=${encodeURIComponent(id)}`, {
                credentials: "same-origin",
            });
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

    // Procesar detalles: datos personales por nombre de campo exacto (evita filas falsas);
    // horario con criterio amplio por compatibilidad.
    detalles.forEach((detalle) => {
        const campo = (detalle.campo_modificado || "").trim();
        const cLow = campo.toLowerCase();

        const campoDatosKey = Object.keys(etiquetaCampoDatos).find(
            (k) => k.toLowerCase() === cLow
        );

        if (campoDatosKey) {
            const va = String(detalle.valor_anterior ?? "").trim();
            const vn = String(detalle.valor_nuevo ?? "").trim();
            if (va !== vn) {
                cambiosDatos.push({
                    campo: campoDatosKey,
                    etiqueta: etiquetaCampoDatos[campoDatosKey],
                    anterior: va || "No especificado",
                    nuevo: vn || "No especificado",
                });
            }
            if (campoDatosKey === "nombre_completo") {
                nombreAnterior = detalle.valor_anterior ?? nombreAnterior;
                nombreNuevo = detalle.valor_nuevo ?? nombreNuevo;
            }
            return;
        }

        if (cLow.includes("horario")) {
            const hAnt = parseHorarioJson(detalle.valor_anterior);
            const hNue = parseHorarioJson(detalle.valor_nuevo);
            const esHorarioNuevo = hNue && (!hAnt || hNue.es_nuevo === true);
            if (esHorarioNuevo) {
                let c = construirNuevoHorarioHumano(hNue, { omitirDiaYFranja: true, omitirUbicacionIds: true });
                if (!c.length) {
                    c = [{ etiqueta: "Detalle", anterior: "—", nuevo: "Nuevo horario" }];
                }
                cambiosHorario = cambiosHorario.concat(c);
                bloquesHorario.push({
                    titulo: tituloBloqueDesdeHorarios(null, hNue, true),
                    cambios: c,
                    soloValoresNuevos: true,
                });
                horarioAnterior = "—";
                horarioNuevo = `${hNue.dia || ""} ${hNue.hora_inicio || ""} – ${hNue.hora_fin || ""}`.trim();
            } else if (hAnt && hNue) {
                const c = construirCambiosHorarioHumanos(hAnt, hNue);
                if (c.length) {
                    cambiosHorario = cambiosHorario.concat(c);
                    bloquesHorario.push({
                        titulo: tituloBloqueDesdeHorarios(hAnt, hNue, false),
                        cambios: c,
                    });
                }
                horarioAnterior = `${hAnt.dia || ""} ${hAnt.hora_inicio || ""} - ${hAnt.hora_fin || ""}`.trim();
                horarioNuevo = `${hNue.dia || ""} ${hNue.hora_inicio || ""} - ${hNue.hora_fin || ""}`.trim();
            } else {
                horarioAnterior = detalle.valor_anterior || horarioAnterior;
                horarioNuevo = detalle.valor_nuevo || horarioNuevo;
            }
        } else if (cLow.includes("nombre") || cLow.includes("nombres")) {
            const va = String(detalle.valor_anterior ?? "").trim();
            const vn = String(detalle.valor_nuevo ?? "").trim();
            nombreAnterior = detalle.valor_anterior || nombreAnterior;
            nombreNuevo = detalle.valor_nuevo || nombreNuevo;
            if (va !== vn) {
                cambiosDatos.push({
                    campo: "nombre_completo",
                    etiqueta: "Nombre",
                    anterior: va || "No especificado",
                    nuevo: vn || "No especificado",
                });
            }
        }
    });

    cambiosDatos.sort(
        (a, b) => ordenCamposDatos.indexOf(a.campo) - ordenCamposDatos.indexOf(b.campo)
    );
    const cambiosDatosUnicos = [];
    const camposVistos = new Set();
    cambiosDatos.forEach((row) => {
        if (camposVistos.has(row.campo)) return;
        camposVistos.add(row.campo);
        cambiosDatosUnicos.push(row);
    });

    // Fallback: leer campos planos de la solicitud si aún están vacíos
    if (solicitudActualTipo === "horario" && !horarioAnterior && !horarioNuevo) {
        horarioAnterior = solicitud.horario_anterior || solicitud.valor_anterior || "";
        horarioNuevo    = solicitud.horario_nuevo    || solicitud.valor_nuevo    || "";
    }

    const rawPrograma = (solicitud.programa || solicitud.nombre_programa || "").trim();
    const programaDisplay =
        rawPrograma && rawPrograma.toUpperCase() !== "N/A"
            ? rawPrograma
            : "Usuario no vinculado a un programa";

    const payloadsUbicacion = extraerPayloadsHorarioDesdeDetalles(detalles);
    const ubicacionHorario = resolverUbicacionDesdePayloads(payloadsUbicacion);

    const dataModal = {
        id:               solicitud.id_solicitud,
        id_instructor_solicitante: solicitud.id_instructor_solicitante,
        codigo:           solicitud.codigo_solicitud || `S-${solicitud.id_solicitud}`,
        solicitante:      solicitud.nombre_instructor || "N/A",
        programa:         programaDisplay,
        fecha:            solicitud.fecha_solicitud || "",
        estado:           (solicitud.estado || "PENDIENTE").toUpperCase(),
        tipo:             solicitudActualTipo,
        tipoRaw:          solicitud.tipo_solicitud || "",   // valor original del backend
        motivoDevolucion: solicitud.observacion_respuesta || "",
        nombreAnterior, nombreNuevo,
        horarioAnterior, horarioNuevo,
        cambiosHorario,
        bloquesHorario,
        cambiosDatos: cambiosDatosUnicos,
        ubicacionHorario,
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
    if (document.body) {
        document.body.dataset.prevOverflow = document.body.style.overflow || "";
        document.body.style.overflow = "hidden";
    }

    const avatarEl = document.getElementById("modalSolicitudAvatar");
    if (avatarEl) {
        avatarEl.textContent = inicialesDesdeNombreCompleto(data.solicitante);
    }

    document.getElementById("modalCodigo").textContent = data.codigo || "";
    document.getElementById("modalSolicitante").textContent = data.solicitante || "";
    document.getElementById("modalPrograma").textContent = data.programa || "Usuario no vinculado a un programa";

    const fechaRaw = data.fecha || "";
    const fechaFormateada = fechaRaw.includes(" ")
        ? fechaRaw.split(" ").join(" - ")
        : fechaRaw;
    document.getElementById("modalFecha").textContent = fechaFormateada;

    // Badge de tipo — usa el valor normalizado o el raw del backend directamente
    const tipoBadge = document.getElementById("modalTipoBadge");
    if (tipoBadge) {
        const tipoTexto = data.tipo === "horario"  ? "Horario"
                        : data.tipo === "datos"    ? "Datos personales"
                        : data.tipoRaw             ? data.tipoRaw   // valor original del backend
                        : data.tipo                ? data.tipo
                        : "";
        tipoBadge.textContent = tipoTexto;
        tipoBadge.className =
            "inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-0.5 text-[11px] font-medium text-gray-600";
        tipoBadge.style.display = tipoTexto ? "inline-flex" : "none";
    }

    // Guardar ID de la solicitud actual para usarlo en los botones
    modal.dataset.solicitudId = data.id;

    // Estado con colores
    const estado = document.getElementById("modalEstado");
    estado.textContent = data.estado || "";

    const estadoBase =
        "inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold";

    if (data.estado === "PENDIENTE") {
        estado.className = estadoBase;
        estado.style.cssText = "background-color: #fef3c7 !important; color: #92400e !important;";
    } else if (data.estado === "APROBADO") {
        estado.className = estadoBase;
        estado.style.cssText = "background-color: #C5E7B5 !important; color: #166534 !important;";
    } else if (data.estado === "DEVUELTO") {
        estado.className = estadoBase;
        estado.style.cssText = "background-color: #ffe4e6 !important; color: #9b1c1c !important;";
    } else {
        estado.className = estadoBase;
        estado.style.cssText = "background-color: #f3f4f6 !important; color: #1f2937 !important;";
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

    // Mostrar/ocultar botones de acción (instructores nunca; coordinador no en solicitud propia)
    const botonesDiv = document.getElementById("botonesAccion");
    const avisoPropiaCoord = document.getElementById("avisoSolicitudPropiaCoordinador");
    const pendiente = String(data.estado || "").toUpperCase() === "PENDIENTE";
    const puedeActuar = pendiente && usuarioPuedeResponderSolicitudPendiente(data);
    const avisoCoordPropio =
        pendiente &&
        usuarioEsCoordinadorNoSistema() &&
        Number(data.id_instructor_solicitante) === Number(window.USUARIO_ID || 0);

    if (puedeActuar) {
        botonesDiv.classList.remove("hidden");
    } else {
        botonesDiv.classList.add("hidden");
    }
    if (avisoPropiaCoord) {
        if (avisoCoordPropio) avisoPropiaCoord.classList.remove("hidden");
        else avisoPropiaCoord.classList.add("hidden");
    }

    const wrapUb = document.getElementById("modalHorarioUbicacionWrap");
    const lineasUb = document.getElementById("modalHorarioUbicacionLineas");
    if (wrapUb && lineasUb) {
        const u = data.ubicacionHorario;
        if (data.tipo === "horario" && u && Array.isArray(u.lineas) && u.lineas.length) {
            wrapUb.classList.remove("hidden");
            lineasUb.innerHTML = u.lineas
                .map(
                    (l) =>
                        `<div class="leading-snug"><span class="text-violet-800/90 font-semibold">${escapeHtmlSolicitud(l.label)}:</span> ${escapeHtmlSolicitud(l.valor)}</div>`
                )
                .join("");
        } else {
            wrapUb.classList.add("hidden");
            lineasUb.innerHTML = "";
        }
    }

    // Contenido dinámico
    const contenedor = document.getElementById("modalContenido");

    if (data.tipo === "datos") {
        const filas =
            data.cambiosDatos && data.cambiosDatos.length > 0
                ? data.cambiosDatos
                      .map(
                          (row) => `
                    <div style="font-size:13px;">
                        <p style="font-weight:600;color:#6b7280;margin:0 0 6px 0;">${escapeHtmlSolicitud(row.etiqueta)}</p>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="background:#fff;border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;color:#374151;word-break:break-word;">${escapeHtmlSolicitud(row.anterior)}</div>
                            <div style="display:flex;align-items:center;gap:6px;padding-left:6px;color:#9ca3af;font-size:15px;">↓</div>
                            <div style="background:#fff;border:1.5px solid #10b981;border-radius:8px;padding:7px 14px;font-weight:600;color:#065f46;word-break:break-word;">${escapeHtmlSolicitud(row.nuevo)}</div>
                        </div>
                    </div>`
                      )
                      .join("")
                : `<p style="font-size:13px;color:#6b7280;">No hay cambios detallados en esta solicitud.</p>`;

        contenedor.innerHTML = `
            <div style="border:1px solid #d1d5db;border-radius:12px;background:#f9fafb;padding:16px 20px;">
                <div style="display:flex;align-items:center;gap:7px;margin-bottom:16px;font-size:13px;font-weight:700;color:#39A900;">
                    <i data-lucide="user" style="width:15px;height:15px;flex-shrink:0;"></i>
                    Cambio de datos personales
                </div>
                <div style="display:flex;flex-direction:column;gap:18px;">${filas}</div>
            </div>
        `;
        lucide.createIcons();

    } else {
        // Default: bloque de horario (cubre "horario" y cualquier otro tipo)
        const filasHorario =
            data.bloquesHorario && data.bloquesHorario.length > 0
                ? data.bloquesHorario
                      .map((bloque) => {
                          const soloNuevo = !!bloque.soloValoresNuevos;
                          const filasCambio = (bloque.cambios || [])
                              .map((row) => {
                                  if (soloNuevo) {
                                      return `
                                <div style="font-size:13px;">
                                    <p style="font-weight:600;color:#6b7280;margin:0 0 6px 0;">${escapeHtmlSolicitud(row.etiqueta)}</p>
                                    <div style="background:#fff;border:1.5px solid #10b981;border-radius:8px;padding:7px 14px;font-weight:600;color:#065f46;word-break:break-word;white-space:normal;">${escapeHtmlSolicitud(row.nuevo)}</div>
                                </div>`;
                                  }
                                  return `
                                <div style="font-size:13px;">
                                    <p style="font-weight:600;color:#6b7280;margin:0 0 6px 0;">${escapeHtmlSolicitud(row.etiqueta)}</p>
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <div style="background:#fff;border:1px solid #d1d5db;border-radius:8px;padding:7px 14px;color:#374151;word-break:break-word;white-space:normal;">${escapeHtmlSolicitud(row.anterior)}</div>
                                        <div style="display:flex;align-items:center;gap:6px;padding-left:6px;color:#9ca3af;font-size:15px;">↓</div>
                                        <div style="background:#fff;border:1.5px solid #10b981;border-radius:8px;padding:7px 14px;font-weight:600;color:#065f46;word-break:break-word;white-space:normal;">${escapeHtmlSolicitud(row.nuevo)}</div>
                                    </div>
                                </div>`;
                              })
                              .join("");
                          return `
                    <div style="border:1px solid #d1d5db;border-radius:10px;background:#fff;padding:12px;">
                        <p style="font-size:12px;font-weight:700;color:#374151;margin:0 0 10px 0;">${escapeHtmlSolicitud(bloque.titulo)}</p>
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            ${filasCambio}
                        </div>
                    </div>`;
                      })
                      .join("")
                : `
                <div class="flex flex-wrap items-center gap-2.5 text-sm">
                    <div class="rounded-md border border-gray-300 bg-white px-3.5 py-1.5 text-gray-700 break-words">
                        ${escapeHtmlSolicitud(data.horarioAnterior || "No especificado")}
                    </div>
                    <span class="text-base text-gray-400">→</span>
                    <div class="rounded-md border border-emerald-300 bg-white px-3.5 py-1.5 font-semibold text-green-700 break-words">
                        ${escapeHtmlSolicitud(data.horarioNuevo || "No especificado")}
                    </div>
                </div>`;
        contenedor.innerHTML = `
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-green-600">
                    <i data-lucide="clock" class="h-4 w-4 shrink-0"></i>
                    Cambio de horario
                </div>
                <div style="display:flex;flex-direction:column;gap:16px;overflow-x:hidden;">${filasHorario}</div>
            </div>
        `;
        lucide.createIcons();
    }
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
        const response = await fetch(`${getSolicitudApiUrl()}?accion=responder`, {
            method: 'POST',
            credentials: 'same-origin',
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
                title: 'Solicitud aprobada correctamente',
                showConfirmButton: false,
                timer: 2000
            });
            cerrarModal();
            window.recargarModuloSolicitudes?.();
            window.refrescarSesionUsuarioYHeader?.();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al aprobar',
                text: resultado.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión'
        });
    }
}

async function devolverSolicitud() {
    if (!solicitudActualId) return;

    const motivo = document.getElementById("motivoDevolucionInput").value.trim();
    
    if (!motivo) {
        Swal.fire({
            icon: 'warning',
            title: 'Debes ingresar un motivo de devolución'
        });
        return;
    }

    try {
        const id_coordinador = window.USUARIO_ID || 1;
        const response = await fetch(`${getSolicitudApiUrl()}?accion=responder`, {
            method: 'POST',
            credentials: 'same-origin',
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
                title: 'Solicitud devuelta correctamente',
                showConfirmButton: false,
                timer: 2000
            });
            cerrarModalDevolver();
            cerrarModal();
            window.recargarModuloSolicitudes?.();
            window.refrescarSesionUsuarioYHeader?.();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al devolver',
                text: resultado.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión'
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
    if (document.body) {
        document.body.style.overflow = document.body.dataset.prevOverflow || "";
        delete document.body.dataset.prevOverflow;
    }

    solicitudActualId = null;
    solicitudActualTipo = null;
    solicitudActualData = null;
}