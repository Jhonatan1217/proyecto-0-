/* src/assets/js/gestionarInstructor.js */
(() => {
  // Usa la URL que define la vista (segura con BASE_URL). Mantengo tu valor original como último fallback.
  const API_URL = (typeof window !== "undefined" && window.API_URL)
    ? window.API_URL
    : "../controllers/InstructorController.php";

  const $ = (s, c = document) => c.querySelector(s);
  const modal = $("#modalInstructor");
  const backdrop = $("#modalBackdrop");
  const panel = $("#modalPanel");
  const btnOpen = $("#btnAbrirModalInstructor");
  const btnClose = $("#btnCerrarModalInstructor");
  const btnCancel = $("#btnCancelarModalInstructor");
  const form = $("#formNuevoInstructor");
  const tbody = $("#tbodyInstructores");
  const wrapTabla = document.getElementById("wrapTabla");

  function toast(msg, type = "success") {
    if (window.Swal) {
      Swal.fire({
        toast: true,
        position: "top-end",
        icon: type,
        title: msg,
        showConfirmButton: false,
        timer: 2200,
        timerProgressBar: true
      });
    } else {
      alert((type === "error" ? "❌ " : type === "warning" ? "⚠️ " : "✅ ") + msg);
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

  // ---------- API helpers ----------
  async function parseJsonOrThrow(res) {
    const txt = await res.text();
    try {
      return JSON.parse(txt);
    } catch {
      console.error("No JSON desde API:\n", txt);
      const status = res.status;
      const msg = status >= 400
        ? `Error ${status} del servidor`
        : "La API no devolvió JSON.";
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

  // ---------- UI helpers ----------
  function prettyTipo(t) {
    const u = (t || "").toString().toUpperCase();
    if (u === "TECNICO") return "Tecnico";
    if (u === "TRANSVERSAL") return "Transversal";
    if (u === "MIXTO") return "Mixto";
    return t;
  }
  function tipoPill(tipo) {
    const u = (tipo || "").toString().toUpperCase();
    const klass = (u === "MIXTO") ? "bg-black text-white" : "bg-gray-100 text-gray-700";
    return `<span class="${klass} text-xs px-3 py-1 rounded-full">${prettyTipo(u)}</span>`;
  }

  function renderRows(lista) {
    if (!Array.isArray(lista)) {
      tbody.innerHTML = `<tr><td class="px-6 py-6 text-red-600" colspan="3">Respuesta inesperada del servidor.</td></tr>`;
      ajustarAltoTabla();
      return;
    }
    if (lista.length === 0) {
      tbody.innerHTML = `<tr><td class="px-6 py-6 text-gray-500 text-center" colspan="3">No hay instructores.</td></tr>`;
      ajustarAltoTabla();
      return;
    }
    tbody.innerHTML = lista.map((it) => {
      const id = it.id_instructor ?? it.id ?? "";
      const nombre = it.nombre_instructor ?? it.nombre ?? "";
      const tipo = it.tipo_instructor ?? it.tipo ?? "";
      const activo = String(it.estado ?? 1) === "1";
      return `
        <tr class="border-b" data-id="${id}">
          <td class="px-6 py-4 align-middle"><span class="cell-nombre">${nombre}</span></td>
          <td class="px-6 py-4 align-middle text-center"><span class="cell-tipo">${tipoPill(tipo)}</span></td>
          <td class="px-6 py-4 align-middle text-right">
            <div class="flex justify-end items-center gap-3">
              <button class="btn-editar p-2 border rounded-xl hover:bg-gray-50 transition" type="button" title="Editar">
                <img class="w-5 h-5" src="src/assets/img/pencil-line.svg" alt="Editar" />
              </button>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer switch-estado" ${activo ? "checked" : ""}>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 transition"></div>
                <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
              </label>
            </div>
          </td>
        </tr>`;
    }).join("");

    ajustarAltoTabla(); // ⬅️ fija altura para 5 filas al renderizar
  }

  function extraerLista(res) {
    if (Array.isArray(res)) return res;
    if (res?.error) throw new Error(res.error);
    return res?.data || res?.lista || res?.rows || res?.instructores || [];
  }

  async function cargarInstructores() {
    try {
      const res = await apiGet({ accion: "listar" });
      renderRows(extraerLista(res));
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td class="px-6 py-6 text-red-600" colspan="3">${e.message}</td></tr>`;
      toast(e.message || "Error al listar", "error");
      ajustarAltoTabla();
    }
  }

  // ---------- Editar en línea / Guardar / Cancelar ----------
  tbody?.addEventListener("click", async (e) => {
    const row = e.target.closest("tr[data-id]");
    if (!row) return;
    const id = row.getAttribute("data-id");

    const btnEditar = e.target.closest(".btn-editar");
    if (btnEditar) {
      if (row.classList.contains("editando")) return;
      row.classList.add("editando");

      const cellNombre = row.querySelector(".cell-nombre");
      const cellTipo = row.querySelector(".cell-tipo");
      const acciones = row.querySelector("td:last-child > div");

      const nombreActual = cellNombre.textContent.trim();
      const tipoActualPretty = cellTipo.textContent.trim();
      const tipoActual = tipoActualPretty.toUpperCase();

      cellNombre.innerHTML = `
        <input type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-gray-300"
               value="${nombreActual}" data-edit="nombre" />`;

      cellTipo.innerHTML = `
        <div class="relative max-w-[180px] mx-auto">
          <select data-edit="tipo" class="w-full appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 focus:outline-none focus:border-gray-300">
            <option value="TECNICO" ${tipoActual==="TECNICO"?"selected":""}>Tecnico</option>
            <option value="TRANSVERSAL" ${tipoActual==="TRANSVERSAL"?"selected":""}>Transversal</option>
            <option value="MIXTO" ${tipoActual==="MIXTO"?"selected":""}>Mixto</option>
          </select>
          <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
          </svg>
        </div>`;

      acciones.innerHTML = `
        <button
          class="btn-guardar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-green-600 text-green-600 hover:bg-green-50 transition"
          type="button">
          Guardar
        </button>
        <button
          class="btn-cancelar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition"
          type="button">
          Cancelar
        </button>
      `;

      acciones.querySelector(".btn-cancelar").addEventListener("click", async () => {
        row.classList.remove("editando");
        await cargarInstructores();
      });

      acciones.querySelector(".btn-guardar").addEventListener("click", async () => {
        const nombreNuevo = row.querySelector('input[data-edit="nombre"]').value.trim();
        const tipoNuevo = row.querySelector('select[data-edit="tipo"]').value.trim();

        const noCambioNombre = nombreNuevo === nombreActual;
        const noCambioTipo = tipoNuevo.toUpperCase() === tipoActual;
        if (noCambioNombre && noCambioTipo) {
          toast("Debes modificar al menos un campo antes de guardar", "warning");
          return;
        }

        if (!nombreNuevo || !tipoNuevo) {
          toast("Complete nombre y tipo de instructor", "warning");
          return;
        }

        try {
          const res = await apiPost("actualizar", {
            id_instructor: id,
            nombre_instructor: nombreNuevo,
            tipo_instructor: tipoNuevo
          });
          if (res?.error) throw new Error(res.error);
          toast(res?.mensaje || "Instructor actualizado", "success");
          row.classList.remove("editando");
          await cargarInstructores();
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
      const res = await apiPost("cambiar_estado", { id_instructor: id, estado: nuevoEstado });
      if (res?.error) throw new Error(res.error);

      toast(nuevoEstado === 0 ? "Usuario deshabilitado correctamente" : (res?.mensaje || "Usuario habilitado correctamente"), "success");
    } catch (e4) {
      sw.checked = !sw.checked;
      toast(e4.message || "No se pudo cambiar el estado", "error");
    }
  });

  // ===== Scroll interno: exactamente 5 filas visibles =====
  function ajustarAltoTabla() {
    if (!wrapTabla) return;
    const thead = document.querySelector("#tablaInstructores thead");
    const firstRow = document.querySelector("#tablaInstructores tbody tr");
    const headH = thead ? thead.getBoundingClientRect().height : 44;   // fallback
    const rowH  = firstRow ? firstRow.getBoundingClientRect().height : 56; // fallback
    // Altura = encabezado + 5 filas
    const maxH = headH + rowH * 5;
    wrapTabla.style.maxHeight = `${Math.ceil(maxH)}px`;
  }
  window.addEventListener("resize", ajustarAltoTabla);

  // Init
  document.addEventListener("DOMContentLoaded", async () => {
    await cargarInstructores();
    ajustarAltoTabla(); // asegura altura incluso si la lista llega vacía
  });
})();
