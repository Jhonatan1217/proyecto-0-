<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Trimestres</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-1 text-[#2a7f00]">Gestión de Trimestres</h1>
    <p class="text-gray-600 mb-8">Administra los trimestres</p>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Trimestres</h2>
        <button 
          id="btnAbrirModalTrimestre"
          class="bg-[#00324D] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00273A] transition"
          type="button"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Nuevo Trimestre</span>
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-800" id="tablaTrimestres">
          <thead>
            <tr class="border-b bg-gray-50 text-sm text-gray-700">
              <th class="px-6 py-3 font-semibold">N° Trimestre</th>
              <th class="px-6 py-3 font-semibold text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyTrimestres" class="text-sm">
            <!-- Se llena dinámicamente -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal: Nuevo Trimestre -->
  <div id="modalTrimestre" class="fixed inset-0 z-50 hidden">
    <div id="backdrop" class="absolute inset-0 bg-black/70 opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel" class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 opacity-0 scale-95 transition-all duration-200 relative">
        <button id="btnCerrarModalTrimestre" class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition" type="button">✕</button>

        <h3 class="text-xl font-semibold mb-1">Nuevo Trimestre</h3>
        <p class="text-sm text-gray-500 mb-5">Ingresa el número de trimestre</p>

        <form id="formNuevoTrimestre" class="space-y-5">
          <div>
            <label class="block text-sm font-semibold mb-1">Número de trimestre</label>
            <input id="inputNumeroTrimestre" type="number" placeholder="Ej: 1"
              class="w-full rounded-xl border border-gray-300 px-4 py-2 focus:border-[#2a7f00] focus:ring-0 outline-none" required />
          </div>

          <div class="flex justify-end gap-4 pt-2">
            <button type="button" id="btnCancelarModalTrimestre"
              class="px-5 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">
              Cancelar
            </button>
            <button type="submit"
              class="px-6 py-2 rounded-xl bg-[#00324D] text-white hover:bg-[#00273A] transition">
              Crear Trimestre
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS -->
<script>
const API_URL = "../controllers/TrimestreController.php";

/* ================================
   CONFIGURACIÓN DE TOAST GLOBAL
================================= */
function toast(title, icon = "info") {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: "#fff",
    color: "#333",
  });
  Toast.fire({ icon, title });
}

/* ================================
   MODAL ABRIR / CERRAR
================================= */
const btnOpen = document.getElementById("btnAbrirModalTrimestre");
const modal = document.getElementById("modalTrimestre");
const backdrop = document.getElementById("backdrop");
const panel = document.getElementById("modalPanel");
const btnClose = document.getElementById("btnCerrarModalTrimestre");
const btnCancelModal = document.getElementById("btnCancelarModalTrimestre");

function openModal() {
  modal.classList.remove("hidden");
  requestAnimationFrame(() => {
    backdrop.classList.remove("opacity-0");
    backdrop.classList.add("opacity-100");
    panel.classList.remove("opacity-0", "scale-95");
    panel.classList.add("opacity-100", "scale-100");
  });
}

function closeModal() {
  backdrop.classList.remove("opacity-100");
  backdrop.classList.add("opacity-0");
  panel.classList.remove("opacity-100", "scale-100");
  panel.classList.add("opacity-0", "scale-95");
  setTimeout(() => modal.classList.add("hidden"), 200);
}

btnOpen?.addEventListener("click", openModal);
btnClose?.addEventListener("click", closeModal);
btnCancelModal?.addEventListener("click", closeModal);
backdrop?.addEventListener("click", (e) => { if (e.target === backdrop) closeModal(); });
window.addEventListener("keydown", (e) => { if (!modal.classList.contains("hidden") && e.key === "Escape") closeModal(); });

/* ================================
   CARGAR TRIMESTRES
================================= */
async function cargarTrimestres() {
  const tbody = document.getElementById("tbodyTrimestres");
  tbody.innerHTML = `<tr><td colspan="2" class="text-center py-4">Cargando...</td></tr>`;

  try {
    const res = await fetch(API_URL);
    const data = await res.json();
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="2" class="text-center py-4 text-gray-500">No hay trimestres registrados</td></tr>`;
      return;
    }

    data.forEach(t => {
      const tr = document.createElement("tr");
      tr.className = "border-b";
      tr.innerHTML = `
        <td class="px-6 py-4 align-middle text-sm font-medium text-gray-800">Trimestre ${t.numero_trimestre}</td>
        <td class="px-6 py-4 align-middle text-right">
          <div class="flex justify-end items-center gap-3">
            <button class="p-2 border rounded-xl hover:bg-gray-100 transition btnEditar" data-numero="${t.numero_trimestre}" title="Editar">
              <img class="w-5 h-5" src="../assets/img/pencil-line.svg" alt="Editar" />
            </button>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer toggleEstado" data-numero="${t.numero_trimestre}" ${t.estado == 1 ? "checked" : ""}>
              <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#2a7f00] transition"></div>
              <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
            </label>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    agregarEventosTabla();
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="2" class="text-center text-red-500 py-4">Error al cargar los datos</td></tr>`;
  }
}

/* ================================
   EVENTOS DE TABLA
================================= */
function agregarEventosTabla() {
  const tbody = document.getElementById("tbodyTrimestres");

  tbody.addEventListener("click", async (e) => {
    const btn = e.target.closest("button");
    if (!btn) return;

    const tr = btn.closest("tr");
    const tdNumero = tr.children[0];
    const tdAcc = tr.children[1];

    // === GUARDAR ===
    if (btn.classList.contains("btnGuardar")) {
      const nuevoNumero = tr.querySelector("input[data-edit='numero']").value.trim();
      const original = tr.dataset.originalNumero;

      if (!nuevoNumero) {
        toast("Ingresa un número de trimestre válido", "warning");
        return;
      }

      try {
        const res = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ accion: "editar", numero_trimestre: original, nuevo_numero: nuevoNumero }),
        });

        const data = await res.json();

        if (data.status === "success" || data.mensaje?.includes("correctamente")) {
          tdNumero.innerHTML = `Trimestre ${nuevoNumero}`;
          restaurarAcciones(tdAcc);
          delete tr.dataset.editing;
          toast(data.mensaje || "Trimestre actualizado correctamente", "success");
          cargarTrimestres();
        } else {
          toast(data.mensaje || "No se pudo guardar el trimestre", "error");
        }
      } catch (err) {
        console.error(err);
        toast("No se pudo conectar con el servidor", "error");
      }
      return;
    }

    // === CANCELAR ===
    if (btn.classList.contains("btnCancelar")) {
      tdNumero.innerHTML = `Trimestre ${tr.dataset.originalNumero}`;
      restaurarAcciones(tdAcc);
      delete tr.dataset.editing;
      return;
    }

    // === EDITAR ===
    if (btn.classList.contains("btnEditar")) {
      if (tr.dataset.editing === "1") return;
      tr.dataset.editing = "1";
      const numeroActual = btn.dataset.numero;
      tr.dataset.originalNumero = numeroActual;

      tdNumero.innerHTML = `
        <input data-edit="numero" type="number" value="${numeroActual}" min="0"
          class="w-32 rounded-xl border border-gray-300 px-3 py-1 focus:ring-0 focus:outline-none focus:border-[#2a7f00]" />
      `;

      btn.classList.add("hidden");
      const accionesBox = tdAcc.querySelector(".flex");

      const btnGuardar = document.createElement("button");
      btnGuardar.className = "btnGuardar px-3 py-2 rounded-xl border border-green-600 text-green-700 hover:bg-green-50 transition";
      btnGuardar.textContent = "Guardar";

      const btnCancelar = document.createElement("button");
      btnCancelar.className = "btnCancelar px-3 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition";
      btnCancelar.textContent = "Cancelar";

      accionesBox.insertBefore(btnGuardar, accionesBox.lastElementChild);
      accionesBox.insertBefore(btnCancelar, accionesBox.lastElementChild);
    }
  });

  document.querySelectorAll(".toggleEstado").forEach(chk => {
    chk.addEventListener("change", async (e) => {
      const numero = e.currentTarget.dataset.numero;
      const accion = e.currentTarget.checked ? "reactivar" : "suspender";
      try {
        const res = await fetch(API_URL, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ accion, numero_trimestre: numero })
        });
        const data = await res.json();

        if (data.mensaje?.includes("Error")) {
          toast("No se pudo actualizar el estado", "error");
          e.currentTarget.checked = !e.currentTarget.checked;
        } else {
          toast(data.mensaje, "success");
        }
      } catch (error) {
        toast("No se pudo conectar con el servidor", "error");
        e.currentTarget.checked = !e.currentTarget.checked;
      }
    });
  });
}

function restaurarAcciones(tdAcc) {
  const accionesBox = tdAcc.querySelector(".flex");
  accionesBox.querySelector(".btnEditar")?.classList.remove("hidden");
  accionesBox.querySelector(".btnGuardar")?.remove();
  accionesBox.querySelector(".btnCancelar")?.remove();
}

/* ================================
   CREAR TRIMESTRE
================================= */
document.getElementById("formNuevoTrimestre").addEventListener("submit", async (e) => {
  e.preventDefault();
  const numero = document.getElementById("inputNumeroTrimestre").value.trim();

  if (!numero) {
    toast("Ingresa el número del trimestre", "warning");
    return;
  }

  const body = { numero_trimestre: numero, estado: 1 };

  try {
    const res = await fetch(API_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });

    const data = await res.json();

    if (data.status === "success" || data.mensaje?.includes("correctamente")) {
      toast(data.mensaje || "Trimestre creado correctamente", "success");
      closeModal();
      cargarTrimestres();
    } else {
      toast(data.mensaje || "No se pudo crear el trimestre", "error");
    }
  } catch (err) {
    console.error(err);
    toast("No se pudo conectar con el servidor", "error");
  }
});

/* ================================
   EVITAR NÚMEROS NEGATIVOS EN INPUTS
================================= */
document.addEventListener("input", (e) => {
  if (e.target.type === "number") {
    if (e.target.value < 0) {
      e.target.value = 0;
      toast("No se permiten valores negativos", "warning");
    }
  }
});

/* ================================
   INICIALIZAR
================================= */
document.addEventListener("DOMContentLoaded", cargarTrimestres);
</script>

</body>
</html>