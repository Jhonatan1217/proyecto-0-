(() => {
  console.log("✅ gestionZonas.js cargado correctamente");

  const BASE_URL = "../../src/controllers/zonaController.php";
  const modal = document.getElementById("modalZonas");
  const formZona = document.getElementById("formNuevaZona");
  const openBtn = document.getElementById("btnAbrirModalZonas");
  const closeBtn = document.getElementById("btnCerrarModalZonas");
  const cancelBtn = document.getElementById("btnCancelarModalZonas");
  const panel = document.getElementById("modalPanel");
  const backdrop = document.getElementById("modalBackdrop");
  const tablaBody = document.querySelector("#tablaInstructores tbody");

  // -------------------- Funciones Modal --------------------
  const openModal = () => {
    modal.classList.remove("hidden");
    requestAnimationFrame(() => {
      panel.classList.add("opacity-100", "scale-100", "translate-y-0");
      backdrop.classList.add("opacity-100");
    });
  };

  const closeModal = () => {
    panel.classList.remove("opacity-100", "scale-100", "translate-y-0");
    backdrop.classList.remove("opacity-100");
    setTimeout(() => modal.classList.add("hidden"), 200);
    formZona.reset();
  };

  openBtn?.addEventListener("click", openModal);
  closeBtn?.addEventListener("click", closeModal);
  cancelBtn?.addEventListener("click", closeModal);
  backdrop?.addEventListener("click", (e) => { if (e.target === backdrop) closeModal(); });

// -------------------- Cargar Áreas dinámicamente --------------------
async function cargarAreas() {
  const selectArea = document.getElementById("id_area");
  selectArea.innerHTML = `<option disabled selected value="">Cargando áreas...</option>`;

  try {
    const res = await fetch("../../src/controllers/areaController.php?accion=listar");
    const json = await res.json();

    if (Array.isArray(json) && json.length > 0) {
      selectArea.innerHTML = `<option disabled selected value="">Seleccione un Área</option>`;
      json.forEach(area => {
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
  }
}

// Llamar al cargar la página
cargarAreas();



  // -------------------- Cargar Zonas --------------------
  async function cargarZonas() {
    tablaBody.innerHTML = `<tr><td colspan="3" class="p-4 text-gray-500 text-center">Cargando zonas...</td></tr>`;
    try {
      const res = await fetch(`${BASE_URL}?accion=listar`);
      const json = await res.json();

      if (json.status === "success") {
        if (json.data.length === 0) {
          tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-gray-500">No hay zonas registradas</td></tr>`;
          return;
        }

        tablaBody.innerHTML = json.data.map(z => `
          <tr data-id="${z.id_zona}" class="border-b">
            <td class="px-6 py-4">${z.id_zona}</td>
            <td class="px-6 py-4 text-center">
              <span class="bg-${z.nombre_area === 'Confecciones' ? 'blue' : 'green'}-600 text-white text-xs px-3 py-1 rounded-full">
                ${z.nombre_area || '—'}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end items-center gap-3">
                <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" title="Editar">
                  <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
                </button>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" ${z.estado == 1 ? "checked" : ""}>
                  <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                  <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                </label>
              </div>
            </td>
          </tr>
        `).join("");
      } else {
        tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">${json.message}</td></tr>`;
      }
    } catch (err) {
      console.error("Error al cargar zonas:", err);
      tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Error al cargar zonas</td></tr>`;
    }
  }

  cargarZonas();

  // -------------------- Crear Zona --------------------
  formZona?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const id_zona = formZona.id_zona?.value?.trim();
    const id_area = formZona.id_area?.value?.trim();

    if (!id_zona || !id_area) {
      alert("⚠️ Debes ingresar número de zona y seleccionar un área");
      return;
    }

    console.log("🟢 Enviando creación:", { accion: "crear", id_zona, id_area });

    try {
      const res = await fetch(BASE_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ accion: "crear", id_zona, id_area }),
      });

      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch {
        console.error("⚠️ Respuesta no JSON:", text);
        alert("Error interno en el servidor al crear zona");
        return;
      }

      console.log("🟣 Respuesta crear:", json);

      if (json.status === "success") {
        alert("✅ Zona creada correctamente");
        closeModal();
        cargarZonas();
      } else {
        alert("❌ " + json.message);
      }
    } catch (err) {
      console.error("Error al crear zona:", err);
      alert("Error al crear zona");
    }
  });

  // -------------------- Cambiar Estado --------------------
  tablaBody.addEventListener("change", async (e) => {
    const chk = e.target.closest("input[type=checkbox]");
    if (!chk) return;
    const tr = chk.closest("tr");
    const id_zona = tr.dataset.id;
    const nuevoEstado = chk.checked ? 1 : 0;

    try {
      const res = await fetch(BASE_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ accion: "cambiarEstado", id_zona, estado: nuevoEstado }),
      });
      const json = await res.json();
      console.log("🟢 Estado actualizado:", json);
    } catch (err) {
      console.error("Error al cambiar estado:", err);
    }
  });

  // -------------------- Editar Zona Inline --------------------
  tablaBody.addEventListener("click", (e) => {
    const btnEditar = e.target.closest(".btn-editar");
    if (!btnEditar) return;

    const tr = btnEditar.closest("tr");
    const id_zona_actual = tr.dataset.id;
    const tdZona = tr.children[0];
    const tdArea = tr.children[1];
    const tdAcc = tr.children[2];

    const zonaOriginal = tdZona.textContent.trim();
    const areaOriginal = tdArea.textContent.trim();

    // Editar inline
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

    // Cancelar edición
    tdAcc.querySelector(".btn-cancelar").addEventListener("click", () => {
      tdZona.textContent = zonaOriginal;
      tdArea.innerHTML = `<span class="bg-${areaOriginal === "Confecciones" ? "blue" : "green"}-600 text-white text-xs px-3 py-1 rounded-full">${areaOriginal}</span>`;
      tdAcc.innerHTML = `
        <div class="flex justify-end items-center gap-3">
          <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" title="Editar">
            <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
          </button>
        </div>
      `;
    });

    // Guardar cambios
    tdAcc.querySelector(".btn-guardar").addEventListener("click", async () => {
      const id_zona_nueva = tdZona.querySelector("input").value.trim();
      const id_area = tdArea.querySelector("select").value.trim();

      if (!id_zona_nueva || !id_area) {
        alert("⚠️ Debes completar todos los campos antes de guardar");
        return;
      }

      console.log("🟢 Enviando actualización:", { id_zona_actual, id_zona_nueva, id_area });

      try {
        const res = await fetch(BASE_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            accion: "actualizar",
            id_zona_actual,
            id_zona_nueva,
            id_area,
          }),
        });

        const text = await res.text();
        let json;
        try {
          json = JSON.parse(text);
        } catch {
          console.error("⚠️ Respuesta no JSON:", text);
          alert("Error interno en el servidor al actualizar");
          return;
        }

        console.log("🟣 Respuesta actualizar:", json);

        if (json.status === "success") {
          alert("✅ Zona actualizada correctamente");
          cargarZonas();
        } else {
          alert("❌ " + json.message);
        }
      } catch (err) {
        console.error("Error al actualizar zona:", err);
      }
    });
  });
})();  