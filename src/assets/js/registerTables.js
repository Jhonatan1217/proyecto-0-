// ===============================
// REGISTER TABLES - (ÁREAS/ZONAS + EDICIÓN + TOASTS + PDF + ELIMINAR)
// ===============================

const urlParams = new URLSearchParams(window.location.search);
let id_zona = urlParams.get("id_zona");
const filtrosIniciales = {
  modalidad: String(urlParams.get("modalidad") || "").trim().toLowerCase(),
  id_area: String(urlParams.get("id_area") || "").trim(),
  id_zona: String(urlParams.get("id_zona") || "").trim(),
  numero_ficha: String(urlParams.get("numero_ficha") || "").trim(),
};
const API_BASE = ((window && window.BASE_URL) || "").replace(/\/+$/, "/");
const IS_AUTHENTICATED = Boolean(window && window.IS_AUTHENTICATED);

// =======================
// CONFIG TOAST (SweetAlert2)
// =======================
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  background: "#fff",
  color: "#000",
});

let horariosCache = [];
let gestionHorasCache = { instructores: [], grupos: [] };
let gestionHorasTabActual = "instructores";

function registroActivo(estado) {
  if (estado === undefined || estado === null || estado === "") return true;
  const valor = String(estado).trim().toLowerCase();
  return valor === "1" || valor === "true" || valor === "activo";
}

function timeToMinutes(t) {
  if (!t) return null;
  const [h, m] = t.split(":").map(Number);
  return Number.isFinite(h) ? h * 60 + (Number.isFinite(m) ? m : 0) : null;
}

function formatHourNumber(value) {
  const n = Number(value || 0);
  if (!Number.isFinite(n)) return "0";
  return Number.isInteger(n) ? String(n) : n.toFixed(1).replace(/\.0$/, "");
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function getEstadoExcedente(value) {
  const n = Number(value || 0);
  if (n < 0) return "danger";
  if (n <= 4) return "neutral";
  return "ok";
}

function renderExcedentePill(value) {
  const estado = getEstadoExcedente(value);
  const texto = Number(value) < 0 ? `${formatHourNumber(value)} h` : `${formatHourNumber(value)} h libres`;
  return `<span class="gestion-horas-pill gestion-horas-pill--${estado}">${texto}</span>`;
}

function dispararToastsExcedente(warnings) {
  const instructores = Array.isArray(warnings?.instructores) ? warnings.instructores : [];
  const grupos = Array.isArray(warnings?.grupos) ? warnings.grupos : [];

  instructores.forEach((inst) => {
    Toast.fire({
      icon: "warning",
      title: `Atención: El instructor ha superado su límite de carga horaria (${inst.nombre_instructor})`
    });
  });

  grupos.forEach((grupo) => {
    Toast.fire({
      icon: "info",
      title: `Aviso: El grupo ${grupo.id_grupo} ha excedido las 30 horas reglamentarias`
    });
  });
}

async function cargarResumenGestionHoras() {
  const res = await fetch(`${API_BASE}src/controllers/TrimestralizacionController.php?accion=resumenHoras`);
  const data = await res.json();
  if (!res.ok || data.status !== "success") {
    throw new Error(data.mensaje || data.error || "No fue posible cargar el resumen de horas");
  }
  gestionHorasCache = {
    instructores: Array.isArray(data?.data?.instructores) ? data.data.instructores : [],
    grupos: Array.isArray(data?.data?.grupos) ? data.data.grupos : []
  };
}

function getGestionHorasFiltrados() {
  const search = String(document.getElementById("gestionHorasSearch")?.value || "").trim().toLowerCase();
  const extra = String(document.getElementById("gestionHorasExtraFiltro")?.value || "").trim().toLowerCase();

  if (gestionHorasTabActual === "instructores") {
    return gestionHorasCache.instructores.filter((item) => {
      const bySearch = !search
        || String(item.nombre_instructor || "").toLowerCase().includes(search)
        || String(item.id_instructor || "").toLowerCase().includes(search);
      const byExtra = !extra || String(item.tipo_contrato || "").toLowerCase() === extra;
      return bySearch && byExtra;
    });
  }

  return gestionHorasCache.grupos.filter((item) => {
    const bySearch = !search
      || String(item.id_grupo || "").toLowerCase().includes(search)
      || String(item.id_ficha || "").toLowerCase().includes(search);
    const byExtra = !extra || String(item.nivel_grupo || "").toLowerCase() === extra;
    return bySearch && byExtra;
  });
}

function renderGestionHorasResumen(rows) {
  const resumen = document.getElementById("gestionHorasResumen");
  if (!resumen) return;

  if (gestionHorasTabActual === "instructores") {
    resumen.innerHTML = `
      <p class="gh-resumen-title">Instructores</p>
      <p class="gh-resumen-sub">(Instructores Planta 32h, Instructores Contratista 40h)</p>`;
  } else {
    resumen.innerHTML = `
      <p class="gh-resumen-title">Grupos</p>
      <p class="gh-resumen-sub">(Cada grupo tiene un máximo de 30 horas semanales)</p>`;
  }
}

function renderGestionHoras() {
  const filtros = document.getElementById("gestionHorasFiltros");
  const head = document.getElementById("gestionHorasHead");
  const body = document.getElementById("gestionHorasBody");
  const tabInst = document.getElementById("tabGestionHorasInstructores");
  const tabGrupos = document.getElementById("tabGestionHorasGrupos");
  if (!filtros || !head || !body) return;

  if (tabInst) tabInst.classList.toggle("is-active", gestionHorasTabActual === "instructores");
  if (tabGrupos) tabGrupos.classList.toggle("is-active", gestionHorasTabActual === "grupos");

  if (gestionHorasTabActual === "instructores") {
    filtros.innerHTML = `
      <input id="gestionHorasSearch" type="text" placeholder="Buscar instructores" class="gh-filtros-input" />
      <select id="gestionHorasExtraFiltro" class="gh-filtros-select">
        <option value="">Todos los tipos de contrato</option>
        <option value="planta">Planta</option>
        <option value="contratista">Contratista</option>
      </select>`;

    head.innerHTML = `
      <tr>
        <th>Instructor</th>
        <th>Tipo contrato</th>
        <th class="center">Horas actuales</th>
        <th class="center">Horas máxima</th>
        <th class="center">Excedente</th>
        <th class="center">Acciones</th>
      </tr>`;
  } else {
    filtros.innerHTML = `
      <input id="gestionHorasSearch" type="text" placeholder="Buscar grupos" class="gh-filtros-input" />
      <select id="gestionHorasExtraFiltro" class="gh-filtros-select">
        <option value="">Todos los niveles</option>
        <option value="técnico">Técnico</option>
        <option value="tecnólogo">Tecnólogo</option>
        <option value="sin nivel">Sin nivel</option>
      </select>`;

    head.innerHTML = `
      <tr>
        <th>ID Grupo</th>
        <th>Nivel de grupo</th>
        <th class="center">Horas actuales</th>
        <th class="center">Horas máxima</th>
        <th class="center">Excedente</th>
      </tr>`;
  }

  document.getElementById("gestionHorasSearch")?.addEventListener("input", renderGestionHorasTabla);
  document.getElementById("gestionHorasExtraFiltro")?.addEventListener("change", renderGestionHorasTabla);
  renderGestionHorasTabla();
}

function renderGestionHorasTabla() {
  const body = document.getElementById("gestionHorasBody");
  if (!body) return;

  const rows = getGestionHorasFiltrados();
  const colspan = gestionHorasTabActual === "instructores" ? 6 : 5;
  renderGestionHorasResumen(rows);

  if (!rows.length) {
    body.innerHTML = `<tr><td colspan="${colspan}" style="text-align:center;padding:24px;color:#6b7280;">Sin datos disponibles</td></tr>`;
    return;
  }

  if (gestionHorasTabActual === "instructores") {
    body.innerHTML = rows.map((item) => {
      const exc = Number(item.excedente ?? 0);
      const excHTML = exc < 0
        ? `<span class="gh-excedente-neg">${formatHourNumber(exc)}</span>`
        : `--`;
      return `
        <tr>
          <td>${escapeHtml(item.nombre_instructor || "Sin nombre")}</td>
          <td>${escapeHtml(item.tipo_contrato || "Contratista")}</td>
          <td class="center">${formatHourNumber(item.horas_actuales)}</td>
          <td class="center">${formatHourNumber(item.horas_maximas)}</td>
          <td class="center">${excHTML}</td>
          <td class="center">
            <button type="button" class="gh-action-btn" onclick="window.location.href='${API_BASE}index.php?page=src/views/gestionInstructores'" title="Gestionar instructor">
              <svg viewBox="0 0 24 24" fill="none" width="14" height="14"><path d="M4 20H8L18.5 9.5C19.33 8.67 19.33 7.33 18.5 6.5C17.67 5.67 16.33 5.67 15.5 6.5L5 17V20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.5 8.5L16.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </td>
        </tr>`;
    }).join("");
    return;
  }

  body.innerHTML = rows.map((item) => {
    const exc = Number(item.excedente ?? 0);
    const excHTML = exc < 0
      ? `<span class="gh-excedente-neg">${formatHourNumber(exc)}</span>`
      : `--`;
    return `
      <tr>
        <td>${escapeHtml(item.id_grupo || "—")}</td>
        <td>${escapeHtml(item.nivel_grupo || "Sin nivel")}</td>
        <td class="center">${formatHourNumber(item.horas_actuales)}</td>
        <td class="center">${formatHourNumber(item.horas_maximas)}</td>
        <td class="center">${excHTML}</td>
      </tr>`;
  }).join("");
}

async function abrirModalGestionHoras() {
  const modal = document.getElementById("modalGestionHoras");
  if (!modal) return;
  try {
    await cargarResumenGestionHoras();
    gestionHorasTabActual = "instructores";
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden";
    renderGestionHoras();
  } catch (e) {
    Toast.fire({ icon: "error", title: e.message || "No se pudo abrir la gestión de horas" });
  }
}

function cerrarModalGestionHoras() {
  const modal = document.getElementById("modalGestionHoras");
  if (!modal) return;
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  document.body.style.overflow = "";
}

function hasOverlap({ dia, inicio, fin, excludeId }) {
  const start = timeToMinutes(inicio);
  const end = timeToMinutes(fin);
  if (start === null || end === null) return false;

  return horariosCache.some((r) => {
    if (!r || String(r.id_horario) === String(excludeId)) return false;
    if (String(r.dia || "").toUpperCase() !== String(dia || "").toUpperCase())
      return false;

    const rStart = timeToMinutes(r.hora_inicio);
    const rEnd = timeToMinutes(r.hora_fin);
    if (rStart === null || rEnd === null) return false;

    return start < rEnd && end > rStart;
  });
}



// =======================
// Mostrar/Ocultar tabla y botones
// =======================
function toggleTabla(mostrar = true) {
  const tabla = document.querySelector("#tabla-horarios");
  const botones = document.querySelector("#botones-principales");
  const emptyState = document.querySelector("#empty-state");
  if (tabla) tabla.style.display = mostrar ? "" : "none";
  if (botones) botones.style.display = mostrar ? "flex" : "none";
  if (emptyState) emptyState.style.display = mostrar ? "none" : "block";
}

// =======================
// CARGAR ÁREAS Y ZONAS
// =======================
async function cargarAreasYZonas() {
  const selectArea = document.getElementById("selectArea");
  const selectZona = document.getElementById("selectZona");

  if (!selectArea || !selectZona) return;
  toggleTabla(false);

  // De entrada, dejamos las zonas solo con placeholder
  selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
  selectZona.disabled = true;

  try {
    // Cargar ÁREAS
    const resAreas = await fetch(`${API_BASE}src/controllers/AreaController.php?accion=listar`);
    const dataAreas = await resAreas.json();

    if (dataAreas.status === "success" && Array.isArray(dataAreas.data)) {
      selectArea.innerHTML = `<option value="" hidden selected>SELECCIONE EL ÁREA</option>`;
      let contadorActivas = 0;

      dataAreas.data.forEach((a) => {
        // 🔥 SOLO ÁREAS ACTIVAS EN ESTE SELECT
        if (!registroActivo(a.estado)) return;
        contadorActivas++;
        const opt = document.createElement("option");
        opt.value = a.id_area;
        opt.textContent = a.nombre_area;
        selectArea.appendChild(opt);
      });

      // Si no hay áreas activas → mensaje y opciones deshabilitadas SOLO al desplegar
      if (contadorActivas === 0) {
        selectArea.innerHTML = `
          <option value="" hidden selected>SELECCIONE EL ÁREA</option>
          <option value="" disabled>Sin datos disponibles</option>
        `;

        selectZona.innerHTML = `
          <option value="" hidden selected>SELECCIONE LA ZONA</option>
          <option value="" disabled>Sin datos disponibles</option>
        `;

        // NO deshabilitamos los selects para que puedan desplegarse
        Toast.fire({ icon: "info", title: "No hay áreas disponibles" });
        return;
      }
    } else {
      Toast.fire({ icon: "warning", title: "No se encontraron áreas" });
    }

    async function cargarZonasPorArea(id_area, opts = {}) {
      const preselectZona = String(opts.preselectZona || "").trim();
      const silent = Boolean(opts.silent);

      selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
      toggleTabla(false);

      if (!id_area) {
        selectZona.disabled = true;
        return false;
      }

      selectZona.disabled = false;

      try {
        let zonasArea = [];

        const resZonasArea = await fetch(
          `${API_BASE}src/controllers/ZonaController.php?accion=listarPorArea&id_area=${id_area}`
        );

        if (resZonasArea.ok) {
          const dataZonasArea = await resZonasArea.json();
          if (dataZonasArea.status === "success" && Array.isArray(dataZonasArea.data)) {
            zonasArea = dataZonasArea.data;
          }
        }

        if (!zonasArea.length) {
          const resZonasAll = await fetch(`${API_BASE}src/controllers/ZonaController.php?accion=listar`);
          if (resZonasAll.ok) {
            const dataZonasAll = await resZonasAll.json();
            const arrayZonas = Array.isArray(dataZonasAll?.data) ? dataZonasAll.data : [];
            zonasArea = arrayZonas.filter(
              (z) => String(z.id_area ?? "").trim() === String(id_area).trim()
            );
          }
        }

        // SOLO ZONAS ACTIVAS EN ESTE ÁREA
        const zonasActivas = zonasArea.filter((z) => registroActivo(z.estado));

        if (!zonasActivas.length) {
          selectZona.innerHTML = `
            <option value="" hidden selected>SELECCIONE LA ZONA</option>
            <option value="" disabled>Sin datos disponibles</option>
          `;
          if (!silent) Toast.fire({
            icon: "info",
            title: "No hay zonas activas en esta área",
          });
          return false;
        }

        zonasActivas.forEach((z) => {
          const opt = document.createElement("option");
          opt.value = z.id_zona;
          opt.textContent = `Zona ${z.id_zona}`;
          selectZona.appendChild(opt);
        });

        if (preselectZona) {
          const existe = Array.from(selectZona.options).some((opt) => String(opt.value) === preselectZona);
          if (existe) {
            selectZona.value = preselectZona;
            id_zona = preselectZona;
          }
        }

        if (!silent) Toast.fire({
          icon: "success",
          title: "Zonas activas cargadas correctamente",
        });
        return true;
      } catch (err) {
        console.error("Error al cargar zonas:", err);
        if (!silent) Toast.fire({ icon: "error", title: "Error al cargar zonas" });
        return false;
      }
    }

    // Cambiar área → cargar zonas
    selectArea.addEventListener("change", async (e) => {
      const id_area = e.target.value;
      await cargarZonasPorArea(id_area);
    });

    selectZona.addEventListener("change", (e) => {
      id_zona = e.target.value;
      const id_area = selectArea.value;
      if (!id_zona || !id_area) {
        toggleTabla(false);
        return;
      }
      const h1 = document.querySelector("#cabecera-trimestralizacion h1");
      if (h1)
        h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}`;
      toggleTabla(true);
      cargarTrimestralizacion();
      Toast.fire({ icon: "info", title: `Zona ${id_zona} seleccionada` });
    });

    // Aplicar filtros iniciales (si vienen desde la creación)
    const modalidadInicial = filtrosIniciales.modalidad === "mixta" ? "mixto" : filtrosIniciales.modalidad;
    if (modalidadInicial === "presencial" && filtrosIniciales.id_area) {
      const existeArea = Array.from(selectArea.options).some((opt) => String(opt.value) === filtrosIniciales.id_area);
      if (existeArea) {
        selectArea.value = filtrosIniciales.id_area;
        await cargarZonasPorArea(filtrosIniciales.id_area, {
          preselectZona: filtrosIniciales.id_zona,
          silent: true,
        });

        if (filtrosIniciales.id_zona && selectZona.value === filtrosIniciales.id_zona) {
          const h1 = document.querySelector("#cabecera-trimestralizacion h1");
          if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${filtrosIniciales.id_zona}`;
          toggleTabla(true);
          cargarTrimestralizacion();
        }
      }
    }
  } catch (err) {
    console.error("Error en cargarAreasYZonas:", err);
    Toast.fire({ icon: "error", title: "Error al conectar con el servidor" });
  }
}


// =======================
// Configurar filtros
// ======================

function configurarFiltros(){
  const selectModalidad = document.getElementById("selectModalidad") || document.getElementById("selectModalidadFiltro");
  const selectArea = document.getElementById("contenedorAreaFiltro") || document.getElementById("selectArea")?.parentElement;
  const selectZona = document.getElementById("contenedorZonaFiltro") || document.getElementById("selectZona")?.parentElement;
  const contenedorGrupo = document.getElementById("contenedorGrupoFiltro");
  const inputGrupo = document.getElementById("inputGrupoTexto");

  if(!selectModalidad) return;

  const normalizarModalidad = (valor) => {
    const v = String(valor || "").trim().toLowerCase();
    if (v === "mixta") return "mixto";
    return v;
  };

  if(inputGrupo){
    inputGrupo.addEventListener("input", () => {
      const modalidadActual = normalizarModalidad(selectModalidad.value);
      if (modalidadActual !== "virtual" && modalidadActual !== "mixto") return;

      const valor = inputGrupo.value.trim();
      if(!valor){
        toggleTabla(false);
        return;
      }
      cargarTrimestralizacionPorGrupo(valor);
    });
  }
  
  selectModalidad.addEventListener("change", () => {
    const modalidad = normalizarModalidad(selectModalidad.value);
    if(modalidad === "presencial"){
      if(selectArea) selectArea.classList.remove("hidden");
      if(selectZona) selectZona.classList.remove("hidden");
      if(contenedorGrupo) contenedorGrupo.classList.add("hidden");

      if (id_zona && document.getElementById("selectArea")?.value) {
        cargarTrimestralizacion();
      } else {
        toggleTabla(false);
      }
    } else if (modalidad === "virtual" || modalidad === "mixto"){
        if(selectArea) selectArea.classList.add("hidden");
        if(selectZona) selectZona.classList.add("hidden");
        if(contenedorGrupo) contenedorGrupo.classList.remove("hidden");

        toggleTabla(false);

        const valor = inputGrupo?.value.trim();
        if (valor) {
          cargarTrimestralizacionPorGrupo(valor);
        }
    } else {
      toggleTabla(false);
    }
  });

  const modalidadInicial = filtrosIniciales.modalidad === "mixta" ? "mixto" : filtrosIniciales.modalidad;
  if (modalidadInicial) {
    selectModalidad.value = modalidadInicial;
    selectModalidad.dispatchEvent(new Event("change", { bubbles: true }));

    if ((modalidadInicial === "virtual" || modalidadInicial === "mixto") && inputGrupo && filtrosIniciales.numero_ficha) {
      inputGrupo.value = filtrosIniciales.numero_ficha;
      cargarTrimestralizacionPorGrupo(filtrosIniciales.numero_ficha);
    }
  }
}

function renderizarTablaDesdeRegistros(registrosServer, emptyMessage = "No hay registros activos.") {
  const tbody = document.getElementById("tbody-horarios");
  if (!tbody) return;

  tbody.innerHTML = "";

  const activos = (Array.isArray(registrosServer) ? registrosServer : []).filter(
    (d) => d && (d.estado === 1 || d.estado === "1")
  );

  if (!activos.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500 text-center">${emptyMessage}</td></tr>`;
    toggleTabla(false);
    return;
  }

  const mapHorarios = new Map();
  activos.forEach((r) => {
    const id = r.id_horario ?? (r.id_horario === 0 ? 0 : null);
    if (id === null) return;

    if (!mapHorarios.has(id)) {
      mapHorarios.set(id, {
        id_horario: id,
        dia: r.dia,
        hora_inicio: r.hora_inicio,
        hora_fin: r.hora_fin,
        id_zona: r.id_zona,
        id_area: r.id_area,
        numero_trimestre: r.numero_trimestre,
        estado: r.estado,
        numero_ficha: r.numero_ficha,
        nivel_ficha: r.nivel_ficha,
        id_instructor: r.id_instructor,
        nombre_instructor: r.nombre_instructor,
        tipo_instructor: r.tipo_instructor,
        programa_formacion: r.programa_formacion,
        nombre_programa: r.nombre_programa,
        id_competencia: r.id_competencia,
        nombre_competencia: r.nombre_competencia,
        raesArray: [],
      });
    }

    const agr = mapHorarios.get(id);
    if (r.id_rae) {
      const textoRae = `${r.id_rae} - ${r.descripcion_rae ?? ""}`.trim();
      if (textoRae && !agr.raesArray.includes(textoRae)) {
        agr.raesArray.push(textoRae);
      }
    }
  });

  const horariosAgrupados = Array.from(mapHorarios.values());
  horariosCache = horariosAgrupados;

  horariosAgrupados.forEach((h) => {
    if (h.raesArray.length) {
      h.raesHtml = `<ul class="list-disc ml-5 mt-1">${h.raesArray
        .map((x) => `<li>${x}</li>`)
        .join("")}</ul>`;
    } else {
      h.raesHtml = `<span class="text-gray-500 italic">Sin especificar</span>`;
    }
  });

  const dias = ["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"];
  const horas = Array.from({ length: 16 }, (_, i) => i + 6);
  const horaMin = horas[0];
  const horaMaxExclusiva = horas[horas.length - 1] + 1;

  const mapaInicioPorDia = {};
  dias.forEach((dia) => {
    mapaInicioPorDia[dia] = {};
  });

  horariosAgrupados.forEach((r) => {
    const dia = String(r.dia || "").toUpperCase();
    if (!mapaInicioPorDia[dia]) return;

    const rStartRaw = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
    const rEndRaw = r.hora_fin
      ? parseInt(r.hora_fin.split(":")[0], 10)
      : rStartRaw + 1;

    if (!Number.isFinite(rStartRaw) || !Number.isFinite(rEndRaw)) return;

    const rStart = Math.max(horaMin, rStartRaw);
    const rEnd = Math.min(horaMaxExclusiva, Math.max(rStart + 1, rEndRaw));

    if (rStart >= horaMaxExclusiva) return;

    if (!mapaInicioPorDia[dia][rStart]) {
      mapaInicioPorDia[dia][rStart] = [];
    }
    mapaInicioPorDia[dia][rStart].push({ ...r, _rowStart: rStart, _rowEnd: rEnd });
  });

  const omitirFilasPorDia = {};
  dias.forEach((dia) => {
    omitirFilasPorDia[dia] = 0;
  });

  horas.forEach((hora, idx) => {
    const fila = document.createElement("tr");
    fila.className = "";
    fila.innerHTML = `<td class="hora-col p-3 whitespace-nowrap min-w-[110px] w-[110px]">
      ${String(hora).padStart(2, "0")}:00 - ${String(hora + 1).padStart(2, "0")}:00 </td>`;

    dias.forEach((dia) => {
      if (omitirFilasPorDia[dia] > 0) {
        omitirFilasPorDia[dia] -= 1;
        return;
      }

      const iniciosEnHora = mapaInicioPorDia[dia][hora] || [];

      if (!iniciosEnHora.length) {
        fila.innerHTML += `
            <td class="p-2 text-sm text-center leading-tight align-top zona-libre cursor-pointer"
              data-dia="${dia}"
              data-hora="${String(hora).padStart(2, "0")}: 00">
              <span class="text-gray-400 italic">Zona libre</span>
          </td>`;
        return;
      }

      const r = iniciosEnHora[0];
      const duracionHoras = Math.max(1, (r._rowEnd || (hora + 1)) - (r._rowStart || hora));
      const rowspan = Math.max(1, Math.min(duracionHoras, horaMaxExclusiva - hora));

      if (rowspan > 1) {
        omitirFilasPorDia[dia] = rowspan - 1;
      }

      const contenido = `
          <div class="registro border-l-4 border-l-green-600 bg-white rounded-md p-2 mb-2 shadow-sm"
              data-id="${r.id_horario || ""}"
              data-id-instructor="${r.id_instructor ?? ""}"
              data-instructor="${r.nombre_instructor ?? ""}"
              data-id-competencia="${r.id_competencia ?? ""}"
              data-competencia="${r.nombre_competencia ?? ""}"
              data-programa="${r.nombre_programa ?? ""}"
              data-ficha="${r.numero_ficha ?? ""}"
              data-nivel-ficha="${r.nivel_ficha ?? ""}"
              data-dia="${r.dia ?? ""}"
              data-hora-inicio="${r.hora_inicio ?? ""}"
              data-hora-fin="${r.hora_fin ?? ""}"
              data-hora-rango="${r.hora_inicio ?? ""} - ${r.hora_fin ?? ""}"
              data-raes='${JSON.stringify(r.raesArray)}' >
            <div class="font-bold text-green-700 text-sm mb-1">Competencia: ${r.nombre_competencia ?? "Sin competencia"}</div>
            <div class="flex items-start gap-1 text-xs text-gray-600 mb-1">
              <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              <span>${r.nombre_instructor ?? ""}</span>
            </div>
            <div class="flex items-start gap-1 text-xs text-gray-600 mb-1">
              <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
              </svg>
              <span class="ficha font-medium text-gray-700">
                ${r.numero_ficha ?? "—"}
              </span>

            </div>
            <div class="flex items-start gap-1 text-xs text-gray-500">
              <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
              </svg>
              <span>${duracionHoras} hora${duracionHoras > 1 ? "s" : ""}</span>
            </div>
          </div>`;

      fila.innerHTML += `
          <td rowspan="${rowspan}" class="p-2 text-sm text-center leading-tight align-top">
            ${contenido}
          </td>`;
    });

    tbody.appendChild(fila);
  });

  toggleTabla(true);
  popupCeldas();
  popupZonaLibre();
}

function configurarModalidadFormulario() {
  const modalidadForm = document.getElementById("modalidad");
  const areaField = document.getElementById("id_area")?.closest(".field");
  const zonaField = document.getElementById("id_zona")?.closest(".field");

  if (!modalidadForm) return;

  modalidadForm.addEventListener("change", () => {
    const modalidad = modalidadForm.value;

    if (modalidad === "presencial") {
      if (areaField) areaField.style.display = "";
      if (zonaField) zonaField.style.display = "";
    } else if (modalidad === "virtual" || modalidad === "mixto") {
      if (areaField) areaField.style.display = "none";
      if (zonaField) zonaField.style.display = "none";
    }
  });
}


// =====================
//Cargar trimestralizacion por grupo
// =====================
async function cargarTrimestralizacionPorGrupo(grupo) {
  const tbody = document.getElementById("tbody-horarios");
  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  try{
    const res = await fetch(`${API_BASE}src/controllers/TrimestralizacionController.php?accion=listarPorGrupo&numero_ficha=${grupo}`);
    const data = await res.json();
    const registrosServer = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
      ? data.data
      : [];

    renderizarTablaDesdeRegistros(
      registrosServer,
      `No se encontraron registros para el grupo ${grupo}.`
    );
  }catch (e){
    console.error(e);
    Toast.fire({ icon: "error", title: "Error al cargar datos por grupo" });
  }
}
  
// =======================
// CARGAR TRIMESTRALIZACIÓN
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  const selectArea = document.getElementById("selectArea");
  const id_area = selectArea ? selectArea.value : "";

  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  if (!id_zona || !id_area) {
    toggleTabla(false);
    return;
  }

  try {
    const modalidad = String(document.getElementById("selectModalidad")?.value || "presencial")
      .trim()
      .toLowerCase();
    const modalidadParam = modalidad === "mixta" ? "MIXTO" : modalidad.toUpperCase();

    const res = await fetch(
      `${API_BASE}src/controllers/TrimestralizacionController.php?accion=listar&id_zona=${id_zona}&id_area=${id_area}&modalidad=${encodeURIComponent(modalidadParam)}`
    );
    const data = await res.json();
    console.log("Datos recibidos del servidor:", data);
    const registrosServer = Array.isArray(data)
      ? data
      : Array.isArray(data.data)
      ? data.data
      : [];
    renderizarTablaDesdeRegistros(
      registrosServer,
      "No hay registros activos para esta zona y área."
    );

    Toast.fire({
      icon: "success",
      title: "Trimestralización cargada correctamente",
    });


  } catch (error) {
    console.error("Error al cargar:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
    Toast.fire({
      icon: "error",
      title: "Error al cargar trimestralización",
    });
  }
}

// =======================
// LISTAR INSTRUCTORES
// =======================
let listaInstructores = [];
let listaCompetencias = [];
let listaFichas = [];

async function cargarFichas() {
  try {
    const res = await fetch(
      `${API_BASE}src/controllers/FichaController.php?accion=listar`
    );
    
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    
    const data = await res.json();

    const array = Array.isArray(data)
      ? data
      : Array.isArray(data.data)
      ? data.data
      : [];

    if (array.length > 0) {
      // SOLO FICHAS ACTIVAS
      listaFichas = array.filter((f) => registroActivo(f.estado));
    }
    
    if (!listaFichas.length) {
      console.warn("No hay fichas activas en API, extrayendo de datos cargados...");
      extraerFichasDeHorarios();
    }
  } catch (error) {
    console.error("Error cargando fichas del API:", error);
    extraerFichasDeHorarios();
  }
}

function extraerFichasDeHorarios() {
  // Extrae fichas únicas de los horarios ya cargados
  if (horariosCache && horariosCache.length > 0) {
    const fichasSet = new Set();
    horariosCache.forEach((h) => {
      if (h.numero_ficha) {
        fichasSet.add(JSON.stringify({
          numero_ficha: h.numero_ficha,
          nivel_ficha: h.nivel_ficha || "Sin nivel"
        }));
      }
    });
    
    listaFichas = Array.from(fichasSet).map(f => JSON.parse(f));
  }
  
  if (!listaFichas.length) {
    console.warn("No se encontraron fichas en los horarios");
    listaFichas = [];
  }
}

async function cargarCompetencias() {
  try {
    const res = await fetch(
      `${API_BASE}src/controllers/CompetenciaController.php?accion=listar`
    );
    const data = await res.json();

    const array = Array.isArray(data)
      ? data
      : Array.isArray(data.data)
      ? data.data
      : [];

    // SOLO COMPETENCIAS ACTIVAS
    listaCompetencias = array.filter((c) => registroActivo(c.estado));

    if (!listaCompetencias.length) {
      console.warn("No hay competencias activas");
    }
  } catch (error) {
    console.error("Error cargando competencias:", error);
    listaCompetencias = [];
  }
}

async function cargarInstructores() {
  try {
    let instructoresArray = [];

    const resInstructor = await fetch(
      `${API_BASE}src/controllers/InstructorController.php?accion=listar`
    );

    if (resInstructor.ok) {
      const data = await resInstructor.json();
      instructoresArray = Array.isArray(data)
        ? data
        : Array.isArray(data.data)
        ? data.data
        : [];
    } else {
      const resUsuarios = await fetch(
        `${API_BASE}src/controllers/UsuarioController.php?accion=listar&cargo=INSTRUCTOR`
      );
      const dataUsuarios = await resUsuarios.json();
      const usuariosArray = Array.isArray(dataUsuarios)
        ? dataUsuarios
        : Array.isArray(dataUsuarios.data)
        ? dataUsuarios.data
        : [];

      instructoresArray = usuariosArray.map((u) => ({
        id_instructor: u.id_instructor ?? u.id_usuario,
        nombre_instructor: u.nombre_instructor ?? u.nombre_completo ?? "",
        tipo_instructor: u.tipo_instructor ?? u.tipo_contrato ?? "",
        estado: u.estado ?? 1,
      }));
    }

    // FILTRAR SOLO INSTRUCTORES ACTIVOS (estado = 1)
    listaInstructores = instructoresArray.filter((i) => registroActivo(i.estado));

    if (listaInstructores.length > 0) {
      llenarSelectInstructores(listaInstructores);
    } else {
      Toast.fire({
        icon: "warning",
        title: "No hay instructores activos",
      });
      listaInstructores = [];
    }
  } catch (error) {
    console.error("Error al cargar instructores:", error);
    Toast.fire({
      icon: "error",
      title: "No se pudo cargar instructores",
    });
    listaInstructores = [];
  }
}

// opcional: si en alguna otra vista tienes un select con id="selectInstructor", esta función lo llenará.
function llenarSelectInstructores(instructores) {
  const selectInstructor = document.getElementById("selectInstructor");
  if (!selectInstructor) return;
  selectInstructor.innerHTML =
    '<option value="">Seleccione un instructor</option>';

  instructores.forEach((i) => {
    const option = document.createElement("option");
    option.value = i.id_instructor;
    option.textContent = `${i.nombre_instructor} - ${i.tipo_instructor}`;
    selectInstructor.appendChild(option);
  });
}

async function obtenerRoesPorCompetencia(id_competencia) {
  try {
    const res = await fetch(
      `${API_BASE}src/controllers/RaeController.php?accion=porCompetencia&id_competencia=${id_competencia}`
    );
    const data = await res.json();

    if (Array.isArray(data)) return data;
    return [];
  } catch (e) {
    console.error("Error obteniendo RAEs:", e);
    return [];
  }
}




// =======================
// CARGAR ÁREAS Y ZONAS ACTIVAS PARA LOS FILTROS SUPERIORES
// =======================
(async function cargarFiltrosSuperiores() {
  const selArea = document.getElementById("selectArea");
  const selZona = document.getElementById("selectZona");

  if (!selArea || !selZona) return;

  const base = (window.BASE_URL || "").replace(/\/+$/, "/");

  try {
    const respAreas = await fetch(base + "src/controllers/AreaController.php?accion=listar");
    const dataAreas = await respAreas.json();

    selArea.innerHTML = `
          <option value="" hidden>SELECCIONE EL ÁREA</option>
      `;

    let activas = 0;
    (dataAreas?.data || []).forEach((a) => {
      if (registroActivo(a.estado)) {
        activas++;
        selArea.innerHTML += `
                  <option value="${a.id_area}">${a.nombre_area}</option>
              `;
      }
    });

    // Si NO hay ninguna área activa en los filtros superiores
    if (activas === 0) {
      selArea.innerHTML = `
        <option value="" hidden selected>SELECCIONE EL ÁREA</option>
        <option value="" disabled>Sin datos disponibles</option>
      `;

      selZona.innerHTML = `
        <option value="" hidden selected>SELECCIONE LA ZONA</option>
        <option value="" disabled>Sin datos disponibles</option>
      `;

      // NO deshabilitamos para que puedan desplegar y ver el mensaje
      Toast.fire({ icon: "info", title: "No hay áreas disponibles" });
      return;
    }

    // Por defecto, las zonas quedan solo con placeholder y deshabilitadas
    selZona.innerHTML = `
          <option value="" hidden selected>SELECCIONE LA ZONA</option>
      `;
    selZona.disabled = true;

    // Se deja que cargarAreasYZonas se encargue de llenar zonas
    // según el área elegida y de habilitar el select.

  } catch (error) {
    console.error("❌ Error cargando áreas/zonas:", error);
  }
})();



// =======================
// Funcion de editar
// ======================

async function editarTrimestralizacion (reg){
  await cargarInstructores();
  await cargarCompetencias();
  await cargarFichas();

  const dia = reg.getAttribute("data-dia") || "Sin día";
  const horaInicio = reg.getAttribute("data-hora-inicio") || "";
  const horaFin = reg.getAttribute("data-hora-fin") || "";
  const ficha = reg.getAttribute("data-ficha") || "";
  const idInstructorActual = reg.getAttribute("data-id-instructor") || "";
  const idCompetenciaActual = reg.getAttribute("data-id-competencia") || "";
  const raesActuales = JSON.parse(reg.getAttribute("data-raes") || "[]");
  const idHorario = reg.getAttribute("data-id") || "";
  const id_zona_val = document.getElementById("selectZona")?.value || id_zona;
  const id_area_val = document.getElementById("selectArea")?.value;

  const optionInstructors = listaInstructores.map(i =>
  `<option value="${i.id_instructor}" ${String(i.id_instructor) === String(idInstructorActual) ? "selected" : ""}>
    ${i.nombre_instructor} - ${i.tipo_instructor}
  </option>`
  ).join("");

  const optionCompetencias = listaCompetencias.map(c =>
  `<option value="${c.id_competencia}" ${String(c.id_competencia) === String(idCompetenciaActual) ? "selected" : ""}>
    ${c.nombre_competencia}
  </option>`
  ).join("");

  const optionFichas = listaFichas.map(f => {
    const nivel = f.nivel_formacion || f.nivel_ficha || "Sin nivel";
    return `<option value="${f.numero_ficha}" ${String(f.numero_ficha) === String(ficha) ? "selected" : ""}>
      ${f.numero_ficha} - Nivel ${nivel}
    </option>`;
  }).join("");

  const horas = Array.from({ length: 16 }, (_, i) => i + 6);
  const horaOpcionesInicio = horas.map(h => `<option value="${String(h).padStart(2, "0")}:00" ${String(h).padStart(2, "0") + ":00" === horaInicio ? "selected" : ""}>${String(h).padStart(2, "0")}:00</option>`).join("");
  const horaOpcionesFin = horas.map(h => `<option value="${String(h).padStart(2, "0")}:00" ${String(h).padStart(2, "0") + ":00" === horaFin ? "selected" : ""}>${String(h).padStart(2, "0")}:00</option>`).join("");

  Swal.fire({
    title: "✏️ Editar Horario",
    html: `
    <div class="bg-white p-2 rounded-lg">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Día</label>
          <select id="editDia" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            ${["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"].map(d => `<option value="${d}" ${d === dia ? "selected" : ""}>${d}</option>`).join("")}
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Ficha / Grupo</label>
          <select id="editFicha" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            <option value="">Seleccione una ficha</option>
            ${optionFichas}
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Hora Inicio</label>
          <select id="editHoraInicio" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            <option value="">Seleccionar hora</option>
            ${horaOpcionesInicio}
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Hora Fin</label>
          <select id="editHoraFin" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            <option value="">Seleccionar hora</option>
            ${horaOpcionesFin}
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Instructor</label>
          <select id="editInstructor" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            <option value="">Seleccione un instructor</option>
            ${optionInstructors}
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Competencia</label>
          <select id="editCompetencia" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent">
            <option value="">Seleccione una competencia</option>
            ${optionCompetencias}
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-[#00324D] mb-1">RAEs</label>
          <div id="editRAEs" class="max-h-48 overflow-auto border border-gray-300 p-3 rounded-md text-sm bg-gray-50"></div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-[#00324D] mb-1">Descripción (Opcional)</label>
          <textarea id="editDescripcion" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#00324d]/20 focus:border-transparent" placeholder="Notas adicionales del horario..."></textarea>
        </div>
      </div>
    </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Guardar cambios",
    cancelButtonText: "Cancelar",
    didOpen: async () => {
      await renderRAEsPopup(idCompetenciaActual, raesActuales);

      document.getElementById("editCompetencia").addEventListener("change", (e) => {
        renderRAEsPopup(e.target.value, []);
      });
    }, 
    preConfirm: () => {
      const dia = document.getElementById("editDia").value;
      const horaInicio = document.getElementById("editHoraInicio").value;
      const horaFin = document.getElementById("editHoraFin").value;
      const ficha = document.getElementById("editFicha").value;
      const idInstructor = document.getElementById("editInstructor").value;
      const idCompetencia = document.getElementById("editCompetencia").value;

      const raes = [...document.querySelectorAll("#editRAEs input:checked")].map(chk => chk.value);

      if (!dia || !horaInicio || !horaFin || !ficha || !idInstructor || !idCompetencia || raes.length === 0) {
        Swal.showValidationMessage("⚠️ Completa todos los campos y selecciona al menos un RA.");
        return false;
      }
      if (timeToMinutes(horaFin) <= timeToMinutes(horaInicio)) {
        Swal.showValidationMessage("⏰ Hora fin debe ser posterior a hora inicio.");
        return false;
      }
      if (hasOverlap({ dia, inicio: horaInicio, fin: horaFin, excludeId: idHorario })) {
        Swal.showValidationMessage("⚠️ Esta franja ya está ocupada en ese día.");
        return false;
      }
      return {
        id_horario: idHorario,
        dia,
        numero_ficha: ficha,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        id_instructor: idInstructor,
        id_competencia: idCompetencia,
        raes: raes,
        id_zona: id_zona_val,
        id_area: id_area_val,
        descripcion: document.getElementById("editDescripcion").value || ""
      };
    }
}). then (async (res) => {
  if(!res.isConfirmed) return;
  
  try{
    // El backend espera un ARRAY de registros
    const payload = [res.value];

    const resUpdate = await fetch(`${API_BASE}src/controllers/TrimestralizacionController.php?accion=actualizar`, {
      method: "POST",
      headers: {"Content-Type" : "application/json"},
      body: JSON.stringify(payload)
  });
  
  if (!resUpdate.ok) {
    throw new Error(`HTTP error! status: ${resUpdate.status}`);
  }
  
  const data = await resUpdate.json();

  // El backend retorna {success: true} o {success: false}
  const esExito = data.success === true || data.success === "true" || data.status === "success";
  
  if (esExito) {
    dispararToastsExcedente(data.warnings);
    await Swal.fire({
      icon: "success",
      title: "Trimestralización actualizada",
      text: data.message || data.mensaje || "El horario se editó correctamente.",
      confirmButtonText: "Aceptar"
    });
    Toast.fire({
      icon: "success",
      title: "Horario actualizado correctamente"
    });
    cargarTrimestralizacion();
  } else {
    console.error("Error del servidor:", data);
    await Swal.fire({
      icon: "error",
      title: "No se pudo editar",
      text: data.error || data.message || data.mensaje || "Error al actualizar el horario.",
      confirmButtonText: "Entendido"
    });
    Toast.fire({
      icon: "error",
      title: data.error || data.message || "Error al actualizar el horario"
    });
  }
} catch (e) {
  console.error("Error en actualización:", e);
  await Swal.fire({
    icon: "error",
    title: "Error al editar",
    text: e.message || "Ocurrió un problema de conexión al actualizar.",
    confirmButtonText: "Entendido"
  });
  Toast.fire({icon: "error", title: `Error: ${e.message}`});
}
});
}



async function renderRAEsPopup(idCompetencia, raesMarcados = []) {
  const cont = document.getElementById("editRAEs");
  if (!cont) return;
  cont.innerHTML = "<p class='text-gray-400 italic'>Cargando RAEs...</p>";

  if (!idCompetencia) {
    cont.innerHTML = "<p class='text-gray-400 italic'>Seleccione una competencia</p>";
    return;
  }

  const raes = await obtenerRoesPorCompetencia(idCompetencia);

  cont.innerHTML = raes.map(rae => {
    const desc = (rae.descripcion || rae.descripcion_rae || "").trim();
    const checked = raesMarcados.includes(`${rae.id_rae} - ${desc}`) ? "checked" : "";
    return `
      <label class="flex items-center gap-2 mb-1">
        <input type="checkbox" value="${rae.id_rae}" ${checked}>
        ${rae.id_rae} - ${desc}
      </label>
    `;
  }).join("");
}


// =======================
// ELIMINAR TODO
// =======================
async function confirmarEliminar() {
  const selectArea = document.getElementById("selectArea");
  const id_area = selectArea ? selectArea.value : "";

  if (!id_zona || !id_area) {
    Toast.fire({
      icon: "warning",
      title:
        "Debes seleccionar un área y una zona antes de limpiar la trimestralización",
    });
    return;
  }

  try {
    const res = await fetch(
      `${API_BASE}src/controllers/TrimestralizacionController.php?accion=eliminar&id_zona=${id_zona}&id_area=${id_area}`
    );
    const data = await res.json();

    Toast.fire({
      icon: data.status === "success" || data.success ? "success" : "warning",
      title: data.message || "Trimestralización eliminada correctamente",
    });

    cargarTrimestralizacion();
  } catch (err) {
    console.error("confirmarEliminar error:", err);
    Toast.fire({ icon: "error", title: "Error al eliminar" });
  } finally {
    cerrarModal();
  }
}

function mostrarModalEliminar() {
  const modal = document.getElementById("modalEliminar");
  if (modal) {
    modal.classList.remove("hidden");
    modal.style.display = "";
    modal.style.pointerEvents = "";
    modal.style.visibility = "";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = ""; });
  }
}
function cerrarModal() {
  const modal = document.getElementById("modalEliminar");
  if (!modal) return;
  const activeEl = document.activeElement;
  if (activeEl && modal.contains(activeEl)) activeEl.blur();
  modal.classList.add("hidden");
  modal.classList.remove("flex", "block", "items-center", "justify-center");
  modal.style.display = "none";
  modal.style.pointerEvents = "none";
  modal.style.visibility = "hidden";
  modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = "none"; });
  document.body.style.overflow = "";
  document.body.classList.remove("overflow-hidden");
}

// =======================
// DESCARGAR PDF
// =======================
async function descargarPDF() {
  const { jsPDF } = window.jspdf;
  const elementoOriginal = document.querySelector("#tabla-horarios");

  if (!elementoOriginal) {
    Toast.fire({
      icon: "error",
      title: "No se encontró la tabla para exportar",
    });
    return;
  }

  const elementoClonado = elementoOriginal.cloneNode(true);
  elementoClonado.style.maxHeight = "none";
  elementoClonado.style.overflow = "visible";
  elementoClonado.style.height = "auto";
  elementoClonado.style.width = "100%";
  elementoClonado.style.position = "absolute";
  elementoClonado.style.top = "0";
  elementoClonado.style.left = "-9999px";

  document.body.appendChild(elementoClonado);

  await new Promise((r) => setTimeout(r, 300));

  const canvas = await html2canvas(elementoClonado, {
    scale: 1.5,
    useCORS: true,
    backgroundColor: "#ffffff",
    scrollX: 0,
    scrollY: 0,
    windowWidth: elementoClonado.scrollWidth,
    windowHeight: elementoClonado.scrollHeight,
    logging: false,
  });

  document.body.removeChild(elementoClonado);

  const imgData = canvas.toDataURL("image/jpeg", 0.75);
  const pdf = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
    compress: true,
  });

  const pdfWidth = pdf.internal.pageSize.getWidth();
  const pdfHeight = pdf.internal.pageSize.getHeight();

  const marginX = 10;
  const marginY = 15;

  const imgWidth = pdfWidth - marginX * 2;
  const imgHeight = (canvas.height * imgWidth) / canvas.width;

  let position = marginY;
  let heightLeft = imgHeight;

  pdf.setFontSize(16);
  pdf.text(
    `Trimestralización - Zona ${id_zona}`,
    pdfWidth / 2,
    10,
    { align: "center" }
  );

  pdf.addImage(imgData, "jpeg", marginX, position, imgWidth, imgHeight);
  heightLeft -= pdfHeight - position;

  while (heightLeft > 0) {
    pdf.addPage();
    position = 0;
    pdf.addImage(
      imgData,
      "jpeg",
      marginX,
      position - heightLeft,
      imgWidth,
      imgHeight
    );
    heightLeft -= pdfHeight;
  }

  pdf.save(`trimestralizacion_zona_${id_zona}.pdf`);
}


// =======================
// ABRIR / CERRAR MODAL CREAR TRIMESTRALIZACIÓN
// =======================

const btnAbrirModalTrimestralizacion = document.getElementById("btnAbrirModal");

function abrirModal() {
  const modal = document.getElementById("modalCrearLanding");
  if (!modal) {
    console.error("❌ No existe #modalCrearLanding");
    return;
  }
  modal.classList.remove("hidden");
  modal.style.display = "";
  modal.style.pointerEvents = "";
  modal.style.visibility = "";
  modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = ""; });
}

function cerrarModalCrear() {
  const modal = document.getElementById("modalCrearLanding");
  if (!modal) return;
  const activeEl = document.activeElement;
  if (activeEl && modal.contains(activeEl)) activeEl.blur();
  modal.classList.add("hidden");
  modal.classList.remove("flex", "block", "items-center", "justify-center");
  modal.style.display = "none";
  modal.style.pointerEvents = "none";
  modal.style.visibility = "hidden";
  modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = "none"; });
  document.body.style.overflow = "";
  document.body.classList.remove("overflow-hidden");
  if (btnAbrirModalTrimestralizacion?.focus) try { btnAbrirModalTrimestralizacion.focus(); } catch (e) {}
}

// Botón principal "Nueva trimestralización"
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("btnAbrirModal");
  const btnCerrar = document.getElementById("btnCerrarModal");
  const backdrop = document.getElementById("modalBackdrop");

  if (btn) {
    btn.addEventListener("click", abrirModal);
  }

  if (btnCerrar) {
    btnCerrar.addEventListener("click", cerrarModalCrear);
  }

  if (backdrop) {
    backdrop.addEventListener("click", cerrarModalCrear);
  }
});


function popupCeldas(){
  document.querySelectorAll("#tbody-horarios .registro").forEach(reg => {
    reg.classList.add("cursor-pointer", "hover:bg-green-50");
    reg.addEventListener("click", () => {
        const competencia = reg.getAttribute("data-competencia") || "Sin competencia"
        const ficha = reg.getAttribute("data-ficha") || "Sin ficha"
        const programa = reg.getAttribute("data-programa") || "Sin programa"
        const instructor = reg.getAttribute("data-instructor") || "Sin instructor"
        const dia = reg.getAttribute("data-dia") || "Sin día"
        const hora = reg.getAttribute("data-hora-rango") || reg.getAttribute("data-hora-inicio") || "Sin hora"
        
        let raes = [];
        try {
          const raesArr = JSON.parse(reg.getAttribute("data-raes") || "[]");
          if (Array.isArray(raesArr) && raesArr.length) {
            raes = raesArr.join(", ");
        }
      }
        catch(e){
          console.error("Error con las Raes:", e);

        }
        const accionesPopup = IS_AUTHENTICATED
          ? `
              <div class="mt-6 flex justify-end gap-2">
                <button id="btnEditarRegistro"
                  class="bg-[#00324d] text-white text-sm px-4 py-2 rounded-lg hover:bg-[#00304D] transition">
                  Editar
                </button>
                <button id="btnCerrarPopup"
                  class="bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                  Aceptar
                </button>
              </div>
            `
          : `
              <div class="mt-6 flex justify-end gap-2">
                <button id="btnCerrarPopup"
                  class="bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                  Cerrar
                </button>
              </div>
            `;

        Swal.fire({
          title: "",
          showCloseButton: false,
          showConfirmButton: false,
          html: `
              <div class="text-left" style="max-height: 420px; overflow-y: auto;">
                <div class="mb-4 pb-2 flex items-center justify-between gap-3">
                  <h2 class="text-xl font-bold text-[#00324D]">Datos de Trimestralización</h2>
                  <button id="btnCerrarXPopup" type="button" class="text-gray-400 hover:text-gray-700 focus:outline-none text-2xl w-8 h-8 flex items-center justify-center leading-none">&times;</button>
                </div>

                <!-- Encabezado día / hora -->
                <div class="mb-4 pb-2 border-b border-[#000]">
                  <p class="text-sm text-gray-500">${dia} • ${hora}</p>
                </div>

                <!-- Ítems -->
                <div class="space-y-3 text-sm">
                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Instructor</p>
                      <p class="text-gray-800 font-medium">${instructor}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                      <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Grupo</p>
                      <p class="text-gray-800 font-medium">${ficha}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-purple-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 2L1 6l9 4 9-4-9-4z"/>
                      <path d="M4 8v4c0 1.5 2.7 3 6 3s6-1.5 6-3V8l-6 2.7L4 8z"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Programa de Formación</p>
                      <p class="text-gray-800 font-medium">${programa}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H4z"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Competencia</p>
                      <p class="text-gray-800 font-medium">${competencia}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 10-1.414-1.414L9 8.757 7.879 7.636a1 1 0 10-1.414 1.414l1.828 1.829a1 1 0 001.414 0l3.829-3.829z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">RAEs</p>
                      <p class="text-gray-800 font-medium">${raes.replace(/\|/g, ", ")}</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 mt-0.5 text-gray-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9.414a2 2 0 00-.586-1.414l-5.414-5.414A2 2 0 0010.586 2H4z"/>
                      <path d="M9 2v5a2 2 0 002 2h5"/>
                    </svg>
                    <div>
                      <p class="text-gray-400 text-xs">Descripción de la jornada</p>
                      <p class="text-gray-800 font-medium">Descripción de la jornada</p>
                    </div>
                  </div>
                </div>
              </div>

              ${accionesPopup}
            `,
            showConfirmButton: false,
            didOpen: () => {
          document.getElementById("btnCerrarXPopup")?.addEventListener("click", () => {
            Swal.close();
          });

          document.getElementById("btnCerrarPopup").addEventListener("click", () => {
            Swal.close();
          });
        
          if (IS_AUTHENTICATED) {
            document.getElementById("btnEditarRegistro")?.addEventListener("click", () => {
              Swal.close();
              editarTrimestralizacion(reg);
            });
          }
        },
      });
    });
  })
}

function popupZonaLibre(){
  document.querySelectorAll("#tbody-horarios td.zona-libre").forEach(td => {
    td.addEventListener("click", () => {
      const dia = td.getAttribute("data-dia") || "Sin día";
      const hora = td.getAttribute("data-hora") || "Sin hora";
      const accionesZonaLibre = IS_AUTHENTICATED
        ? `
          <div class="mt-8 flex justify-end gap-3">
            <button id="btnCerrarPopupZonaLibre" class="bg-[#00324d] text-white text-sm px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-full sm:w-auto">Cerrar</button>
            <button id="btnAbrirModalZonaLibre" class="bg-[#00324d] text-white text-sm px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-full sm:w-auto">
              Agregar Horario
            </button>
          </div>
        `
        : `
          <div class="mt-8 flex justify-end gap-3">
            <button id="btnCerrarPopupZonaLibre" class="bg-[#00324d] text-white text-sm px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-full sm:w-auto">Cerrar</button>
          </div>
        `;

      Swal.fire({
        title: "Zona libre",
        html:`
        <div class="mb-3 text-sm text-left space-y-2 text-gray-500 italic">
          <p><strong>Día:</strong> ${dia}</p>
          <p><strong>Hora:</strong> ${hora}</p>
          <p>En esta franja no hay ninguna competencia programada.</p>
          </div>  
          ${accionesZonaLibre}
          `,
          showConfirmButton: false,
          didOpen: () => {
            document.getElementById("btnCerrarPopupZonaLibre").addEventListener("click", () => {
              Swal.close();
            });
            if (IS_AUTHENTICATED) {
              document.getElementById("btnAbrirModalZonaLibre")?.addEventListener("click", () => {
                Swal.close();
                abrirModal();
              });
            }
          }
      });
    });
  })
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarAreasYZonas();
  configurarFiltros();
  cargarFichas();
  cargarInstructores();
  cargarCompetencias();
  configurarModalidadFormulario();
  if (id_zona) {
    toggleTabla(true);
    cargarTrimestralizacion();
  } else toggleTabla(false);

  const btnActualizar = document.getElementById("btn-actualizar");
  if (btnActualizar) {
    btnActualizar.addEventListener("click", abrirModalGestionHoras);
  }

  document.getElementById("btnCerrarGestionHoras")?.addEventListener("click", cerrarModalGestionHoras);
  document.getElementById("btnAceptarGestionHoras")?.addEventListener("click", cerrarModalGestionHoras);
  document.getElementById("tabGestionHorasInstructores")?.addEventListener("click", () => {
    gestionHorasTabActual = "instructores";
    renderGestionHoras();
  });
  document.getElementById("tabGestionHorasGrupos")?.addEventListener("click", () => {
    gestionHorasTabActual = "grupos";
    renderGestionHoras();
  });
  document.querySelector("#modalGestionHoras .gh-backdrop")?.addEventListener("click", cerrarModalGestionHoras);
  document.getElementById("btnIrGestionInstructores")?.addEventListener("click", () => {
    window.location.href = `${API_BASE}index.php?page=src/views/gestionInstructores`;
  });
  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    const modal = document.getElementById("modalGestionHoras");
    if (!modal || modal.classList.contains("hidden")) return;
    cerrarModalGestionHoras();
  });

  const mostrarAlertaSinConexion = () => {
    Swal.fire({
      icon: "error",
      title: "Sin conexión a la red",
      text: "No se pudo completar la consulta. Verifica tu conexión e intenta nuevamente.",
      showCancelButton: true,
      confirmButtonText: "Volver al inicio",
      cancelButtonText: "Cerrar"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `${API_BASE}index.php?page=landing`;
      }
    });
  };

  window.addEventListener("offline", mostrarAlertaSinConexion);

  document.addEventListener("click", (event) => {
    if (!navigator.onLine) {
      const objetivo = event.target.closest("button, a, select, input");
      if (objetivo) {
        mostrarAlertaSinConexion();
      }
    }
  });
});
