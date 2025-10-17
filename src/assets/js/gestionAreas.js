(() => {
  const API_URL = (typeof window !== "undefined" && window.API_URL)
    ? window.API_URL
    : "../../controllers/AreaController.php";

  const $ = (s, c = document) => c.querySelector(s);
  const modal = $("#modalArea");
  const backdrop = $("#modalBackdrop");
  const panel = $("#modalPanel");
  const btnOpen = $("#btnAbrirModalArea");
  const btnClose = $("#btnCerrarModalArea"); 
  const btnCancel = $("#btnCancelarModalArea");
  const form = $("#formNuevaArea");
  const tbody = $("#tablaAreas tbody");

  // ---------- Toast ----------
  function toast(msg, type = "success") {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: type,
        title: msg,
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true,
      });
    } else {
      alert((type === "error" ? "❌ " : type === "warning" ? "⚠ " : "✅ ") + msg);
    }
  }

  // ---------- Modal ----------
  function openModal() {
    modal.classList.remove("hidden");
    requestAnimationFrame(() => {
      backdrop.classList.remove("opacity-0");
      panel.classList.remove("opacity-0", "scale-95", "translate-y-2");
    });
  }
  function closeModal() {
    form?.reset();
    backdrop.classList.add("opacity-0");
    panel.classList.add("opacity-0", "scale-95", "translate-y-2");
    setTimeout(() => modal.classList.add("hidden"), 180);
  }
  btnOpen?.addEventListener("click", openModal);
  btnClose?.addEventListener("click", closeModal);
  btnCancel?.addEventListener("click", closeModal);
  backdrop?.addEventListener("click", (e) => { if (e.target === backdrop) closeModal(); });

  // ---------- Helpers de fetch ----------
  async function parseJsonOrThrow(res) {
    const txt = await res.text();
    try {
      return JSON.parse(txt);
    } catch {
      console.error("No JSON desde API:\n", txt);
      const status = res.status;
      const msg = status >= 400 ? `Error ${status} del servidor` : "La API no devolvió JSON.";
      throw new Error(msg);
    }
  }

  async function apiGet(params) {
    const url = `${API_URL}?${new URLSearchParams(params).toString()}`;
    const res = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
    return parseJsonOrThrow(res);
  }

  async function apiPost(accion, payload) {
    const url = `${API_URL}?accion=${encodeURIComponent(accion)}`;
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json; charset=utf-8", Accept: "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload),
    });
    return parseJsonOrThrow(res);
  }

  // ---------- Render ----------
  function renderRows(lista) {
    if (!Array.isArray(lista)) {
      tbody.innerHTML = `
        <tr>
          <td class="px-6 py-6 text-red-600 text-center" colspan="2">
            Respuesta inesperada
          </td>
        </tr>`;
      return;
    }

    if (lista.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td class="px-6 py-6 text-gray-500 text-center" colspan="2">
            No hay áreas
          </td>
        </tr>`;
      return;
    }

    tbody.innerHTML = lista.map((it) => {
      const id = it.id_area ?? "";
      const nombre = it.nombre_area ?? "";
      const activo = String(it.estado ?? 1) === "1";
      return `
        <tr class="border-b" data-id="${id}">
          <td class="px-6 py-4 align-middle">
            <span class="cell-nombre">${nombre}</span>
          </td>
          <td class="px-6 py-4 align-middle text-right">
            <div class="flex justify-end items-center gap-3">
              <button class="btn-editar p-2 border rounded-lg hover:bg-gray-50 transition" type="button" title="Editar">
                <img class="w-5 h-5" src="src/assets/img/pencil-line.svg" alt="Editar" />
              </button>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer switch-estado" ${activo ? "checked" : ""}>
                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
                <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
              </label>
            </div>
          </td>
        </tr>`;
    }).join("");
  }

  async function cargarAreas() {
    try {
      const res = await apiGet({ accion: "listar" });
      renderRows(Array.isArray(res) ? res : res?.data || []);
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `
        <tr>
          <td class="px-6 py-6 text-red-600 text-center" colspan="2">
            ${e.message}
          </td>
        </tr>`;
      toast("Error al cargar áreas", "error");
    }
  }

  // ---------- Crear ----------
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nombre = (form.querySelector("input[type=text]").value || "").trim();
    if (!nombre) {
      toast("Debe ingresar el nombre del área", "warning");
      return;
    }
    try {
      const res = await apiPost("crear", { nombre_area: nombre });
      if (res?.error) throw new Error(res.error);
      toast(res?.mensaje || "Área creada correctamente", "success");
      closeModal();
      await cargarAreas();
    } catch (e2) {
      toast(e2.message || "Error al crear", "error");
    }
  });

  // ---------- Editar ----------
  tbody?.addEventListener("click", async (e) => {
    const row = e.target.closest("tr[data-id]");
    if (!row) return;
    const id = row.getAttribute("data-id");

    const btnEditar = e.target.closest(".btn-editar");
    if (btnEditar) {
      if (row.classList.contains("editando")) return;
      row.classList.add("editando");

      const cellNombre = row.querySelector(".cell-nombre");
      const acciones = row.querySelector("td:last-child > div");
      const nombreActual = cellNombre.textContent.trim();

      // Input
      cellNombre.innerHTML = `
        <input type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2"
               value="${nombreActual}" data-edit="nombre" />`;

      // Botones
      acciones.innerHTML = `
        <button class="btn-guardar px-5 py-2 rounded-xl border border-green-600 text-green-600 hover:bg-green-50 transition">Guardar</button>
        <button class="btn-cancelar px-5 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</button>
      `;

      acciones.querySelector(".btn-cancelar").addEventListener("click", async () => {
        row.classList.remove("editando");
        await cargarAreas();
      });

      acciones.querySelector(".btn-guardar").addEventListener("click", async () => {
        const nombreNuevo = row.querySelector('input[data-edit="nombre"]').value.trim();
        if (!nombreNuevo) {
          toast("Debe ingresar nombre del área", "warning");
          return;
        }
        if (nombreNuevo === nombreActual) {
          toast("Debes modificar el campo antes de guardar", "warning");
          return;
        }
        try {
          const res = await apiPost("actualizar", { id_area: id, nombre_area: nombreNuevo });
          if (res?.error) throw new Error(res.error);
          toast(res?.mensaje || "Área actualizada", "success");
          row.classList.remove("editando");
          await cargarAreas();
        } catch (e3) {
          toast(e3.message || "Error al actualizar", "error");
        }
      });
    }
  });

  // ---------- Cambiar estado ----------
  tbody?.addEventListener("change", async (e) => {
    const sw = e.target.closest(".switch-estado");
    if (!sw) return;
    const row = e.target.closest("tr[data-id]");
    const id = row?.getAttribute("data-id");
    const nuevoEstado = sw.checked ? 1 : 0;
    try {
      const res = await apiPost("cambiar_estado", { id_area: id, estado: nuevoEstado });
      if (res?.error) throw new Error(res.error);
      toast(nuevoEstado === 1 ? "Área habilitada correctamente" : "Área deshabilitada correctamente", "success");
    } catch (e4) {
      sw.checked = !sw.checked;
      toast(e4.message || "No se pudo cambiar el estado", "error");
    }
  });

  document.addEventListener("DOMContentLoaded", cargarAreas);
})();
