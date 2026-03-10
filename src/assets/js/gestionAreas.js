/**
 * Gestión de Áreas - Refactorizado: table-edit + modal-enterprise + buscador clear.
 */
(() => {
  const API_URL = (typeof window !== "undefined" && window.API_URL) ? window.API_URL : "../../controllers/AreaController.php";
  const $ = (s, c = document) => c.querySelector(s);

  function toast(msg, type = "success") {
    if (window.Swal) {
      Swal.fire({ toast: true, position: "top-end", icon: type, title: msg, showConfirmButton: false, timer: 2200, timerProgressBar: true });
    } else {
      alert((type === "error" ? "❌ " : type === "warning" ? "⚠ " : "✅ ") + msg);
    }
  }

  async function parseJsonOrThrow(res) {
    const txt = await res.text();
    try { return JSON.parse(txt); } catch {
      console.error("No JSON desde API:\n", txt);
      throw new Error(res.status >= 400 ? `Error ${res.status} del servidor` : "La API no devolvió JSON.");
    }
  }

  async function apiGet(params) {
    const url = `${API_URL}?${new URLSearchParams(params).toString()}`;
    const res = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
    return parseJsonOrThrow(res);
  }

  async function apiPost(accion, payload) {
    const res = await fetch(`${API_URL}?accion=${encodeURIComponent(accion)}`, {
      method: "POST",
      headers: { "Content-Type": "application/json; charset=utf-8", Accept: "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(payload),
    });
    return parseJsonOrThrow(res);
  }

  function setupBuscadorConClear() {
    const wrap = document.getElementById("buscadorAreaWrap") || document.getElementById("buscadorArea")?.parentElement;
    const input = document.getElementById("buscadorArea");
    if (!wrap || !input) return;
    let clearBtn = wrap.querySelector(".btn-clear-buscador");
    if (!clearBtn) {
      clearBtn = document.createElement("button");
      clearBtn.type = "button";
      clearBtn.className = "btn-clear-buscador absolute right-10 top-1/2 -translate-y-1/2 p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 hidden";
      clearBtn.setAttribute("aria-label", "Limpiar búsqueda");
      clearBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
      wrap.appendChild(clearBtn);
    }
    function toggleClear() { if (clearBtn) clearBtn.classList.toggle("hidden", !input.value.trim()); }
    input.addEventListener("input", toggleClear);
    input.addEventListener("focus", toggleClear);
    clearBtn?.addEventListener("click", () => { input.value = ""; input.focus(); toggleClear(); aplicarFiltroBusqueda(); });
  }

  document.addEventListener("DOMContentLoaded", () => {
    const modal = $("#modalArea");
    const form = $("#formNuevaArea");
    const tbody = $("#tbodyAreas");
    const buscadorArea = $("#buscadorArea");
    let listaAreas = [];

    function aplicarFiltroBusqueda() {
      const term = (buscadorArea?.value || "").trim().toLowerCase();
      const list = !term ? listaAreas : listaAreas.filter((a) => (a.nombre_area || "").toLowerCase().includes(term));
      renderRows(list);
    }

    function openModal() {
      if (!modal) return;
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      document.body.style.overflow = "hidden";
    }

    function closeModal() {
      form?.reset();
      modal?.classList.add("hidden");
      modal?.classList.remove("flex");
      document.body.style.overflow = "";
    }

    const ICON_PENCIL = window.ICON_PENCIL_AREA || "src/assets/img/pencil-line.svg";

    function renderRows(lista) {
      if (!tbody) return;
      if (!Array.isArray(lista)) {
        tbody.innerHTML = `<tr><td class="px-6 py-6 text-red-600 text-center" colspan="2">Respuesta inesperada</td></tr>`;
        return;
      }
      if (lista.length === 0) {
        tbody.innerHTML = `<tr><td class="px-6 py-6 text-gray-500 text-center" colspan="2">No hay áreas</td></tr>`;
        return;
      }
      tbody.innerHTML = lista.map((it) => {
        const id = it.id_area ?? "";
        const nombre = it.nombre_area ?? "";
        const activo = String(it.estado ?? 1) === "1";
        return `<tr class="border-b hover:bg-gray-50 transition-colors" data-id="${id}">
          <td class="px-6 py-4 align-middle"><span class="cell-nombre">${nombre}</span></td>
          <td class="px-6 py-4 align-middle text-right">
            <div class="flex justify-end items-center gap-3 acciones">
              <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" type="button" title="Editar">
                <img class="w-5 h-5 pointer-events-none" src="${ICON_PENCIL}" alt="Editar" />
              </button>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer switch-estado" ${activo ? "checked" : ""} data-id="${id}" aria-label="Cambiar estado de ${nombre}">
                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-[#39A900] transition"></div>
                <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
              </label>
            </div>
          </td>
        </tr>`;
      }).join("");
    }

    async function cargarAreas() {
      if (!tbody) return;
      try {
        const res = await apiGet({ accion: "listar" });
        listaAreas = Array.isArray(res) ? res : res?.data || [];
        aplicarFiltroBusqueda();
      } catch (e) {
        console.error(e);
        tbody.innerHTML = `<tr><td class="px-6 py-6 text-red-600 text-center" colspan="2">${e.message}</td></tr>`;
        toast("Error al cargar áreas", "error");
      }
    }

    $("#btnAbrirModalArea")?.addEventListener("click", openModal);
    $("#btnCerrarModalArea")?.addEventListener("click", closeModal);
    $("#btnCancelarModalArea")?.addEventListener("click", closeModal);
    modal?.querySelector(".modal-area-box")?.addEventListener("click", (e) => e.stopPropagation());
    modal?.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

    buscadorArea?.addEventListener("input", aplicarFiltroBusqueda);
    setupBuscadorConClear();

    form?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const nombre = (form.querySelector("input[name=nombre_area]").value || "").trim();
      if (!nombre) { toast("Debe ingresar el nombre del área", "warning"); return; }
      if (listaAreas.some((a) => (a.nombre_area || "").trim().toLowerCase() === nombre.toLowerCase())) {
        toast("Ya existe un área con ese nombre", "warning");
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
        const nombreActual = (cellNombre?.textContent || "").trim();
        cellNombre.innerHTML = `<div class="cell-edit-wrap"><input type="text" class="cell-edit input-enterprise" value="${nombreActual}" data-edit="nombre" /></div>`;
        acciones.innerHTML = `
          <div class="acciones-edit">
            <button type="button" class="btn-guardar btn-icon-check p-2 rounded-lg transition" title="Guardar" aria-label="Guardar">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
            <button type="button" class="btn-cancelar btn-icon-x p-2 rounded-lg transition" title="Cancelar" aria-label="Cancelar">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        `;
        acciones.querySelector(".btn-cancelar")?.addEventListener("click", () => { row.classList.remove("editando"); cargarAreas(); });
        acciones.querySelector(".btn-guardar")?.addEventListener("click", async () => {
          const nombreNuevo = (row.querySelector('input[data-edit="nombre"]')?.value || "").trim();
          if (!nombreNuevo) { toast("Debe ingresar nombre del área", "warning"); return; }
          if (listaAreas.some((a) => a.id_area != id && (a.nombre_area || "").trim().toLowerCase() === nombreNuevo.toLowerCase())) {
            toast("Ya existe un área con ese nombre", "warning");
            return;
          }
          if (nombreNuevo === nombreActual) { toast("Debes modificar el campo antes de guardar", "warning"); return; }
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

    tbody?.addEventListener("change", async (e) => {
      const sw = e.target.closest(".switch-estado");
      if (!sw) return;
      const row = e.target.closest("tr[data-id]");
      const id = row?.getAttribute("data-id");
      if (!id) return;
      const nuevoEstado = sw.checked ? 1 : 0;
      if (nuevoEstado === 0) {
        let confirmado = true;
        if (window.Swal) {
          const result = await Swal.fire({
            title: "Deshabilitar área",
            text: "Si deshabilitas esta área, también se deshabilitarán todas las entidades relacionadas (zonas y horarios). ¿Deseas continuar?",
            icon: "warning",
            width: 420,
            showCancelButton: true,
            confirmButtonText: "Sí, deshabilitar todo",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
            confirmButtonColor: "#39A900",
            cancelButtonColor: "#6B7280",
          });
          confirmado = result.isConfirmed;
        } else {
          confirmado = window.confirm("Si deshabilitas esta área, se deshabilitarán los registros relacionados. ¿Continuar?");
        }
        if (!confirmado) { sw.checked = true; return; }
      }
      try {
        const res = await apiPost("cambiar_estado", { id_area: id, estado: nuevoEstado, cascada: 1 });
        if (res?.error) throw new Error(res.error);
        toast(nuevoEstado === 1 ? (res?.mensaje || "Área habilitada") : (res?.mensaje || "Área deshabilitada"), "success");
        await cargarAreas();
      } catch (e4) {
        sw.checked = !sw.checked;
        toast(e4.message || "No se pudo cambiar el estado", "error");
      }
    });

    cargarAreas();
  });
})();
