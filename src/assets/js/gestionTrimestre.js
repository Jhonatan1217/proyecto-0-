/**
 * Gestión de Trimestres - Refactorizado: table-edit + modal-enterprise + buscador clear.
 */
(() => {
  const API_URL = (typeof window !== "undefined" && window.API_URL) ? window.API_URL : "src/controllers/TrimestreController.php";
  const ICON_PENCIL = window.ICON_PENCIL_TRIMESTRE || "src/assets/img/pencil-line.svg";

  function toast(title, icon = "success") {
    if (window.Swal) {
      Swal.fire({ toast: true, position: "top-end", icon, title, showConfirmButton: false, timer: 2500, timerProgressBar: true });
    } else {
      alert((icon === "error" ? "❌ " : icon === "warning" ? "⚠ " : "✅ ") + title);
    }
  }

  function setupBuscadorConClear() {
    const wrap = document.getElementById("buscadorTrimestreWrap") || document.getElementById("buscadorTrimestre")?.parentElement;
    const input = document.getElementById("buscadorTrimestre");
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
    clearBtn?.addEventListener("click", () => {
      input.value = "";
      input.focus();
      toggleClear();
      if (typeof aplicarFiltroBusqueda === "function") aplicarFiltroBusqueda();
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalTrimestre");
    const form = document.getElementById("formNuevoTrimestre");
    const tbody = document.getElementById("tbodyTrimestres");
    const buscador = document.getElementById("buscadorTrimestre");
    let listaTrimestres = [];

    function openModal() {
      modal?.classList.remove("hidden");
      modal?.classList.add("flex");
      document.body.style.overflow = "hidden";
    }

    function closeModal() {
      form?.reset();
      modal?.classList.add("hidden");
      modal?.classList.remove("flex");
      document.body.style.overflow = "";
      if (typeof ComboboxComponent !== "undefined") ComboboxComponent.reset();
    }

    function aplicarFiltroBusqueda() {
      const term = (buscador?.value || "").trim().toLowerCase();
      const list = !term ? listaTrimestres : listaTrimestres.filter((t) =>
        String(t.numero_trimestre ?? "").toLowerCase().includes(term)
      );
      renderRows(list);
    }

    function renderRows(lista) {
      if (!tbody) return;
      if (!Array.isArray(lista) || lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" class="px-6 py-8 text-gray-500 text-center">${lista?.length === 0 ? "No hay trimestres registrados" : "Cargando..."}</td></tr>`;
        return;
      }
      tbody.innerHTML = lista.map((t) => {
        const num = t.numero_trimestre ?? "";
        const activo = t.estado == 1;
        return `<tr class="border-b hover:bg-gray-50 transition-colors" data-numero="${num}">
          <td class="px-6 py-4 align-middle text-sm font-medium text-gray-800">Trimestre ${num}</td>
          <td class="px-6 py-4 align-middle text-right">
            <div class="flex justify-end items-center gap-3">
              <button class="btn-editar p-2 border rounded-xl hover:bg-gray-100 transition btnEditar" data-numero="${num}" title="Editar">
                <img class="w-5 h-5 pointer-events-none" src="${ICON_PENCIL}" alt="Editar" />
              </button>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer toggleEstado" data-numero="${num}" ${activo ? "checked" : ""} aria-label="Activar trimestre ${num}">
                <div class="w-11 h-6 rounded-full bg-gray-200 transition peer-checked:bg-[#39A900] peer-disabled:opacity-60"></div>
                <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></div>
              </label>
            </div>
          </td>
        </tr>`;
      }).join("");
    }

    async function cargarTrimestres() {
      if (!tbody) return;
      tbody.innerHTML = `<tr><td colspan="2" class="px-6 py-8 text-gray-500 text-center">Cargando...</td></tr>`;
      try {
        const res = await fetch(API_URL);
        const data = await res.json();
        listaTrimestres = Array.isArray(data) ? data : data?.data ?? [];
        aplicarFiltroBusqueda();
      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="2" class="px-6 py-8 text-red-500 text-center">Error al cargar los datos</td></tr>`;
      }
    }

    document.getElementById("btnAbrirModalTrimestre")?.addEventListener("click", openModal);
    document.getElementById("btnCerrarModalTrimestre")?.addEventListener("click", closeModal);
    document.getElementById("btnCancelarModalTrimestre")?.addEventListener("click", closeModal);
    modal?.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
    window.addEventListener("keydown", (e) => { if (modal && !modal.classList.contains("hidden") && e.key === "Escape") closeModal(); });

    buscador?.addEventListener("input", aplicarFiltroBusqueda);
    setupBuscadorConClear();

    form?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const numero = document.getElementById("inputNumeroTrimestre").value.trim();
      if (!numero) return toast("Ingresa el número del trimestre", "warning");
      if (isNaN(numero) || numero.includes(",") || !Number.isInteger(Number(numero))) {
        return toast("Solo se permiten números enteros", "warning");
      }
      if (Number(numero) < 1) return toast("El número debe ser mayor a 0", "warning");
      try {
        const res = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ numero_trimestre: numero, estado: 1 }),
        });
        const data = await res.json();
        if (data.mensaje?.toLowerCase().includes("existe") || data.mensaje?.toLowerCase().includes("repetido")) {
          return toast("Ya existe un trimestre con ese número", "warning");
        }
        if (data.status === "success" || data.mensaje?.includes("correctamente")) {
          toast(data.mensaje || "Trimestre creado correctamente", "success");
          closeModal();
          cargarTrimestres();
        } else {
          toast(data.mensaje || "No se pudo crear", "error");
        }
      } catch (err) {
        toast("No se pudo conectar con el servidor", "error");
      }
    });

    tbody?.addEventListener("click", async (e) => {
      const btn = e.target.closest("button");
      if (!btn) return;
      const tr = btn.closest("tr");
      const tdNumero = tr?.children[0];
      const tdAcc = tr?.children[1];
      const original = tr?.dataset.originalNumero;

      if (btn.classList.contains("btnGuardar") || btn.classList.contains("btn-icon-check")) {
        const nuevoNumero = (tr.querySelector("input[data-edit='numero']")?.value || "").trim();
        if (!nuevoNumero) return toast("Ingresa un número válido", "warning");
        if (isNaN(nuevoNumero) || !Number.isInteger(Number(nuevoNumero)) || Number(nuevoNumero) < 1) {
          return toast("Solo números enteros mayores a 0", "warning");
        }
        if (nuevoNumero === original) return toast("No ha cambiado nada. Modifica al menos un campo para guardar.", "warning");
        try {
          const res = await fetch(API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ accion: "editar", numero_trimestre: original, nuevo_numero: nuevoNumero }),
          });
          const data = await res.json();
          if (data.mensaje?.toLowerCase().includes("existe") || data.mensaje?.toLowerCase().includes("repetido")) {
            return toast("Ya existe un trimestre con ese número", "warning");
          }
          if (data.status === "success" || data.mensaje?.includes("correctamente")) {
            toast(data.mensaje || "Trimestre actualizado", "success");
            cargarTrimestres();
          } else {
            toast(data.mensaje || "No se pudo guardar", "error");
          }
        } catch (err) {
          toast("Error de conexión", "error");
        }
        return;
      }

      if (btn.classList.contains("btn-icon-x") || btn.classList.contains("btnCancelar")) {
        if (typeof ComboboxComponent !== "undefined") ComboboxComponent.reset();
        tdNumero.innerHTML = `Trimestre ${original}`;
        tdAcc.innerHTML = `
          <div class="flex justify-end items-center gap-3">
            <button class="btn-editar p-2 border rounded-xl hover:bg-gray-100 transition btnEditar" data-numero="${original}" title="Editar">
              <img class="w-5 h-5 pointer-events-none" src="${ICON_PENCIL}" alt="Editar" />
            </button>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer toggleEstado" data-numero="${original}" aria-label="Activar trimestre ${original}">
              <div class="w-11 h-6 rounded-full bg-gray-200 transition peer-checked:bg-[#39A900] peer-disabled:opacity-60"></div>
              <div class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></div>
            </label>
          </div>`;
        const estadoOriginal = listaTrimestres.find((t) => String(t.numero_trimestre) === String(original));
        const chk = tdAcc.querySelector(".toggleEstado");
        if (chk && estadoOriginal) chk.checked = estadoOriginal.estado == 1;
        tr?.classList.remove("editando");
        delete tr?.dataset.editing;
        delete tr?.dataset.originalNumero;
        return;
      }

      if (btn.classList.contains("btnEditar")) {
        if (tr.dataset.editing === "1") return;
        tr.classList.add("editando");
        tr.dataset.editing = "1";
        const numeroActual = btn.dataset.numero;
        tr.dataset.originalNumero = numeroActual;
        tdNumero.innerHTML = `<div class="cell-edit-wrap"><input data-edit="numero" type="number" value="${numeroActual}" min="1" class="cell-edit numero input-enterprise" /></div>`;
        tdAcc.innerHTML = `
          <div class="acciones-edit">
            <button type="button" class="btnGuardar btn-icon-check p-2 rounded-lg transition" title="Guardar" aria-label="Guardar">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
            <button type="button" class="btnCancelar btn-icon-x p-2 rounded-lg transition" title="Cancelar" aria-label="Cancelar">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>`;
      }
    });

    document.body.addEventListener("change", async (e) => {
      const chk = e.target.closest(".toggleEstado");
      if (!chk) return;
      const numero = chk.dataset.numero;
      const accion = chk.checked ? "reactivar" : "suspender";
      try {
        const res = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ accion, numero_trimestre: numero }),
        });
        const data = await res.json();
        if (data.mensaje?.includes("Error")) {
          toast("No se pudo actualizar el estado", "error");
          chk.checked = !chk.checked;
        } else {
          toast(data.mensaje, "success");
          cargarTrimestres();
        }
      } catch (err) {
        toast("Error de conexión", "error");
        chk.checked = !chk.checked;
      }
    });

    document.addEventListener("input", (e) => {
      if (e.target.type === "number" && e.target.value < 0) {
        e.target.value = 0;
        toast("No se permiten valores negativos", "warning");
      }
    });

    cargarTrimestres();
  });
})();
