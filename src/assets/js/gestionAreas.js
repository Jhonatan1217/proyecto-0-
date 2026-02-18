// src/assets/js/gestionAreas.js
(() => {
  const API_URL = (typeof window !== "undefined" && window.API_URL)
    ? window.API_URL
    : "../../controllers/AreaController.php";

  // Helper para querySelector
  const $ = (s, c = document) => c.querySelector(s);

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
      alert(
        (type === "error" ? "❌ " : type === "warning" ? "⚠ " : "✅ ") + msg
      );
    }
  }

  // ---------- Helpers de fetch ----------
  async function parseJsonOrThrow(res) {
    const txt = await res.text();
    try {
      return JSON.parse(txt);
    } catch {
      console.error("No JSON desde API:\n", txt);
      const status = res.status;
      const msg =
        status >= 400
          ? `Error ${status} del servidor`
          : "La API no devolvió JSON.";
      throw new Error(msg);
    }
  }

  async function apiGet(params) {
    const url = `${API_URL}?${new URLSearchParams(params).toString()}`;
    const res = await fetch(url, {
      headers: { Accept: "application/json" },
      credentials: "same-origin",
    });
    return parseJsonOrThrow(res);
  }

  async function apiPost(accion, payload) {
    const url = `${API_URL}?accion=${encodeURIComponent(accion)}`;
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json; charset=utf-8",
        Accept: "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify(payload),
    });
    return parseJsonOrThrow(res);
  }

  /* =======================================================
     SCROLL DINÁMICO – MISMO COMPORTAMIENTO QUE INSTRUCTORES
     (altura fija a 5 filas visibles dentro de la tarjeta)
  ======================================================= */
  function ajustarAltoTablaAreas() {
    const wrapper = document.getElementById("areasWrapper");
    const tabla = document.getElementById("tablaAreas");
    if (!wrapper || !tabla) return;

    const filasNode = tabla.querySelectorAll("tbody tr");
    const filas = filasNode.length;

    // Si no hay filas, no forzamos altura ni scroll
    if (!filas) {
      wrapper.style.maxHeight = "none";
      wrapper.style.overflowY = "hidden";
      return;
    }

    const thead = tabla.querySelector("thead");
    const firstRow = filasNode[0];

    const headH = thead ? thead.getBoundingClientRect().height : 44;
    const rowH = firstRow ? firstRow.getBoundingClientRect().height : 56;

    // Altura para 5 filas (igual que en instructores)
    const maxH = headH + rowH * 5;

    if (filas > 5) {
      wrapper.style.maxHeight = `${Math.ceil(maxH)}px`;
      wrapper.style.overflowY = "auto";
      wrapper.style.overscrollBehavior = "contain";
    } else {
      // Si hay menos de 5 filas, dejamos la altura justa sin mostrar scroll
      const h = headH + rowH * filas;
      wrapper.style.maxHeight = `${Math.ceil(h)}px`;
      wrapper.style.overflowY = "hidden";
      wrapper.style.overscrollBehavior = "auto";
    }
  }
  /* ======================================================= */

  // --- TODO el manejo de DOM lo hacemos cuando el DOM esté listo ---
  document.addEventListener("DOMContentLoaded", () => {
    const modal = $("#modalArea");
    const backdrop = $("#modalBackdrop");
    const panel = $("#modalPanel");
    const btnOpen = $("#btnAbrirModalArea");
    const btnClose = $("#btnCerrarModalArea");
    const btnCancel = $("#btnCancelarModalArea");
    const form = $("#formNuevaArea");
    const tbody = $("#tablaAreas tbody");

    let listaAreas = []; // Guardar en memoria la lista de áreas actuales

    // ---------- Modal ----------
    function openModal() {
      if (!modal) return;
      modal.classList.remove("hidden");
      requestAnimationFrame(() => {
        backdrop?.classList.remove("opacity-0");
        panel?.classList.remove("opacity-0", "scale-95", "translate-y-2");
      });
    }
    function closeModal() {
      form?.reset();
      backdrop?.classList.add("opacity-0");
      panel?.classList.add("opacity-0", "scale-95", "translate-y-2");
      if (modal) {
        setTimeout(() => modal.classList.add("hidden"), 180);
      }
    }

    btnOpen?.addEventListener("click", openModal);
    btnClose?.addEventListener("click", closeModal);
    btnCancel?.addEventListener("click", closeModal);
    backdrop?.addEventListener("click", (e) => {
      if (e.target === backdrop) closeModal();
    });

    // ---------- Render ----------
    function renderRows(lista) {
      if (!tbody) return;

      if (!Array.isArray(lista)) {
        tbody.innerHTML = `
          <tr>
            <td class="px-6 py-6 text-red-600 text-center" colspan="2">
              Respuesta inesperada
            </td>
          </tr>`;
        ajustarAltoTablaAreas();
        return;
      }

      if (lista.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td class="px-6 py-6 text-gray-500 text-center" colspan="2">
              No hay áreas
            </td>
          </tr>`;
        ajustarAltoTablaAreas();
        return;
      }

      tbody.innerHTML = lista
        .map((it) => {
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
        })
        .join("");

      // Ajustar alto y scroll después de re-renderizar
      ajustarAltoTablaAreas();
    }

    async function cargarAreas() {
      if (!tbody) return;
      try {
        const res = await apiGet({ accion: "listar" });
        listaAreas = Array.isArray(res) ? res : res?.data || [];
        renderRows(listaAreas);
      } catch (e) {
        console.error(e);
        tbody.innerHTML = `
          <tr>
            <td class="px-6 py-6 text-red-600 text-center" colspan="2">
              ${e.message}
            </td>
          </tr>`;
        toast("Error al cargar áreas", "error");
        ajustarAltoTablaAreas();
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

      const duplicado = listaAreas.some(
        (a) => a.nombre_area.trim().toLowerCase() === nombre.toLowerCase()
      );
      if (duplicado) {
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

        cellNombre.innerHTML = `
          <input type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2"
                 value="${nombreActual}" data-edit="nombre" />`;

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

          const duplicadoEditar = listaAreas.some(
            (a) =>
              a.id_area != id &&
              a.nombre_area.trim().toLowerCase() === nombreNuevo.toLowerCase()
          );
          if (duplicadoEditar) {
            toast("Ya existe un área con ese nombre", "warning");
            return;
          }

          if (nombreNuevo === nombreActual) {
            toast("Debes modificar el campo antes de guardar", "warning");
            return;
          }

          try {
            const res = await apiPost("actualizar", {
              id_area: id,
              nombre_area: nombreNuevo,
            });
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

    // ---------- Cambiar estado (con cascada al deshabilitar/habilitar) ----------
    tbody?.addEventListener("change", async (e) => {
      const sw = e.target.closest(".switch-estado");
      if (!sw) return;

      const row = e.target.closest("tr[data-id]");
      const id = row?.getAttribute("data-id");
      if (!id) return;

      const nuevoEstado = sw.checked ? 1 : 0;

      console.log("Cambio estado área:", { id, nuevoEstado }); // DEBUG

      // Si se va a DESHABILITAR el área, pedimos confirmación
      if (nuevoEstado === 0) {
        let confirmado = true;

        if (window.Swal) {
          const result = await Swal.fire({
            title: "Deshabilitar área",
            text: "Si deshabilitas esta área, también se deshabilitarán todas las entidades relacionadas (por ejemplo, zonas y horarios asociados). ¿Deseas continuar?",
            icon: "warning",
            width: 420, // 🔹 Más pequeña que el tamaño por defecto
            showCancelButton: true,
            confirmButtonText: "Sí, deshabilitar todo",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
            // 🔹 Colores institucionales
            confirmButtonColor: "#39A900", // Verde SENA
            cancelButtonColor: "#6B7280",  // Gris
          });
          confirmado = result.isConfirmed;
        } else {
          confirmado = window.confirm(
            "Si deshabilitas esta área, también se deshabilitarán todos los registros relacionados. ¿Deseas continuar?"
          );
        }

        if (!confirmado) {
          // Revertir el switch si el usuario cancela
          sw.checked = true;
          return;
        }
      }

      try {
        const payload = {
          id_area: id,
          estado: nuevoEstado,
          // Ahora siempre aplicamos la lógica en cascada (área + zonas relacionadas)
          cascada: 1,
        };

        const res = await apiPost("cambiar_estado", payload);
        if (res?.error) throw new Error(res.error);

        toast(
          nuevoEstado === 1
            ? res?.mensaje || "Área y zonas relacionadas habilitadas correctamente"
            : res?.mensaje || "Área y zonas relacionadas deshabilitadas correctamente",
          "success"
        );
      } catch (e4) {
        // Si algo falla, revertimos visualmente el switch
        sw.checked = !sw.checked;
        toast(e4.message || "No se pudo cambiar el estado", "error");
      }
    });

    // Recalcular alto al cambiar el tamaño de la ventana
    window.addEventListener("resize", ajustarAltoTablaAreas);

    // Cargar al inicio
    cargarAreas();
  });
})();
