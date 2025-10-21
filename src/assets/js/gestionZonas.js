(() => {
  console.log("gestionZonas.js cargado correctamente");

  const API_URL = "src/controllers/zonaController.php";

  const modal = document.getElementById("modalZonas");
  const formZona = document.getElementById("formNuevaZona");
  const openBtn = document.getElementById("btnAbrirModalZonas");
  const closeBtn = document.getElementById("btnCerrarModalZonas");
  const cancelBtn = document.getElementById("btnCancelarModalZonas");
  const panel = document.getElementById("modalPanel");
  const backdrop = document.getElementById("modalBackdrop");
  const tablaBody = document.querySelector("#tablaInstructores tbody");
  const inputZona = document.getElementById("id_zona");

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

  // -------------------- Modal --------------------
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

  // -------------------- Validación de número --------------------
  inputZona?.addEventListener("input", (e) => {
    let val = e.target.value;
    val = val.replace(/[^0-9]/g, "");
    if (val.length > 1 && val.startsWith("0")) val = val.replace(/^0+/, "");
    if (val.length > 4) val = val.slice(0, 4);
    e.target.value = val;
  });

  // -------------------- Cargar Áreas --------------------
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
        Toast.fire({ icon: "warning", title: "No hay áreas registradas." });
      }
    } catch (err) {
      console.error("Error al cargar áreas:", err);
      selectArea.innerHTML = `<option disabled selected value="">Error al cargar áreas</option>`;
      Toast.fire({ icon: "error", title: "Error al cargar las áreas." });
    }
  }

  // -------------------- Cargar Zonas --------------------
  async function cargarZonas() {
    if (!tablaBody) return;
    tablaBody.innerHTML = `<tr><td colspan="3" class="p-4 text-gray-500 text-center">Cargando zonas...</td></tr>`;
    try {
      const res = await fetch(`${API_URL}?accion=listar`);
      const json = await res.json();

      if (json.status === "success") {
        if (!Array.isArray(json.data) || json.data.length === 0) {
          tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-gray-500">No hay zonas registradas</td></tr>`;
          Toast.fire({ icon: "info", title: "No hay zonas registradas aún." });
          return;
        }

        tablaBody.innerHTML = json.data
          .map(
            (z) => `
          <tr data-id="${z.id_zona}" data-id-area="${z.id_area ?? ""}" class="border-b">
            <td class="px-6 py-4">${z.id_zona}</td>
            <td class="px-6 py-4 text-center">
              <span class="bg-${z.nombre_area === "Confecciones" ? "blue" : "green"}-600 text-white text-xs px-3 py-1 rounded-full">
                ${z.nombre_area || "—"}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end items-center gap-3">
                <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" title="Editar">
                  <img class="w-5 h-5" src="src/assets/img/pencil-line.svg" alt="Editar" />
                </button>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" ${Number(z.estado) === 1 ? "checked" : ""}>
                  <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                  <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                </label>
              </div>
            </td>
          </tr>`
          )
          .join("");
      } else {
        tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">${json.message || "Error al listar"}</td></tr>`;
        Toast.fire({ icon: "error", title: json.message || "Error al listar zonas" });
      }
    } catch (err) {
      console.error("Error al cargar zonas:", err);
      tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Error al cargar zonas</td></tr>`;
      Toast.fire({ icon: "error", title: "Error al cargar zonas." });
    }
  }

  // -------------------- Crear Zona (FormData + POST) --------------------
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
        cargarZonas();
      } else {
        Toast.fire({ icon: "error", title: json.message || "No se pudo crear la zona." });
      }
    } catch (err) {
      console.error("Error al crear zona:", err);
      Toast.fire({ icon: "error", title: "Error al crear zona." });
    }
  });

  // -------------------- Cambiar Estado (FormData + POST) --------------------
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
    fd.append("accion", "cambiar_estado"); // nombre que espera tu PHP
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

  // -------------------- Editar Zona Inline (FormData + POST) --------------------
  tablaBody?.addEventListener("click", (e) => {
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

    tdZona.innerHTML = `<input type="number" value="${zonaOriginal}" class="w-20 border rounded-lg text-center">`;
    tdArea.innerHTML = `
      <select class="border rounded-lg px-2 py-1">
        <option value="1" ${areaOriginal === "Polivalente" ? "selected" : ""}>Polivalente</option>
        <option value="2" ${areaOriginal === "Confecciones" ? "selected" : ""}>Confecciones</option>
      </select>
    `;
    tdAcc.innerHTML = `
      <button class="btn-guardar bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition">Guardar</button>
      <button class="btn-cancelar bg-gray-400 text-white px-3 py-1 rounded-lg hover:bg-gray-500 transition">Cancelar</button>
    `;

    tdAcc.querySelector(".btn-cancelar").addEventListener("click", () => {
      cargarZonas();
    });

    tdAcc.querySelector(".btn-guardar").addEventListener("click", async () => {
      const id_zona_nueva = tdZona.querySelector("input").value.trim();
      const id_area_nueva = tdArea.querySelector("select").value.trim();

      if (!id_zona_nueva || !id_area_nueva) {
        Toast.fire({ icon: "warning", title: "Debes completar todos los campos antes de guardar." });
        return;
      }

      const fd = new FormData();
      fd.append("accion", "actualizar"); // nombre que espera tu PHP
      fd.append("id_zona_actual", id_zona_actual);
      fd.append("id_area_actual", id_area_actual);
      fd.append("id_zona_nueva", id_zona_nueva);
      fd.append("id_area_nueva", id_area_nueva);

      try {
        const res = await fetch(API_URL, { method: "POST", body: fd });
        const text = await res.text();
        let json;
        try {
          json = JSON.parse(text);
        } catch {
          console.error("Respuesta no JSON:", text);
          Toast.fire({ icon: "error", title: "Error interno al actualizar." });
          return;
        }

        if (json.status === "success") {
          Toast.fire({ icon: "success", title: "Zona actualizada correctamente." });
          cargarZonas();
        } else {
          Toast.fire({ icon: "error", title: json.message || "No se pudo actualizar" });
        }
      } catch (err) {
        console.error("Error al actualizar zona:", err);
        Toast.fire({ icon: "error", title: "Error al actualizar zona." });
      }
    });
  });

  // -------------------- Inicializar --------------------
  cargarAreas();
  cargarZonas();
})();
