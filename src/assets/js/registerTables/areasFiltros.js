/**
 * Empty state, tabla visible/oculta, carga de áreas/zonas y filtros de cabecera.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const T = RT.Toast;
  const U = RT.util;

  RT.ui.setEmptyStateCopy = function (emptyMode) {
    const title = document.getElementById("empty-state-title");
    const desc = document.getElementById("empty-state-desc");
    if (!title || !desc) return;
    if (emptyMode === "filtered-empty") {
      title.textContent = RT.EMPTY_STATE_FILTERED_TITLE;
      title.classList.remove("text-gray-700");
      title.classList.add(RT.EMPTY_STATE_FILTERED_TITLE_COLOR_CLASS || "text-red-600");
      desc.textContent = RT.EMPTY_STATE_FILTERED_DESC;
    } else {
      title.textContent = RT.EMPTY_STATE_DEFAULT_TITLE;
      title.classList.remove(RT.EMPTY_STATE_FILTERED_TITLE_COLOR_CLASS || "text-red-600");
      title.classList.add("text-gray-700");
      desc.textContent = RT.EMPTY_STATE_DEFAULT_DESC;
    }
  };

  /** @param {boolean} mostrar @param {'default'|'filtered-empty'} [emptyMode] */
  RT.ui.toggleTabla = function (mostrar = true, emptyMode = "default") {
    const tabla = document.querySelector("#tabla-horarios");
    const botones = document.querySelector("#botones-principales");
    const emptyState = document.querySelector("#empty-state");
    if (tabla) tabla.classList.toggle("hidden", !mostrar);
    if (botones) botones.classList.toggle("hidden", !mostrar);
    if (emptyState) {
      emptyState.classList.toggle("hidden", mostrar);
      if (!mostrar) RT.ui.setEmptyStateCopy(emptyMode);
    }
  };

  /** Botón X en comboboxes locales (.custom-combobox); mismo patrón que registerTablesModal.js */
  RT.ui.attachCustomComboboxClear = function (input, panel, onClear) {
    const host = input.closest(".custom-combobox");
    if (!host || host.querySelector(".btn-clear-custom-combobox")) return;

    const fieldRow = document.createElement("div");
    fieldRow.className = "custom-combobox-field";
    host.insertBefore(fieldRow, input);
    fieldRow.appendChild(input);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "btn-clear-custom-combobox";
    btn.setAttribute("aria-label", "Limpiar");
    btn.innerHTML =
      '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    fieldRow.appendChild(btn);

    function updateClearBtn() {
      const show = !input.disabled && input.value.trim().length > 0;
      btn.classList.toggle("visible", show);
    }

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (panel) panel.classList.add("hidden");
      input.value = "";
      if (typeof onClear === "function") onClear();
      else {
        input.dispatchEvent(new Event("input", { bubbles: true }));
        input.dispatchEvent(new Event("change", { bubbles: true }));
      }
      updateClearBtn();
      if (!input.disabled) {
        queueMicrotask(() => {
          input.focus({ preventScroll: true });
        });
      }
    });

    input.addEventListener("input", updateClearBtn);
    input.addEventListener("change", updateClearBtn);
    updateClearBtn();
  };

  RT.ui.configurarComboboxArea = function () {
    const inputArea = document.getElementById("inputAreaTexto");
    const selectArea = document.getElementById("selectArea");
    const panelArea = document.getElementById("panelAreaFiltro");
    const listaArea = panelArea?.querySelector(".custom-combobox-list");

    if (!inputArea || !selectArea || !panelArea || !listaArea) return;

    RT.ui.attachCustomComboboxClear(inputArea, panelArea, () => {
      selectArea.value = "";
      selectArea.dispatchEvent(new Event("change", { bubbles: true }));
    });

    function actualizarPanelArea() {
      const valor = inputArea.value.trim().toLowerCase();
      const opciones = Array.from(selectArea.options).filter((opt) => opt.value && opt.value !== "");

      listaArea.innerHTML = "";

      const filtradas = opciones.filter((opt) => opt.textContent.toLowerCase().includes(valor));

      if (filtradas.length === 0) {
        listaArea.innerHTML = '<div class="custom-combobox-option text-gray-400 p-3">Sin resultados</div>';
        panelArea.classList.remove("hidden");
        return;
      }

      filtradas.forEach((opt) => {
        const div = document.createElement("div");
        div.className = "custom-combobox-option p-3 cursor-pointer hover:bg-green-50 text-gray-700";
        div.textContent = opt.textContent;
        div.addEventListener("click", () => {
          selectArea.value = opt.value;
          inputArea.value = opt.textContent;
          panelArea.classList.add("hidden");
          selectArea.dispatchEvent(new Event("change", { bubbles: true }));
        });
        listaArea.appendChild(div);
      });

      panelArea.classList.remove("hidden");
    }

    inputArea.addEventListener("input", actualizarPanelArea);
    inputArea.addEventListener("focus", actualizarPanelArea);

    const hostArea = inputArea.closest(".custom-combobox");
    document.addEventListener(
      "click",
      (e) => {
        if (hostArea && !hostArea.contains(e.target)) {
          panelArea.classList.add("hidden");
        }
      },
      true
    );
  };

  RT.ui.configurarComboboxZona = function () {
    const inputZona = document.getElementById("inputZonaTexto");
    const selectZona = document.getElementById("selectZona");
    const panelZona = document.getElementById("panelZonaFiltro");
    const listaZona = panelZona?.querySelector(".custom-combobox-list");

    if (!inputZona || !selectZona || !panelZona || !listaZona) return;

    RT.ui.attachCustomComboboxClear(inputZona, panelZona, () => {
      selectZona.value = "";
      selectZona.dispatchEvent(new Event("change", { bubbles: true }));
    });

    function actualizarPanelZona() {
      const valor = inputZona.value.trim().toLowerCase();
      const opciones = Array.from(selectZona.options).filter((opt) => opt.value && opt.value !== "");

      listaZona.innerHTML = "";

      const filtradas = opciones.filter((opt) => opt.textContent.toLowerCase().includes(valor));

      if (filtradas.length === 0) {
        listaZona.innerHTML = '<div class="custom-combobox-option text-gray-400 p-3">Sin resultados</div>';
        panelZona.classList.remove("hidden");
        return;
      }

      filtradas.forEach((opt) => {
        const div = document.createElement("div");
        div.className = "custom-combobox-option p-3 cursor-pointer hover:bg-green-50 text-gray-700";
        div.textContent = opt.textContent;
        div.addEventListener("click", () => {
          selectZona.value = opt.value;
          inputZona.value = opt.textContent;
          panelZona.classList.add("hidden");
          selectZona.dispatchEvent(new Event("change", { bubbles: true }));
        });
        listaZona.appendChild(div);
      });

      panelZona.classList.remove("hidden");
    }

    inputZona.addEventListener("input", actualizarPanelZona);
    inputZona.addEventListener("focus", actualizarPanelZona);

    const hostZona = inputZona.closest(".custom-combobox");
    document.addEventListener(
      "click",
      (e) => {
        if (hostZona && !hostZona.contains(e.target)) {
          panelZona.classList.add("hidden");
        }
      },
      true
    );
  };

  /**
   * Área y zona solo usables con modalidad "presencial"; zona además requiere área.
   */
  RT.ui.sincronizarHabilitacionFiltrosAreaZona = function () {
    const selectModalidad = document.getElementById("selectModalidad") || document.getElementById("selectModalidadFiltro");
    const sa = document.getElementById("selectArea");
    const ia = document.getElementById("inputAreaTexto");
    const sz = document.getElementById("selectZona");
    const iz = document.getElementById("inputZonaTexto");
    if (!selectModalidad) return;

    const v = String(selectModalidad.value || "").trim().toLowerCase();
    const mod = v === "mixta" ? "mixto" : v;
    const presencial = mod === "presencial";
    const tieneArea = Boolean(sa && String(sa.value || "").trim());

    if (ia) ia.disabled = !presencial;
    if (sa) sa.disabled = !presencial;
    if (iz) iz.disabled = !presencial || !tieneArea;
    if (sz) {
      if (!presencial || !tieneArea) sz.disabled = true;
    }
  };

  RT.ui.configurarFiltros = function () {
    const selectModalidad = document.getElementById("selectModalidad") || document.getElementById("selectModalidadFiltro");
    const contenedorArea = document.getElementById("contenedorAreaFiltro") || document.getElementById("selectArea")?.parentElement;
    const contenedorZona = document.getElementById("contenedorZonaFiltro") || document.getElementById("selectZona")?.parentElement;
    const contenedorGrupo = document.getElementById("contenedorGrupoFiltro");
    const inputGrupo = document.getElementById("inputGrupoTexto");

    if (!selectModalidad) return;

    const normalizarModalidad = (valor) => {
      const v = String(valor || "").trim().toLowerCase();
      if (v === "mixta") return "mixto";
      return v;
    };

    if (inputGrupo) {
      const recargarPorGrupoVirtualMixto = () => {
        if (S.syncFiltrosDesdePreview) return;
        const modalidadActual = normalizarModalidad(selectModalidad.value);
        if (modalidadActual !== "virtual" && modalidadActual !== "mixto") return;

        const valor = inputGrupo.value.trim();
        if (!valor) {
          RT.ui.toggleTabla(false);
          return;
        }
        RT.data.cargarTrimestralizacionPorGrupo(valor);
      };
      inputGrupo.addEventListener("input", recargarPorGrupoVirtualMixto);
      inputGrupo.addEventListener("change", recargarPorGrupoVirtualMixto);
    }

    selectModalidad.addEventListener("change", () => {
      // Si el cambio fue provocado programáticamente desde la sincronización
      // de filtros de preview, no recargar datos del servidor.
      if (S.syncFiltrosDesdePreview) return;

      if (S.cascadaFiltrosPresencialActiva) {
        const inputGrupoEl = document.getElementById("inputGrupoTexto");
        if (inputGrupoEl) inputGrupoEl.value = "";
        const sa = document.getElementById("selectArea");
        const ia = document.getElementById("inputAreaTexto");
        if (ia) {
          ia.value = "";
          ia.dispatchEvent(new Event("change", { bubbles: true }));
        }
        if (sa) {
          sa.value = "";
          sa.dispatchEvent(new Event("change", { bubbles: true }));
        }
      }

      const modalidad = normalizarModalidad(selectModalidad.value);
      if (modalidad === "presencial") {
        if (contenedorArea) contenedorArea.classList.remove("hidden");
        if (contenedorZona) contenedorZona.classList.remove("hidden");
        if (contenedorGrupo) contenedorGrupo.classList.add("hidden");

        if (S.id_zona && document.getElementById("selectArea")?.value) {
          RT.data.cargarTrimestralizacion();
        } else {
          RT.ui.toggleTabla(false);
        }
      } else if (modalidad === "virtual" || modalidad === "mixto") {
        if (contenedorArea) contenedorArea.classList.add("hidden");
        if (contenedorZona) contenedorZona.classList.add("hidden");
        if (contenedorGrupo) contenedorGrupo.classList.remove("hidden");

        RT.ui.toggleTabla(false);

        const valor = inputGrupo?.value.trim();
        if (valor) {
          RT.data.cargarTrimestralizacionPorGrupo(valor);
        }
      } else {
        if (contenedorArea) contenedorArea.classList.remove("hidden");
        if (contenedorZona) contenedorZona.classList.remove("hidden");
        if (contenedorGrupo) contenedorGrupo.classList.add("hidden");
        RT.ui.toggleTabla(false);
      }

      RT.ui.sincronizarHabilitacionFiltrosAreaZona();
    });

    const modalidadInicial = RT.filtrosIniciales.modalidad === "mixta" ? "mixto" : RT.filtrosIniciales.modalidad;
    if (modalidadInicial) {
      selectModalidad.value = modalidadInicial;
      selectModalidad.dispatchEvent(new Event("change", { bubbles: true }));

      if (
        (modalidadInicial === "virtual" || modalidadInicial === "mixto") &&
        inputGrupo &&
        RT.filtrosIniciales.numero_ficha
      ) {
        inputGrupo.value = RT.filtrosIniciales.numero_ficha;
        RT.data.cargarTrimestralizacionPorGrupo(RT.filtrosIniciales.numero_ficha);
      }
    }

    RT.ui.sincronizarHabilitacionFiltrosAreaZona();
    S.cascadaFiltrosPresencialActiva = true;
  };

  RT.ui.cargarAreasYZonas = async function () {
    const selectArea = document.getElementById("selectArea");
    const selectZona = document.getElementById("selectZona");

    if (!selectArea || !selectZona) return;
    RT.ui.toggleTabla(false);

    selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
    selectZona.disabled = true;

    try {
      const resAreas = await fetch(`${RT.API_BASE}src/controllers/AreaController.php?accion=listar`);
      const dataAreas = await resAreas.json();

      if (dataAreas.status === "success" && Array.isArray(dataAreas.data)) {
        selectArea.innerHTML = `<option value="" hidden selected>SELECCIONE EL ÁREA</option>`;
        let contadorActivas = 0;

        dataAreas.data.forEach((a) => {
          if (!U.registroActivo(a.estado)) return;
          contadorActivas++;
          const opt = document.createElement("option");
          opt.value = a.id_area;
          opt.textContent = a.nombre_area;
          selectArea.appendChild(opt);
        });

        if (contadorActivas === 0) {
          selectArea.innerHTML = `
          <option value="" hidden selected>SELECCIONE EL ÁREA</option>
          <option value="" disabled>Sin datos disponibles</option>
        `;

          selectZona.innerHTML = `
          <option value="" hidden selected>SELECCIONE LA ZONA</option>
          <option value="" disabled>Sin datos disponibles</option>
        `;

          T.fire({ icon: "info", title: "No hay áreas disponibles" });
          return;
        }
      } else {
        T.fire({ icon: "warning", title: "No se encontraron áreas" });
      }

      async function cargarZonasPorArea(id_area, opts = {}) {
        const preselectZona = String(opts.preselectZona || "").trim();
        const silent = Boolean(opts.silent);

        selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
        if (!opts.preserveTabla) {
          RT.ui.toggleTabla(false);
        }

        if (!id_area) {
          selectZona.disabled = true;
          return false;
        }

        selectZona.disabled = false;

        try {
          let zonasArea = [];

          const resZonasArea = await fetch(
            `${RT.API_BASE}src/controllers/ZonaController.php?accion=listarPorArea&id_area=${id_area}`
          );

          if (resZonasArea.ok) {
            const dataZonasArea = await resZonasArea.json();
            if (dataZonasArea.status === "success" && Array.isArray(dataZonasArea.data)) {
              zonasArea = dataZonasArea.data;
            }
          }

          if (!zonasArea.length) {
            const resZonasAll = await fetch(`${RT.API_BASE}src/controllers/ZonaController.php?accion=listar`);
            if (resZonasAll.ok) {
              const dataZonasAll = await resZonasAll.json();
              const arrayZonas = Array.isArray(dataZonasAll?.data) ? dataZonasAll.data : [];
              zonasArea = arrayZonas.filter((z) => String(z.id_area ?? "").trim() === String(id_area).trim());
            }
          }

          const zonasActivas = zonasArea.filter((z) => U.registroActivo(z.estado));

          if (!zonasActivas.length) {
            selectZona.innerHTML = `
            <option value="" hidden selected>SELECCIONE LA ZONA</option>
            <option value="" disabled>Sin datos disponibles</option>
          `;
            if (!silent)
              T.fire({
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
              S.id_zona = preselectZona;
            }
          }

          if (!silent)
            T.fire({
              icon: "success",
              title: "Zonas activas cargadas correctamente",
            });
          return true;
        } catch (err) {
          console.error("Error al cargar zonas:", err);
          if (!silent) T.fire({ icon: "error", title: "Error al cargar zonas" });
          return false;
        }
      }

      selectArea.addEventListener("change", async (e) => {
        if (S.syncFiltrosDesdePreview) return;
        const id_area = e.target.value;
        RT.ui.toggleTabla(false);
        S.id_zona = null;
        const inputZonaClear = document.getElementById("inputZonaTexto");
        if (inputZonaClear) {
          inputZonaClear.value = "";
          inputZonaClear.dispatchEvent(new Event("change", { bubbles: true }));
        }
        const inputArea = document.getElementById("inputAreaTexto");
        if (inputArea) {
          if (!id_area) {
            inputArea.value = "";
          } else {
            const areaLabel = Array.from(selectArea.options).find((opt) => opt.value === id_area)?.textContent || "";
            inputArea.value = areaLabel;
          }
          inputArea.dispatchEvent(new Event("change", { bubbles: true }));
        }
        await cargarZonasPorArea(id_area);
        RT.ui.sincronizarHabilitacionFiltrosAreaZona();
        setTimeout(() => RT.ui.configurarComboboxZona(), 100);
      });

      selectZona.addEventListener("change", (e) => {
        if (S.syncFiltrosDesdePreview) return;
        S.id_zona = e.target.value;
        const inputZona = document.getElementById("inputZonaTexto");
        if (inputZona) {
          if (!S.id_zona) {
            inputZona.value = "";
          } else {
            const zonaLabel =
              Array.from(selectZona.options).find((opt) => opt.value === S.id_zona)?.textContent || "";
            inputZona.value = zonaLabel;
          }
          inputZona.dispatchEvent(new Event("change", { bubbles: true }));
        }
        const id_area = selectArea.value;
        if (!S.id_zona || !id_area) {
          RT.ui.toggleTabla(false);
          return;
        }
        const h1 = document.querySelector("#cabecera-trimestralizacion h1");
        if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${S.id_zona}`;
        RT.data.cargarTrimestralizacion();
        T.fire({ icon: "info", title: `Zona ${S.id_zona} seleccionada` });
      });

      const modalidadInicial = RT.filtrosIniciales.modalidad === "mixta" ? "mixto" : RT.filtrosIniciales.modalidad;
      if (modalidadInicial === "presencial" && RT.filtrosIniciales.id_area) {
        const existeArea = Array.from(selectArea.options).some((opt) => String(opt.value) === RT.filtrosIniciales.id_area);
        if (existeArea) {
          selectArea.value = RT.filtrosIniciales.id_area;
          await cargarZonasPorArea(RT.filtrosIniciales.id_area, {
            preselectZona: RT.filtrosIniciales.id_zona,
            silent: true,
          });

          if (RT.filtrosIniciales.id_zona && selectZona.value === RT.filtrosIniciales.id_zona) {
            const h1 = document.querySelector("#cabecera-trimestralizacion h1");
            if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${RT.filtrosIniciales.id_zona}`;
            RT.data.cargarTrimestralizacion();
          }
        }
      }

      /**
       * Tras crear en vista previa local: refleja en cabecera modalidad / área-zona / grupo
       * (destino de la solicitud si se aprueba), sin recargar desde el servidor.
       */
      RT.ui.sincronizarFiltrosCabeceraDesdePreview = async function (reg) {
        if (!reg) return;
        S.syncFiltrosDesdePreview = true;
        try {
          const selectModalidad = document.getElementById("selectModalidad");
          const contenedorArea = document.getElementById("contenedorAreaFiltro");
          const contenedorZona = document.getElementById("contenedorZonaFiltro");
          const contenedorGrupo = document.getElementById("contenedorGrupoFiltro");
          const inputGrupo = document.getElementById("inputGrupoTexto");
          const inputArea = document.getElementById("inputAreaTexto");
          const inputZona = document.getElementById("inputZonaTexto");
          const h1 = document.querySelector("#cabecera-trimestralizacion h1");

          const mod = String(reg.modalidad || "").trim().toUpperCase();
          const esVirtual = mod === "VIRTUAL" || mod === "MIXTO" || mod === "MIXTA";

          if (esVirtual) {
            const selVal = mod === "VIRTUAL" ? "virtual" : "mixto";
            if (selectModalidad) {
              selectModalidad.value = selVal;
              const w = selectModalidad.closest(".combobox-wrapper");
              if (w && typeof w._cbUpdateInput === "function") w._cbUpdateInput();
            }
            if (contenedorArea) contenedorArea.classList.add("hidden");
            if (contenedorZona) contenedorZona.classList.add("hidden");
            if (contenedorGrupo) contenedorGrupo.classList.remove("hidden");
            if (inputGrupo) {
              inputGrupo.value = String(reg.numero_ficha || "").trim();
              inputGrupo.dispatchEvent(new Event("input", { bubbles: true }));
            }
            if (h1) {
              const nf = String(reg.numero_ficha || "").trim();
              h1.innerHTML = nf
                ? `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN — GRUPO ${nf}`
                : "VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN — VIRTUAL / MIXTA";
            }
            RT.ui.toggleTabla(true);
            RT.ui.sincronizarHabilitacionFiltrosAreaZona();
          } else {
            if (selectModalidad) {
              selectModalidad.value = "presencial";
              const w = selectModalidad.closest(".combobox-wrapper");
              if (w && typeof w._cbUpdateInput === "function") w._cbUpdateInput();
            }
            if (contenedorArea) contenedorArea.classList.remove("hidden");
            if (contenedorZona) contenedorZona.classList.remove("hidden");
            if (contenedorGrupo) contenedorGrupo.classList.add("hidden");

            const id_area = String(reg.id_area || "").trim();
            const id_zona = String(reg.id_zona || "").trim();

            if (id_area && selectArea) {
              const existeArea = Array.from(selectArea.options).some((o) => String(o.value) === id_area);
              if (existeArea) {
                selectArea.value = id_area;
                if (inputArea) {
                  const areaLabel =
                    Array.from(selectArea.options).find((o) => String(o.value) === id_area)?.textContent || "";
                  inputArea.value = String(areaLabel).trim();
                  inputArea.dispatchEvent(new Event("input", { bubbles: true }));
                }
                await cargarZonasPorArea(id_area, {
                  preselectZona: id_zona,
                  silent: true,
                  preserveTabla: true,
                });
                if (id_zona && selectZona) {
                  const existeZona = Array.from(selectZona.options).some((o) => String(o.value) === id_zona);
                  if (existeZona) {
                    selectZona.value = id_zona;
                    S.id_zona = id_zona;
                    if (inputZona) {
                      const zonaLabel =
                        Array.from(selectZona.options).find((o) => String(o.value) === id_zona)?.textContent || "";
                      inputZona.value = String(zonaLabel).trim();
                      inputZona.dispatchEvent(new Event("input", { bubbles: true }));
                    }
                  }
                }
                if (h1 && id_zona) {
                  h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}`;
                }
              }
            }
            RT.ui.toggleTabla(true);
            RT.ui.sincronizarHabilitacionFiltrosAreaZona();
          }
        } finally {
          S.syncFiltrosDesdePreview = false;
        }
      };

      RT.ui.configurarComboboxArea();
      RT.ui.configurarComboboxZona();
      RT.ui.sincronizarHabilitacionFiltrosAreaZona();
    } catch (err) {
      console.error("Error en cargarAreasYZonas:", err);
      T.fire({ icon: "error", title: "Error al conectar con el servidor" });
    }
  };

  RT.ui.cargarFiltrosSuperiores = async function () {
    const selArea = document.getElementById("selectArea");
    const selZona = document.getElementById("selectZona");

    if (!selArea || !selZona) return;

    const base = (w.BASE_URL || "").replace(/\/+$/, "/");

    try {
      const respAreas = await fetch(base + "src/controllers/AreaController.php?accion=listar");
      const dataAreas = await respAreas.json();

      selArea.innerHTML = `
          <option value="" hidden>SELECCIONE EL ÁREA</option>
      `;

      let activas = 0;
      (dataAreas?.data || []).forEach((a) => {
        if (U.registroActivo(a.estado)) {
          activas++;
          selArea.innerHTML += `
                  <option value="${a.id_area}">${a.nombre_area}</option>
              `;
        }
      });

      if (activas === 0) {
        selArea.innerHTML = `
        <option value="" hidden selected>SELECCIONE EL ÁREA</option>
        <option value="" disabled>Sin datos disponibles</option>
      `;

        selZona.innerHTML = `
        <option value="" hidden selected>SELECCIONE LA ZONA</option>
        <option value="" disabled>Sin datos disponibles</option>
      `;

        T.fire({ icon: "info", title: "No hay áreas disponibles" });
        return;
      }

      selZona.innerHTML = `
          <option value="" hidden selected>SELECCIONE LA ZONA</option>
      `;
      selZona.disabled = true;
    } catch (error) {
      console.error("❌ Error cargando áreas/zonas:", error);
    }
  };
})(window);
