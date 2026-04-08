/**
 * Edición de horario: solapamiento, modal y envío al servidor.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const U = RT.util;
  const T = RT.Toast;
  const E = {};

  E.hasOverlap = function ({ dia, inicio, fin, excludeId }) {
    const start = U.timeToMinutes(inicio);
    const end = U.timeToMinutes(fin);
    if (start === null || end === null) return false;

    return S.horariosCache.some((r) => {
      if (!r || String(r.id_horario) === String(excludeId)) return false;
      if (String(r.dia || "").toUpperCase() !== String(dia || "").toUpperCase()) return false;

      const rStart = U.timeToMinutes(r.hora_inicio);
      const rEnd = U.timeToMinutes(r.hora_fin);
      if (rStart === null || rEnd === null) return false;

      return start < rEnd && end > rStart;
    });
  };

  E.refreshEditarHorarioNativeSelectsUi = function () {
    ["editDia", "editHoraInicio", "editHoraFin"].forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      const cw = el.closest(".combobox-wrapper");
      if (cw && typeof cw._cbUpdateInput === "function") cw._cbUpdateInput();
    });
  };

  E.capturarSnapshotEdicionHorario = function () {
    const raes = [...document.querySelectorAll("#editRAEs input:checked")]
      .map((chk) => String(chk.value))
      .sort();
    return {
      dia: String(document.getElementById("editDia")?.value ?? ""),
      horaInicio: U.normalizarHoraParaSelectEditar(document.getElementById("editHoraInicio")?.value ?? ""),
      horaFin: U.normalizarHoraParaSelectEditar(document.getElementById("editHoraFin")?.value ?? ""),
      ficha: String(document.getElementById("editFicha")?.value ?? ""),
      idInstructor: String(document.getElementById("editInstructor")?.value ?? ""),
      idCompetencia: String(document.getElementById("editCompetencia")?.value ?? ""),
      raesKey: raes.join(","),
      descripcion: String(document.getElementById("editDescripcion")?.value ?? "").trim(),
    };
  };

  E.snapshotsEdicionHorarioIguales = function (a, b) {
    if (!a || !b) return false;
    return (
      a.dia === b.dia &&
      a.horaInicio === b.horaInicio &&
      a.horaFin === b.horaFin &&
      a.ficha === b.ficha &&
      a.idInstructor === b.idInstructor &&
      a.idCompetencia === b.idCompetencia &&
      a.raesKey === b.raesKey &&
      a.descripcion === b.descripcion
    );
  };

  E.ensureEditarHorarioNativeSelectsEnhanced = function () {
    if (w._editModalNativeEnhanced) return;
    if (typeof ComboboxComponent === "undefined" || typeof ComboboxComponent.enhanceSelectStyled !== "function") return;
    if (!document.getElementById("modalEditarHorario")) return;
    ComboboxComponent.enhanceSelectStyled({
      selector: "#modalEditarHorario select.js-edit-horario-native",
      placeholder: "Seleccione…",
      placeholderValues: [""],
      maxDropdownItems: 6,
      forceDropup: true,
      allowClear: true,
    });
    w._editModalNativeEnhanced = true;
  };

  E.ensureEditarHorarioComboBusquedaEnhance = function () {
    if (typeof ComboboxComponent === "undefined" || typeof ComboboxComponent.enhance !== "function") return;
    ["#editFicha", "#editInstructor", "#editCompetencia"].forEach((selector) => {
      const el = document.querySelector(selector);
      if (!el || el.dataset.comboboxEnhanced === "1") return;
      ComboboxComponent.enhance({
        selector,
        dropdownClass: "custom-select-dropdown",
        optionClass: "custom-option",
        placeholder: "Buscar...",
        restoreValueOnBlurWhenEmpty: true,
        forceDropup: true,
        maxDropdownItems: 6,
      });
    });
  };

  E.refreshEditarHorarioComboBusquedaUi = function () {
    ["#editFicha", "#editInstructor", "#editCompetencia"].forEach((selector) => {
      const el = document.querySelector(selector);
      if (!el) return;
      const cw = el.closest(".combobox-wrapper");
      if (cw && typeof cw._cbUpdateInput === "function") cw._cbUpdateInput();
      if (typeof ComboboxComponent !== "undefined" && typeof ComboboxComponent.setInitialValue === "function") {
        ComboboxComponent.setInitialValue(el, el.value);
      }
    });
  };

  E.setEditHorarioValidation = function (msg) {
    const el = document.getElementById("editHorarioValidation");
    if (!el) return;
    if (msg) {
      el.textContent = msg;
      el.classList.remove("hidden");
    } else {
      el.textContent = "";
      el.classList.add("hidden");
    }
  };

  E.abrirModalEditarHorario = function () {
    const modal = document.getElementById("modalEditarHorario");
    if (!modal) return;
    E.setEditHorarioValidation("");
    modal.classList.remove("hidden");
    modal.classList.add("flex", "items-center", "justify-center");
    modal.style.display = "";
    modal.style.pointerEvents = "";
    modal.style.visibility = "";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "";
    });
    document.body.classList.add("overflow-hidden");
    document.body.style.overflow = "hidden";
    if (typeof ComboboxComponent !== "undefined" && typeof ComboboxComponent.closeAll === "function") {
      ComboboxComponent.closeAll();
    }
    queueMicrotask(() => {
      if (typeof ComboboxComponent !== "undefined" && typeof ComboboxComponent.closeAll === "function") {
        ComboboxComponent.closeAll();
      }
    });
  };

  E.cerrarModalEditarHorario = function () {
    const modal = document.getElementById("modalEditarHorario");
    if (!modal) return;
    const activeEl = document.activeElement;
    if (activeEl && modal.contains(activeEl)) activeEl.blur();
    modal.classList.add("hidden");
    modal.classList.remove("flex", "block", "items-center", "justify-center");
    modal.style.display = "none";
    modal.style.pointerEvents = "none";
    modal.style.visibility = "hidden";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "none";
    });
    document.body.style.overflow = "";
    document.body.classList.remove("overflow-hidden");
  };

  E.recolectarPayloadEdicionHorario = function () {
    const idHorario = S.editarHorarioContext.idHorario;
    const id_zona_val = S.editarHorarioContext.id_zona_val;
    const id_area_val = S.editarHorarioContext.id_area_val;

    const dia = document.getElementById("editDia")?.value;
    const horaInicio = document.getElementById("editHoraInicio")?.value;
    const horaFin = document.getElementById("editHoraFin")?.value;
    const ficha = document.getElementById("editFicha")?.value;
    const idInstructor = document.getElementById("editInstructor")?.value;
    const idCompetencia = document.getElementById("editCompetencia")?.value;
    const raes = [...document.querySelectorAll("#editRAEs input:checked")].map((chk) => chk.value);

    if (!dia || !horaInicio || !horaFin || !ficha || !idInstructor || !idCompetencia || raes.length === 0) {
      return { error: "Completa todos los campos y selecciona al menos un RA." };
    }
    if (U.timeToMinutes(horaFin) <= U.timeToMinutes(horaInicio)) {
      return { error: "Hora fin debe ser posterior a hora inicio." };
    }
    if (E.hasOverlap({ dia, inicio: horaInicio, fin: horaFin, excludeId: idHorario })) {
      return { error: "Esta franja ya está ocupada en ese día." };
    }
    return {
      value: {
        id_horario: idHorario,
        dia,
        numero_ficha: ficha,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        id_instructor: idInstructor,
        id_competencia: idCompetencia,
        raes,
        id_zona: id_zona_val,
        id_area: id_area_val,
        descripcion: document.getElementById("editDescripcion")?.value || "",
      },
    };
  };

  E.aplicarEdicionHorarioEnCache = function (outValue) {
    const index = S.horariosCache.findIndex((h) => String(h.id_horario) === String(outValue.id_horario));
    if (index === -1) return false;

    const prev = S.horariosCache[index];
    const raesArray = [...document.querySelectorAll("#editRAEs input:checked")].map((chk) => {
      const span = chk.closest("label")?.querySelector("span");
      return span ? span.textContent.trim() : String(chk.value);
    });

    const inst = S.listaInstructores.find((i) => String(i.id_instructor) === String(outValue.id_instructor));
    const comp = S.listaCompetencias.find((c) => String(c.id_competencia) === String(outValue.id_competencia));
    const fich = S.listaFichas.find((f) => String(f.numero_ficha) === String(outValue.numero_ficha));

    const merged = { ...prev, ...outValue, raesArray };
    delete merged.raes;

    if (inst) {
      merged.nombre_instructor = inst.nombre_instructor;
      merged.tipo_instructor = inst.tipo_instructor;
    }
    if (comp) {
      merged.nombre_competencia = comp.nombre_competencia;
    }
    if (fich) {
      merged.nivel_ficha = fich.nivel_ficha;
      if (fich.programa_formacion != null) merged.programa_formacion = fich.programa_formacion;
      if (fich.nombre_programa != null) merged.nombre_programa = fich.nombre_programa;
    }

    S.horariosCache[index] = merged;
    return true;
  };

  E.enviarEdicionHorarioDesdeModal = async function () {
    E.setEditHorarioValidation("");
    const out = E.recolectarPayloadEdicionHorario();
    if (out.error) {
      E.setEditHorarioValidation(out.error);
      return;
    }

    const actual = E.capturarSnapshotEdicionHorario();
    if (E.snapshotsEdicionHorarioIguales(actual, S.editarHorarioContext.snapshotInicial)) {
      E.setEditHorarioValidation("No hay cambios respecto al horario actual.");
      return;
    }

    try {
      if (!E.aplicarEdicionHorarioEnCache(out.value)) {
        throw new Error("No se encontró el horario en la tabla actual.");
      }
      S.huboCambios = true;
      if (typeof RT.solicitud.detectarCambios === "function") {
        RT.solicitud.detectarCambios();
      }
      E.cerrarModalEditarHorario();
      T.fire({
        icon: "success",
        title: "Horario modificado",
      });
      RT.grid.renderizarTablaDesdeRegistros(S.horariosCache, "", { filtersApplied: true });
    } catch (e) {
      console.error("Error al editar horario:", e);
      T.fire({
        icon: "error",
        title: "Error al editar horario",
      });
    }
  };

  E.renderRAEsPopup = async function (idCompetencia, raesMarcados) {
    raesMarcados = raesMarcados || [];
    const cont = document.getElementById("editRAEs");
    if (!cont) return;
    cont.innerHTML = '<p class="text-sm text-gray-500 italic">Cargando RAEs…</p>';

    if (!idCompetencia) {
      cont.innerHTML = '<p class="text-sm text-gray-500 italic">Seleccione una competencia</p>';
      return;
    }

    const raes = await RT.data.obtenerRoesPorCompetencia(idCompetencia);

    cont.innerHTML = raes
      .map((rae) => {
        const desc = (rae.descripcion || rae.descripcion_rae || "").trim();
        const idRae = String(rae.id_rae ?? "");
        const checked = raesMarcados.some((m) => {
          const s = String(m ?? "");
          return s === `${idRae} - ${desc}` || s === idRae || s.startsWith(`${idRae} -`);
        })
          ? "checked"
          : "";
        return `
      <label class="flex items-start gap-2 mb-1 py-1.5 px-2 rounded-lg hover:bg-white/80 cursor-pointer border border-transparent hover:border-gray-200/80">
        <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-[#39A900] focus:ring-[#39A900] focus:ring-offset-0" value="${rae.id_rae}" ${checked}>
        <span class="text-sm text-gray-800 leading-snug">${rae.id_rae} - ${desc}</span>
      </label>
    `;
      })
      .join("");
  };

  E.editarTrimestralizacion = async function (reg) {
    await RT.data.cargarInstructores();
    await RT.data.cargarCompetencias();
    await RT.data.cargarFichas();

    const modal = document.getElementById("modalEditarHorario");
    if (!modal) {
      console.error("No existe #modalEditarHorario");
      return;
    }

    const dia = reg.getAttribute("data-dia") || "Sin día";
    const horaInicio = reg.getAttribute("data-hora-inicio") || "";
    const horaFin = reg.getAttribute("data-hora-fin") || "";
    const ficha = reg.getAttribute("data-ficha") || "";
    const idInstructorActual = reg.getAttribute("data-id-instructor") || "";
    const idCompetenciaActual = reg.getAttribute("data-id-competencia") || "";
    const raesActuales = JSON.parse(reg.getAttribute("data-raes") || "[]");
    const idHorario = reg.getAttribute("data-id") || "";
    const id_zona_val = document.getElementById("selectZona")?.value || S.id_zona;
    const id_area_val = document.getElementById("selectArea")?.value;
    const nombreCompetencia = reg.getAttribute("data-competencia") || "";

    S.editarHorarioContext = { idHorario, id_zona_val, id_area_val, snapshotInicial: null };

    const optionInstructors = S.listaInstructores
      .map(
        (i) =>
          `<option value="${i.id_instructor}" ${String(i.id_instructor) === String(idInstructorActual) ? "selected" : ""}>
    ${i.nombre_instructor} - ${i.tipo_instructor}
  </option>`
      )
      .join("");

    const optionCompetencias = S.listaCompetencias
      .map(
        (c) =>
          `<option value="${c.id_competencia}" ${String(c.id_competencia) === String(idCompetenciaActual) ? "selected" : ""}>
    ${c.nombre_competencia}
  </option>`
      )
      .join("");

    const optionFichas = S.listaFichas
      .map((f) => {
        const nivel = U.etiquetaNivelGrupo(f);
        return `<option value="${f.numero_ficha}" ${String(f.numero_ficha) === String(ficha) ? "selected" : ""}>
      ${f.numero_ficha} - Nivel ${nivel}
    </option>`;
      })
      .join("");

    const horas = Array.from({ length: 16 }, (_, i) => i + 6);
    const hiNorm = U.normalizarHoraParaSelectEditar(horaInicio);
    const hfNorm = U.normalizarHoraParaSelectEditar(horaFin);
    const slotsEstandar = new Set(horas.map((h) => `${String(h).padStart(2, "0")}:00`));

    let extraIni = "";
    let extraFin = "";
    if (hiNorm && !slotsEstandar.has(hiNorm)) {
      extraIni = `<option value="${hiNorm}" selected>${hiNorm}</option>`;
    }
    if (hfNorm && !slotsEstandar.has(hfNorm)) {
      extraFin = `<option value="${hfNorm}" selected>${hfNorm}</option>`;
    }

    const horaOpcionesInicio =
      extraIni +
      horas
        .map((h) => {
          const v = `${String(h).padStart(2, "0")}:00`;
          return `<option value="${v}" ${v === hiNorm ? "selected" : ""}>${v}</option>`;
        })
        .join("");
    const horaOpcionesFin =
      extraFin +
      horas
        .map((h) => {
          const v = `${String(h).padStart(2, "0")}:00`;
          return `<option value="${v}" ${v === hfNorm ? "selected" : ""}>${v}</option>`;
        })
        .join("");

    const diasSemana = ["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"];
    const selDia = document.getElementById("editDia");
    if (selDia) {
      selDia.innerHTML = diasSemana.map((d) => `<option value="${d}" ${d === dia ? "selected" : ""}>${d}</option>`).join("");
    }

    const selFicha = document.getElementById("editFicha");
    if (selFicha) {
      selFicha.innerHTML = `<option value="">Seleccione un grupo</option>${optionFichas}`;
    }
    const selHi = document.getElementById("editHoraInicio");
    if (selHi) {
      selHi.innerHTML = `<option value="">Seleccionar hora</option>${horaOpcionesInicio}`;
    }
    const selHf = document.getElementById("editHoraFin");
    if (selHf) {
      selHf.innerHTML = `<option value="">Seleccionar hora</option>${horaOpcionesFin}`;
    }
    const selIns = document.getElementById("editInstructor");
    if (selIns) {
      selIns.innerHTML = `<option value="">Seleccione un instructor</option>${optionInstructors}`;
    }
    const selComp = document.getElementById("editCompetencia");
    if (selComp) {
      selComp.innerHTML = `<option value="">Seleccione una competencia</option>${optionCompetencias}`;
    }

    const ta = document.getElementById("editDescripcion");
    if (ta) ta.value = "";

    const sub = document.getElementById("subtituloModalEditarHorario");
    if (sub) {
      const rango = horaInicio && horaFin ? `${horaInicio} – ${horaFin}` : "";
      sub.textContent = [dia, rango, nombreCompetencia].filter(Boolean).join(" · ");
    }

    E.ensureEditarHorarioComboBusquedaEnhance();
    E.refreshEditarHorarioComboBusquedaUi();
    E.refreshEditarHorarioNativeSelectsUi();

    ["editDia", "editHoraInicio", "editHoraFin"].forEach((id) => {
      const s = document.getElementById(id);
      if (s) s.dispatchEvent(new Event("change", { bubbles: true }));
    });

    E.abrirModalEditarHorario();
    E.refreshEditarHorarioNativeSelectsUi();
    await E.renderRAEsPopup(idCompetenciaActual, raesActuales);
    S.editarHorarioContext.snapshotInicial = E.capturarSnapshotEdicionHorario();
  };

  Object.assign(RT.edit, E);
})(window);
