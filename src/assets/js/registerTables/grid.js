/**
 * Grilla semanal, caché de horarios y popups (delegación en #tabla-horarios).
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const U = RT.util;
  const TP = RT.templates;

  const G = {};

  G.renderizarTablaDesdeRegistros = function (registrosServer, emptyMessage, opts) {
    emptyMessage = emptyMessage === undefined ? "No hay registros activos." : emptyMessage;
    opts = opts || {};
    const tbody = document.getElementById("tbody-horarios");
    if (!tbody) return false;

    tbody.innerHTML = "";

    const activos = (Array.isArray(registrosServer) ? registrosServer : []).filter(
      (d) => d && (d.estado === 1 || d.estado === "1")
    );

    if (!activos.length) {
      tbody.innerHTML = "";
      const emptyMode = opts.filtersApplied ? "filtered-empty" : "default";
      RT.ui.toggleTabla(false, emptyMode);
      return false;
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
    S.horariosCache = horariosAgrupados;

    horariosAgrupados.forEach((h) => {
      if (h.raesArray.length) {
        h.raesHtml = `<ul class="list-disc ml-5 mt-1">${h.raesArray.map((x) => `<li>${x}</li>`).join("")}</ul>`;
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
      const rEndRaw = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStartRaw + 1;

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

    horas.forEach((hora) => {
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
        const duracionHoras = Math.max(1, (r._rowEnd || hora + 1) - (r._rowStart || hora));
        const rowspan = Math.max(1, Math.min(duracionHoras, horaMaxExclusiva - hora));

        if (rowspan > 1) {
          omitirFilasPorDia[dia] = rowspan - 1;
        }

        const nivelGrupoRaw = String(r.nivel_ficha ?? "").trim();
        const nivelGrupoNorm = nivelGrupoRaw
          ? nivelGrupoRaw.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase()
          : "";
        const nivelGrupoTxt = !nivelGrupoRaw
          ? "Sin nivel"
          : nivelGrupoNorm === "tecnologo"
          ? "Tecnólogo"
          : nivelGrupoRaw;
        const contenido = `
          <div class="registro horario-registro h-full flex flex-col items-start justify-start text-left cursor-pointer hover:bg-gray-50"
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
              data-raes='${JSON.stringify(r.raesArray)}'
              >
            <div class="font-bold text-sm horario-registro-line" style="color: #39a900;">Competencia: ${r.nombre_competencia ?? "Sin competencia"}</div>
            <div class="horario-registro-line w-full flex items-center justify-start gap-1 text-xs text-gray-600">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
              <span>${r.nombre_instructor ?? ""}</span>
            </div>
            <div class="horario-registro-line w-full flex items-center justify-start gap-1 text-xs text-gray-600">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
              </svg>
              <span class="ficha font-medium text-gray-700">
                ${U.escapeHtml(nivelGrupoTxt)} · ${U.escapeHtml(String(r.numero_ficha ?? "—"))}
              </span>
            </div>
            <div class="horario-registro-line w-full flex items-center justify-start gap-1 text-xs text-gray-500">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
              </svg>
              <span>${duracionHoras} hora${duracionHoras > 1 ? "s" : ""}</span>
            </div>
          </div>`;

        fila.innerHTML += `
          <td rowspan="${rowspan}" class="td-con-registro p-0 text-sm text-left leading-tight align-middle">
            ${contenido}
          </td>`;
      });

      tbody.appendChild(fila);
    });

    RT.ui.toggleTabla(true);
    G.bindTablePopupDelegates();
    return true;
  };

  G.openTrimestralizacionPopup = function (reg) {
    const competencia = reg.getAttribute("data-competencia") || "Sin competencia";
    const ficha = reg.getAttribute("data-ficha") || "Sin ficha";
    const programa = reg.getAttribute("data-programa") || "Sin programa";
    const instructor = reg.getAttribute("data-instructor") || "Sin instructor";
    const dia = reg.getAttribute("data-dia") || "Sin día";
    const hora = reg.getAttribute("data-hora-rango") || reg.getAttribute("data-hora-inicio") || "Sin hora";

    let raesDisplay = "";
    try {
      const raesArr = JSON.parse(reg.getAttribute("data-raes") || "[]");
      if (Array.isArray(raesArr) && raesArr.length) {
        raesDisplay = raesArr.join(", ");
      }
    } catch (e) {
      console.error("Error con las Raes:", e);
    }

    const accionesHtml = TP.accionesPopupTrimestralizacion(RT.IS_AUTHENTICATED);
    const html = TP.trimestralizacionPopupHtml({
      competencia,
      ficha,
      programa,
      instructor,
      dia,
      hora,
      raesDisplay,
      accionesHtml,
    });

    Swal.fire({
      title: "",
      width: "32em",
      showCloseButton: false,
      showConfirmButton: false,
      html: html,
      didOpen: () => {
        document.getElementById("btnCerrarXPopup")?.addEventListener("click", () => {
          Swal.close();
        });

        document.getElementById("btnCerrarPopup").addEventListener("click", () => {
          Swal.close();
        });

        if (RT.IS_AUTHENTICATED) {
          document.getElementById("btnEditarRegistro")?.addEventListener("click", () => {
            Swal.close();
            RT.edit.editarTrimestralizacion(reg);
          });
        }
      },
    });
  };

  G.openZonaLibrePopup = function (td) {
    const dia = td.getAttribute("data-dia") || "Sin día";
    const hora = td.getAttribute("data-hora") || "Sin hora";
    const accionesZonaLibre = TP.accionesZonaLibre(RT.IS_AUTHENTICATED);
    const html = TP.zonaLibrePopupHtml(dia, hora, accionesZonaLibre);

    Swal.fire({
      title: "Zona libre",
      html: html,
      showConfirmButton: false,
      didOpen: () => {
        document.getElementById("btnCerrarPopupZonaLibre").addEventListener("click", () => {
          Swal.close();
        });
        if (RT.IS_AUTHENTICATED) {
          document.getElementById("btnAbrirModalZonaLibre")?.addEventListener("click", () => {
            Swal.close();
            RT.modals.abrirModal();
          });
        }
      },
    });
  };

  G.bindTablePopupDelegates = function () {
    const host = document.getElementById("tabla-horarios");
    if (!host || host.dataset.rtPopupsBound === "1") return;
    host.dataset.rtPopupsBound = "1";
    host.addEventListener("click", (e) => {
      const reg = e.target.closest("#tbody-horarios .registro");
      if (reg) {
        e.preventDefault();
        G.openTrimestralizacionPopup(reg);
        return;
      }
      const td = e.target.closest("#tbody-horarios td.zona-libre");
      if (td) {
        G.openZonaLibrePopup(td);
      }
    });
  };

  RT.grid = G;
})(window);
