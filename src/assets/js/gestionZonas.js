(() => {
  // =======================
  // CONFIGURACIÓN GLOBAL
  // =======================
  const API_URL = (typeof window !== "undefined" && window.API_URL)
    ? window.API_URL
    : "../controllers/ZonaController.php";

  // =======================
  // ELEMENTOS DEL DOM
  // =======================
  const modal = document.getElementById("modalZonas");
  const formZona = document.getElementById("formNuevaZona");
  const openBtn = document.getElementById("btnAbrirModalZonas");
  const closeBtn = document.getElementById("btnCerrarModalZonas");
  const cancelBtn = document.getElementById("btnCancelarModalZonas");
  const panel = document.getElementById("modalPanel");
  const backdrop = document.getElementById("modalBackdrop");

  // ⚠️ Mantengo tu selector existente:
  const tabla = document.querySelector("#tablaInstructores");
  const tablaBody = document.querySelector("#tablaInstructores tbody");
  const inputZona = document.getElementById("id_zona");

  // Wrapper para scroll interno (debe existir en el HTML)
  const wrapTabla = document.getElementById("wrapTablaZonas") || document.getElementById("wrapTabla");

  // =======================
  // CONFIGURACIÓN TOAST
  // =======================
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: "#fff",
    color: "#333",
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
  });

  // =======================
  // FUNCIONES MODAL
  // =======================
  const openModal = () => {
    modal?.classList.remove("hidden");
    requestAnimationFrame(() => {
      panel?.classList.add("opacity-100", "scale-100", "translate-y-0");
      backdrop?.classList.add("opacity-100");
    });
  };

  const closeModal = () => {
    panel?.classList.remove("opacity-100", "scale-100", "translate-y-0");
    backdrop?.classList.remove("opacity-100");
    setTimeout(() => modal?.classList.add("hidden"), 200);
    formZona?.reset();
  };

  openBtn?.addEventListener("click", openModal);
  closeBtn?.addEventListener("click", closeModal);
  cancelBtn?.addEventListener("click", closeModal);
  backdrop?.addEventListener("click", (e) => {
    if (e.target === backdrop) closeModal();
  });

  // =======================
  // VALIDAR CAMPO DE ZONA
  // =======================
  inputZona?.addEventListener("input", (e) => {
    let val = e.target.value;
    val = val.replace(/[^0-9]/g, "");
    if (val.length > 1 && val.startsWith("0")) val = val.replace(/^0+/, "");
    if (val.length > 4) val = val.slice(0, 4);
    e.target.value = val;
  });

  // =======================
  // SCROLL INTERNO (5 filas)
  // =======================
  function ajustarAltoTablaZonas() {
    if (!wrapTabla || !tabla) return;

    const thead = tabla.querySelector("thead");
    const firstRow = tabla.querySelector("tbody tr");
    const filas = tabla.querySelectorAll("tbody tr").length;

    // Alturas de respaldo
    const headH = thead ? thead.getBoundingClientRect().height : 44;
    const rowH  = firstRow ? firstRow.getBoundingClientRect().height : 56;

    const maxFilas = 5;
    const maxH = headH + rowH * maxFilas;

    wrapTabla.style.maxHeight = `${Math.ceil(maxH)}px`;
    wrapTabla.style.overflowY = filas > maxFilas ? "auto" : "visible";
    wrapTabla.style.overscrollBehavior = "contain";
  }
  window.addEventListener("resize", ajustarAltoTablaZonas);

  // =======================
  // CARGAR ÁREAS
  // =======================
  async function cargarAreas() {
    const selectArea = document.getElementById("id_area");
    if (!selectArea) return;
    selectArea.innerHTML = `<option disabled selected value="">Cargando áreas...</option>`;

    try {
      const res = await fetch("src/controllers/areaController.php?accion=listar");
      const json = await res.json();

      if (json.status === "success" && Array.isArray(json.data) && json.data.length > 0) {
        selectArea.innerHTML = `<option disabled selected value="">Seleccione un Área</option>`;
        json.data.forEach((area) => {
          const option = document.createElement("option");
          option.value = area.id_area;
          option.textContent = area.nombre_area;
          selectArea.appendChild(option);
        });
      } else {
        selectArea.innerHTML = `<option disabled selected value="">No hay áreas disponibles</option>`;
      }
    } catch (err) {
      console.error("Error al cargar áreas:", err);
      selectArea.innerHTML = `<option disabled selected value="">Error al cargar áreas</option>`;
      Toast.fire({ icon: "error", title: "Error al cargar las áreas." });
    }
  }

  // =======================
  // CARGAR ZONAS
  // =======================
  async function cargarZonas() {
    if (!tablaBody) return;
    tablaBody.innerHTML = `<tr><td colspan="3" class="p-4 text-gray-500 text-center">Cargando zonas...</td></tr>`;
    try {
      const res = await fetch(`${API_URL}?accion=listar`);
      const json = await res.json();

      if (json.status === "success") {
        if (!Array.isArray(json.data) || json.data.length === 0) {
          tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-gray-500">No hay zonas registradas</td></tr>`;
          ajustarAltoTablaZonas();
          return;
        }

        tablaBody.innerHTML = json.data
          .map((z) => {
            // Pill EXACTO al de gestionarInstructor.js (sin borde)
            const pill = `
              <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
                ${z.nombre_area || "—"}
              </span>`.trim();

            return `
              <tr data-id="${z.id_zona}" data-id-area="${z.id_area ?? ""}" class="border-b">
                <td class="px-6 py-4">${z.id_zona}</td>
                <td class="px-6 py-4 text-center">
                  ${pill}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end items-center gap-3">
                    <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" title="Editar">
                      <img class="w-5 h-5" src="src/assets/img/pencil-line.svg" alt="Editar" />
                    </button>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" class="sr-only peer" ${Number(z.estado) === 1 ? "checked" : ""}>
                      <div class="w-11 h-6 bg-gray-200 rounded-full transition peer-checked:bg-[#39A900]"></div>
                      <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                    </label>
                  </div>
                </td>
              </tr>`;
          })
          .join("");

        ajustarAltoTablaZonas();
      } else {
        tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">${json.message || "Error al listar"}</td></tr>`;
        ajustarAltoTablaZonas();
        Toast.fire({ icon: "error", title: json.message || "Error al listar zonas" });
      }
    } catch (err) {
      console.error("Error al cargar zonas:", err);
      tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Error al cargar zonas</td></tr>`;
      ajustarAltoTablaZonas();
      Toast.fire({ icon: "error", title: "Error al cargar zonas." });
    }
  }

  // =======================
  // CREAR ZONA
  // =======================
  formZona?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const id_zona = formZona.id_zona?.value?.trim();
    const id_area = formZona.id_area?.value?.trim();

    if (!id_zona || !id_area) {
      Toast.fire({ icon: "warning", title: "Debes ingresar número de zona y seleccionar un área." });
      return;
    }
    if (isNaN(id_zona) || parseInt(id_zona) <= 0) {
      Toast.fire({ icon: "warning", title: "El número de zona debe ser un entero positivo." });
      return;
    }

    const fd = new FormData();
    fd.append("accion", "crear");
    fd.append("id_zona", id_zona);
    fd.append("id_area", id_area);

    try {
      const res = await fetch(API_URL, { method: "POST", body: fd });
      const json = await res.json();

      if (json.status === "success") {
        Toast.fire({ icon: "success", title: "Zona creada correctamente" });
        closeModal();
        await cargarZonas();
        ajustarAltoTablaZonas();
      } else {
        Toast.fire({ icon: "error", title: json.message || "No se pudo crear la zona." });
      }
    } catch (err) {
      console.error("Error al crear zona:", err);
      Toast.fire({ icon: "error", title: "Error al crear zona." });
    }
  });

  // =======================
  // CAMBIAR ESTADO
  // =======================
  tablaBody?.addEventListener("change", async (e) => {
    const chk = e.target.closest("input[type=checkbox]");
    if (!chk) return;
    const tr = chk.closest("tr");
    const id_zona = tr?.dataset?.id;
    const id_area = tr?.dataset?.idArea;
    const nuevoEstado = chk.checked ? 1 : 0;

    if (!id_zona || !id_area) {
      Toast.fire({ icon: "error", title: "No se pudo identificar la zona/área." });
      return;
    }

    const fd = new FormData();
    fd.append("accion", "cambiar_estado");
    fd.append("id_zona", id_zona);
    fd.append("id_area", id_area);
    fd.append("estado", String(nuevoEstado));

    try {
      const res = await fetch(API_URL, { method: "POST", body: fd });
      const json = await res.json();
      Toast.fire({
        icon: json.status === "success" ? "success" : "error",
        title: json.message || (json.status === "success" ? "Estado actualizado" : "No se pudo actualizar"),
      });
    } catch (err) {
      console.error("Error al cambiar estado:", err);
      Toast.fire({ icon: "error", title: "Error al cambiar el estado." });
    }
  });

  // =======================
  // EDITAR ZONA INLINE
  // =======================
  tablaBody?.addEventListener("click", async (e) => {
    const btnEditar = e.target.closest(".btn-editar");
    if (!btnEditar) return;

    const tr = btnEditar.closest("tr");
    const id_zona_actual = tr?.dataset?.id;
    const id_area_actual = tr?.dataset?.idArea;

    const tdZona = tr.children[0];
    const tdArea = tr.children[1];
    const tdAcc = tr.children[2];

    const zonaOriginal = tdZona.textContent.trim();
    const areaOriginal = tdArea.textContent.trim();

    // 🔹 Cargar áreas dinámicamente desde la DB
    let opcionesHTML = `<option disabled selected value="">Cargando áreas...</option>`;
    try {
      const res = await fetch("src/controllers/areaController.php?accion=listar");
      const json = await res.json();

      if (json.status === "success" && Array.isArray(json.data)) {
        opcionesHTML = json.data
          .map(
            (a) =>
              `<option value="${a.id_area}" ${a.nombre_area === areaOriginal ? "selected" : ""}>${a.nombre_area}</option>`
          )
          .join("");
      } else {
        opcionesHTML = `<option disabled selected value="">No hay áreas</option>`;
      }
    } catch (err) {
      console.error("Error al cargar áreas:", err);
      opcionesHTML = `<option disabled selected value="">Error al cargar</option>`;
    }

    // 🔹 Reemplazar contenido de la fila (estilos sobrios como en gestionarInstructor.js)
    tdZona.innerHTML = `<input type="number" value="${zonaOriginal}" class="w-20 rounded-lg border border-gray-200 px-3 py-2 text-center focus:outline-none focus:border-gray-300">`;
    tdArea.innerHTML = `
      <div class="relative max-w-[220px] mx-auto">
        <select class="w-full appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 focus:outline-none focus:border-gray-300">
          ${opcionesHTML}
        </select>
        <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
        </svg>
      </div>`;
    tdAcc.innerHTML = `
      <button class="btn-guardar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-green-600 text-green-600 hover:bg-green-50 transition">Guardar</button>
      <button class="btn-cancelar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</button>
    `;

    tdAcc.querySelector(".btn-cancelar").addEventListener("click", async () => {
      await cargarZonas();
      ajustarAltoTablaZonas();
    });

    tdAcc.querySelector(".btn-guardar").addEventListener("click", async () => {
      const id_zona_nueva = tdZona.querySelector("input").value.trim();
      const id_area_nueva = tdArea.querySelector("select").value.trim();

      if (!id_zona_nueva || !id_area_nueva) {
        Toast.fire({ icon: "warning", title: "Debes completar todos los campos antes de guardar." });
        return;
      }

      const fd = new FormData();
      fd.append("accion", "actualizar");
      fd.append("id_zona_actual", id_zona_actual);
      fd.append("id_area_actual", id_area_actual);
      fd.append("id_zona_nueva", id_zona_nueva);
      fd.append("id_area_nueva", id_area_nueva);

      try {
        const res = await fetch(API_URL, { method: "POST", body: fd });
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); }
        catch {
          console.error("Respuesta no JSON:", text);
          Toast.fire({ icon: "error", title: "Error interno al actualizar." });
          return;
        }

        if (json.status === "success") {
          Toast.fire({ icon: "success", title: "Zona actualizada correctamente." });
          await cargarZonas();
          ajustarAltoTablaZonas();
        } else {
          Toast.fire({ icon: "error", title: json.message || "No se pudo actualizar" });
        }
      } catch (err) {
        console.error("Error al actualizar zona:", err);
        Toast.fire({ icon: "error", title: "Error al actualizar zona." });
      }
    });
  });

  // =======================
  // INICIALIZAR
  // =======================
  cargarAreas();
  cargarZonas();
  ajustarAltoTablaZonas(); // por si ya hay filas renderizadas del servidor
})();
