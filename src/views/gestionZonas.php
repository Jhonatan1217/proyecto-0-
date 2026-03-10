<?php
$cargo = $_SESSION['cargo'] ?? '';

if ($cargo === 'INSTRUCTOR') {
    header("Location: index.php?page=register_tables");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Zonas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Tailwind y SweetAlert deben estar cargados en tu layout principal -->
  <style>
    /* Scrollbar bonito (opcional) */
    #wrapTablaZonas::-webkit-scrollbar { width: 8px; }
    #wrapTablaZonas::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.15); border-radius: 10px; }
    #wrapTablaZonas:hover::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.25); }

    /* ============================
       SOPORTE PARA LÁPIZ / ACCIONES
       ============================ */
    .btn-editar {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .btn-editar:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      pointer-events: none;
    }

    /* Contenedor de acciones en la última columna */
    #tablaInstructores td:last-child > div {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: nowrap;
    }

    /* ============================
       RESPONSIVE EXTRA
       ============================ */
    @media (max-width: 640px) {
      /* Header de la card: título + botón se apilan */
      .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }

      /* Botón "Nueva Zona" ocupa ancho completo en móvil */
      #btnAbrirModalZonas {
        width: 100%;
        justify-content: center;
      }

      /* Filtros se apilan en móvil */
      .filtros-container {
        flex-direction: column;
        width: 100%;
      }
      
      .filtros-container > div {
        width: 100%;
      }

      /* Ajustar paddings de la tabla para pantallas pequeñas */
      #tablaInstructores th,
      #tablaInstructores td {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
      }

      /* Dar más espacio a la columna Acciones */
      #tablaInstructores th:last-child,
      #tablaInstructores td:last-child {
        width: 120px;
        white-space: nowrap;
      }

      /* Altura máxima del wrapper y scroll vertical interno en móvil */
      #wrapTablaZonas {
        max-height: 320px;
        overflow-y: auto;
      }
    }

    /* Mismo espacio a la izquierda que el filtro de áreas (1.25rem) */
    #buscadorZonas {
      padding-left: 1.25rem !important;
    }

    /* Sombra gris en títulos de tabla (estándar con Áreas) */
    #tablaInstructores thead th {
      box-shadow: 0 1px 0 0 #e5e7eb;
    }

    /* Combobox filtro área: estilo cuando está abierto, chevron dentro */
    .combobox-zona-wrapper {
      position: relative;
      width: 100%;
      min-width: 0;
    }
    .combobox-zona-wrapper .combobox-zona-input {
      width: 100%;
      min-width: 0;
      padding-left: 1.25rem;
      padding-right: 2.25rem;
      box-sizing: border-box;
      border: none;
      border-radius: 0;
      background: transparent;
      color: #111827;
      font-size: inherit;
      outline: none;
    }
    .combobox-zona-wrapper .combobox-zona-input::placeholder {
      color: #9ca3af;
    }
    .combobox-zona-wrapper .combobox-zona-input:focus {
      outline: none;
      box-shadow: none;
    }
    .combobox-zona-wrapper .btn-clear-combobox-zona {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.25rem;
      height: 1.25rem;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 0;
      border: none;
      background: transparent;
      color: #6b7280;
      cursor: pointer;
      border-radius: 9999px;
    }
    .combobox-zona-wrapper .btn-clear-combobox-zona:hover {
      color: #374151;
      background: #f3f4f6;
    }
    .combobox-zona-wrapper .btn-clear-combobox-zona.visible {
      display: flex;
    }
    .combobox-zona-wrapper .combobox-zona-trigger:focus-within {
      border-color: #39A900;
      box-shadow: 0 0 0 3px rgba(57,169,0,0.2);
    }
    .combobox-zona-wrapper .combobox-zona-trigger {
      display: flex;
      align-items: center;
      position: relative;
      width: 100%;
      min-width: 0;
    }
    .combobox-zona-wrapper .combobox-zona-trigger .chevron-combobox-zona {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.25rem;
      height: 1.25rem;
      pointer-events: none;
      color: #6b7280;
    }
    .combobox-zona-wrapper.has-value .chevron-combobox-zona {
      display: none;
    }
    .combobox-zona-wrapper .combobox-zona-dropdown {
      position: absolute;
      left: 0;
      right: 0;
      top: 100%;
      margin-top: 6px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
      background: white;
      max-height: calc(5 * 2.5rem);
      overflow-y: auto;
      z-index: 100;
    }
    .combobox-zona-wrapper .combobox-zona-dropdown .combobox-zona-option {
      padding: 10px 14px;
      cursor: pointer;
      transition: background 0.15s;
    }
    .combobox-zona-wrapper .combobox-zona-dropdown .combobox-zona-option:hover { background: #f3f4f6; }
    .combobox-zona-wrapper .combobox-zona-dropdown .combobox-zona-option.selected {
      background: rgba(57, 169, 0, 0.1);
      color: #0a3a57;
    }

    /* Última fila: que el combobox reciba eventos y no se recorte (scroll) */
    #tablaInstructores tbody tr:last-child td:nth-child(2) {
      overflow: visible;
      position: relative;
      z-index: 1;
    }
    #tablaInstructores tbody tr:last-child .select-zona-wrapper {
      position: relative;
      z-index: 2;
    }
    /* Select de área (modal e inline): mismo estilo que editar usuario */
    .select-zona-wrapper {
      position: relative;
      width: 100%;
    }
    .select-zona-wrapper .select-zona-combobox-trigger {
      display: flex;
      align-items: center;
      position: relative;
      width: 100%;
      min-width: 0;
      box-sizing: border-box;
      pointer-events: auto;
      z-index: 50;
    }
    .select-zona-dropdown {
      position: absolute;
      left: 0;
      right: 0;
      top: 100%;
      margin-top: 6px;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
      background: white;
      max-height: calc(4 * 2.5rem);
      overflow-y: auto;
      z-index: 100;
    }
    .select-zona-dropdown.select-zona-dropdown-over-table {
      position: fixed;
      z-index: 9999;
      margin-top: 0;
      margin-bottom: 0;
      right: auto;
    }
    .select-zona-wrapper .select-zona-combobox-input {
      width: 100%;
      min-width: 0;
      padding-left: 0.75rem;
      padding-right: 2.25rem;
      border: none;
      background: transparent;
      color: #111827;
      font-size: inherit;
      outline: none;
    }
    /* Celda y combobox en tabla: alinear a la izquierda para evitar centrado al cerrar/X */
    #tablaInstructores tbody td .select-zona-wrapper {
      text-align: left;
    }
    #tablaInstructores tbody td .select-zona-wrapper .select-zona-combobox-trigger,
    #tablaInstructores tbody td .select-zona-wrapper .select-zona-combobox-input {
      text-align: left !important;
    }
    .select-zona-wrapper .select-zona-combobox-input::placeholder { color: #9ca3af; }
    .select-zona-wrapper .select-zona-combobox-trigger .select-zona-chevron-inner {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.25rem;
      height: 1.25rem;
      pointer-events: none;
      color: #6b7280;
    }
    .select-zona-wrapper.has-value-zona .select-zona-chevron-inner { display: none; }
    .select-zona-wrapper .btn-clear-zona {
      position: absolute;
      right: 0.5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1.25rem;
      height: 1.25rem;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 0;
      border: none;
      background: transparent;
      color: #6b7280;
      cursor: pointer;
      border-radius: 9999px;
    }
    .select-zona-wrapper .btn-clear-zona:hover { color: #374151; background: #f3f4f6; }
    .select-zona-wrapper .btn-clear-zona.visible { display: flex; }
    .select-zona-dropdown .select-zona-option {
      padding: 10px 14px;
      cursor: pointer;
      transition: background 0.15s;
    }
    .select-zona-dropdown .select-zona-option:hover { background: #f3f4f6; }
    .select-zona-dropdown .select-zona-option.selected {
      background: rgba(57, 169, 0, 0.1);
      color: #0a3a57;
    }
  </style>
</head>
<body class="bg-white text-gray-900 font-sans">

  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Encabezado principal -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Zonas</h1>
    <p class="text-gray-500 mb-6">Administra las zonas</p>

    <!-- Card principal -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Header card con título y botón (sin línea separadora) -->
      <div class="card-header flex items-center justify-between p-6">
        <h2 class="text-xl font-semibold">Todas las áreas</h2>
        <button 
          id="btnAbrirModalZonas"
          class="bg-[#0a3a57] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00304D] active:scale-[0.99] transition"
          type="button"
        >
          <span class="text-sm font-medium">+ Nueva Zona</span>
        </button>
      </div>

      <!-- Filtros (estilo estándar: sin línea horizontal) -->
      <div class="filtros-container flex flex-col md:flex-row md:items-center gap-4 px-6 py-4">
        <div id="filtroAreaWrap" class="relative w-full md:w-64">
          <select 
            id="filtroArea"
            class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition hover:border-gray-400"
          >
            <option value="todas">Todas las áreas</option>
            <!-- Las áreas se cargarán dinámicamente desde la BD -->
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none filtro-area-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="relative w-full md:w-64">
          <input 
            type="text"
            id="buscadorZonas"
            placeholder="Buscar zona..."
            class="w-full rounded-xl border border-gray-300 bg-white pl-5 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition hover:border-gray-400"
          />
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>

      <!-- 🟩 Contenedor con scroll interno -->
      <div id="wrapTablaZonas" class="overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="tablaInstructores">
            <thead class="bg-gray-50">
              <tr class="text-gray-600 text-sm border-b">
                <th class="px-6 py-3 font-medium">Nombre Zona</th>
                <th class="px-6 py-3 font-medium text-center">Área</th>
                <th class="px-6 py-3 font-medium text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <!-- Las filas se renderizan por JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== MODAL: Nueva Zona (sin cambios) ========== -->
  <div id="modalZonas" class="fixed inset-0 z-50 hidden">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] opacity-0 transition-opacity duration-200"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-[720px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 lg:p-10 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        
        <button id="btnCerrarModalZonas"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition"
          type="button">✕</button>

        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold">Nueva Zona</h3>
            <p class="text-gray-400 mt-1">Ingresa el número y el área de la nueva zona</p>
          </div>

          <form id="formNuevaZona" class="space-y-6">
            <!-- N° Zona -->
            <div class="space-y-2">
              <label for="id_zona" class="block text-sm font-semibold">Número de la Zona</label>
              <input id="id_zona" name="id_zona" type="number" placeholder="Ej: 1"
                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm placeholder:text-gray-400
                       focus:ring-0 focus:outline-none focus:border-gray-300" />
            </div>

            <!-- Área -->
            <div class="space-y-2">
              <label for="id_area" class="block text-sm font-semibold">Área perteneciente</label>
              <div id="id_areaWrap" class="relative">
                <select id="id_area" name="id_area" class="select-zona w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm focus:ring-0 focus:outline-none focus:border-gray-300">
                  <option disabled selected value="">Cargando áreas...</option>
                </select>
                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 select-zona-chevron" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>

            <!-- Acciones -->
            <div class="pt-2 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalZonas"
                class="px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#0a3a57] text-white hover:bg-[#00304D] transition">
                Crear Zona
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- Inyecta la URL base para el JS -->
  <script>
    window.BASE_URL = "<?= BASE_URL ?? '' ?>";
  </script>

  <!-- Script principal (sin cambios) -->
  <script>
    (() => {
      // =======================
      // CONFIGURACIÓN GLOBAL
      // =======================
      const API_URL = (window.BASE_URL || '') + "src/controllers/ZonaController.php";
      const API_AREA_URL = (window.BASE_URL || '') + "src/controllers/AreaController.php";

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
      const tabla = document.querySelector("#tablaInstructores");
      const tablaBody = document.querySelector("#tablaInstructores tbody");
      const inputZona = document.getElementById("id_zona");
      const wrapTabla = document.getElementById("wrapTablaZonas") || document.getElementById("wrapTabla");

      /** Fila actualmente en modo edición (solo una a la vez). Se limpia al guardar o cancelar. */
      let filaEnEdicion = null;

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
      // FUNCIONES AUXILIARES
      // =======================
      function normalizarTexto(texto) {
        return texto
          .toLowerCase()
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .trim();
      }

      function esErrorDuplicado(mensajeCrudo = "") {
        const msg = String(mensajeCrudo).toLowerCase();
        return (
          msg.includes("duplicate entry") ||
          msg.includes("ya existe una zona") ||
          msg.includes("1062") ||
          msg.includes("sqlstate[23000]")
        );
      }

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
        document.body.style.overflow = "";
        document.body.classList.remove("overflow-hidden");
        if (typeof resetZonaComboboxes === "function") resetZonaComboboxes();
        filaEnEdicion = null;
        if (tablaBody && tablaBody.querySelector(".btn-guardar")) {
          cargarZonas().then(() => ajustarAltoTablaZonas());
        }
        setTimeout(() => modal?.classList.add("hidden"), 200);
        formZona?.reset();
      };

      openBtn?.addEventListener("click", openModal);
      closeBtn?.addEventListener("click", closeModal);
      cancelBtn?.addEventListener("click", closeModal);
      backdrop?.addEventListener("click", (e) => {
        if (e.target === backdrop) closeModal();
      });

      inputZona?.addEventListener("input", (e) => {
        let val = e.target.value;
        val = val.replace(/[^0-9]/g, "");
        if (val.length > 1 && val.startsWith("0")) val = val.replace(/^0+/, "");
        if (val.length > 4) val = val.slice(0, 4);
        e.target.value = val;
      });

      function ajustarAltoTablaZonas() {
        if (!wrapTabla || !tabla) return;
        const thead = tabla.querySelector("thead");
        const firstRow = tabla.querySelector("tbody tr");
        const filas = tabla.querySelectorAll("tbody tr").length;
        const headH = thead ? thead.getBoundingClientRect().height : 44;
        const rowH = firstRow ? firstRow.getBoundingClientRect().height : 56;
        const maxFilas = 5;
        const maxH = headH + rowH * maxFilas;
        wrapTabla.style.maxHeight = filas > maxFilas ? `${Math.ceil(maxH)}px` : "";
        wrapTabla.style.overflowY = filas > maxFilas ? "auto" : "visible";
        wrapTabla.style.overscrollBehavior = filas > maxFilas ? "contain" : "";
        wrapTabla.style.paddingBottom = "0";
      }
      window.addEventListener("resize", ajustarAltoTablaZonas);

      // =======================
      // CARGAR ÁREAS PARA FILTRO (SOLO LAS QUE TIENEN ZONAS)
      // =======================
      async function cargarAreasParaFiltro() {
        const filtroArea = document.getElementById("filtroArea");
        if (!filtroArea) return;
        
        try {
          const [resZonas, resAreas] = await Promise.all([
            fetch(`${API_URL}?accion=listar`),
            fetch(`${API_AREA_URL}?accion=listar`)
          ]);
          
          const jsonZonas = await resZonas.json();
          const jsonAreas = await resAreas.json();

          if (jsonZonas.status === "success" && Array.isArray(jsonZonas.data) && 
              jsonAreas.status === "success" && Array.isArray(jsonAreas.data)) {
            
            const areasConZonas = new Set(
              jsonZonas.data.map(z => String(z.id_area)).filter(id => id)
            );

            const areasFiltradas = jsonAreas.data.filter(area =>
              areasConZonas.has(String(area.id_area))
            );
            
            filtroArea.innerHTML = `<option value="todas">Todas las áreas</option>`;
            
            if (areasFiltradas.length > 0) {
              const areasUnicas = new Set();
              areasFiltradas.forEach((area) => {
                const nombreArea = area.nombre_area.trim();
                if (!areasUnicas.has(nombreArea)) {
                  areasUnicas.add(nombreArea);
                  const option = document.createElement("option");
                  option.value = area.id_area;
                  option.textContent = nombreArea;
                  filtroArea.appendChild(option);
                }
              });
            }
          } else {
            filtroArea.innerHTML = `<option value="todas">Todas las áreas</option>`;
          }
        } catch (err) {
          console.error("Error al cargar áreas para filtro:", err);
          filtroArea.innerHTML = `<option value="todas">Todas las áreas</option>`;
        }
        enhanceComboboxFiltroArea();
      }

      function enhanceComboboxFiltroArea() {
        const select = document.getElementById("filtroArea");
        const container = document.getElementById("filtroAreaWrap");
        if (!select || !container || select.dataset.comboboxZona === "1") return;
        select.dataset.comboboxZona = "1";

        const wrapper = document.createElement("div");
        wrapper.className = "combobox-zona-wrapper";

        const triggerWrap = document.createElement("div");
        triggerWrap.className = "combobox-zona-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm";
        const input = document.createElement("input");
        input.type = "text";
        input.autocomplete = "off";
        input.className = "combobox-zona-input w-full bg-transparent py-0 border-0 focus:ring-0 text-gray-900 placeholder:text-gray-400";
        input.placeholder = "Todas las áreas";
        const btnClear = document.createElement("button");
        btnClear.type = "button";
        btnClear.className = "btn-clear-combobox-zona";
        btnClear.setAttribute("aria-label", "Limpiar búsqueda");
        btnClear.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        const chevron = document.createElement("span");
        chevron.className = "chevron-combobox-zona";
        chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
        triggerWrap.appendChild(input);
        triggerWrap.appendChild(btnClear);
        triggerWrap.appendChild(chevron);

        const dropdown = document.createElement("div");
        dropdown.className = "combobox-zona-dropdown hidden";

        const optionsData = () => [...select.options].map(opt => ({ value: opt.value, text: (opt.textContent || "").trim() }));

        function renderOptions(filterText) {
          const q = (filterText || "").trim().toLowerCase();
          dropdown.innerHTML = "";
          optionsData().forEach(({ value, text }) => {
            if (q && !text.toLowerCase().includes(q)) return;
            const div = document.createElement("div");
            div.className = "combobox-zona-option" + (value === select.value ? " selected" : "");
            div.textContent = text;
            div.dataset.value = value;
            div.addEventListener("click", (e) => {
              e.stopPropagation();
              select.value = value;
              select.dispatchEvent(new Event("change", { bubbles: true }));
              input.value = value === "todas" ? "" : text;
              dropdown.classList.add("hidden");
              toggleClearVisibility();
            });
            dropdown.appendChild(div);
          });
          dropdown.classList.toggle("hidden", dropdown.children.length === 0);
        }

        function toggleClearVisibility() {
          const hasText = (input.value || "").trim().length > 0;
          wrapper.classList.toggle("has-value", hasText);
          btnClear.classList.toggle("visible", hasText);
        }

        function updateInputFromSelect() {
          if (select.value === "todas") {
            input.value = "";
          } else {
            const opt = select.options[select.selectedIndex];
            input.value = opt ? (opt.textContent || "").trim() : "";
          }
          toggleClearVisibility();
        }
        input.value = "";
        toggleClearVisibility();

        triggerWrap.addEventListener("click", (e) => {
          if (e.target === input) return;
          e.stopPropagation();
          const isHidden = dropdown.classList.contains("hidden");
          if (isHidden) {
            renderOptions(input.value);
          } else {
            dropdown.classList.add("hidden");
          }
        });
        input.addEventListener("focus", () => { renderOptions(input.value); });
        input.addEventListener("input", () => {
          renderOptions(input.value);
          toggleClearVisibility();
        });
        input.addEventListener("keydown", (e) => {
          if (e.key === "Escape") { dropdown.classList.add("hidden"); input.blur(); }
        });
        btnClear.addEventListener("click", (e) => {
          e.stopPropagation();
          input.value = "";
          select.value = "todas";
          select.dispatchEvent(new Event("change", { bubbles: true }));
          dropdown.classList.add("hidden");
          toggleClearVisibility();
          input.focus();
        });
        select.addEventListener("change", updateInputFromSelect);

        document.addEventListener("click", (e) => {
          if (!wrapper.contains(e.target)) dropdown.classList.add("hidden");
        }, true);

        select.classList.add("sr-only", "absolute", "opacity-0", "pointer-events-none", "overflow-hidden");
        select.style.cssText = "position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);";
        container.querySelector(".filtro-area-chevron")?.setAttribute("style", "display: none;");
        container.insertBefore(wrapper, select);
        wrapper.appendChild(triggerWrap);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(select);
      }

      // =======================
      // CARGAR ÁREAS PARA MODAL (TODAS)
      // =======================
      async function cargarAreasParaModal() {
        const selectArea = document.getElementById("id_area");
        if (!selectArea) return;
        
        try {
          const res = await fetch(`${API_AREA_URL}?accion=listar`);
          const json = await res.json();

          if (json.status === "success" && Array.isArray(json.data) && json.data.length > 0) {
            selectArea.innerHTML = `<option disabled selected value="">Seleccione un Área</option>`;
            const areasUnicas = new Set();
            json.data.forEach((area) => {
              const nombreArea = area.nombre_area.trim();
              if (!areasUnicas.has(nombreArea)) {
                areasUnicas.add(nombreArea);
                const option = document.createElement("option");
                option.value = area.id_area;
                option.textContent = nombreArea;
                selectArea.appendChild(option);
              }
            });
          } else {
            selectArea.innerHTML = `<option disabled selected value="">No hay áreas disponibles</option>`;
          }
        } catch (err) {
          console.error("Error al cargar áreas para modal:", err);
          selectArea.innerHTML = `<option disabled selected value="">Error al cargar áreas</option>`;
        }
        enhanceSelectsZona();
      }

      function resetZonaComboboxes() {
        document.querySelectorAll(".select-zona-dropdown").forEach((d) => {
          d.classList.add("hidden");
          d.classList.remove("select-zona-dropdown-over-table");
          d.style.cssText = "";
          if (d._selectZonaWrapper && d.parentNode === document.body) d._selectZonaWrapper.appendChild(d);
        });
      }

      function enhanceSelectsZona() {
        document.querySelectorAll(".select-zona").forEach((select) => {
          if (select.dataset.customDropdownZona === "1") return;
          select.dataset.customDropdownZona = "1";
          const container = select.parentNode;
          const wrapper = document.createElement("div");
          wrapper.className = "select-zona-wrapper";
          container.insertBefore(wrapper, select);
          wrapper.appendChild(select);
          const oldChevron = container.querySelector(".select-zona-chevron") || container.querySelector("svg");
          if (oldChevron) oldChevron.style.display = "none";

          const triggerWrap = document.createElement("div");
          triggerWrap.className = "select-zona-combobox-trigger w-full border border-gray-300 rounded-xl bg-white hover:border-gray-400 focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] py-2.5 text-sm";
          const input = document.createElement("input");
          input.type = "text";
          input.autocomplete = "off";
          input.className = "select-zona-combobox-input w-full bg-transparent py-0 border-0 focus:ring-0 text-gray-900 placeholder:text-gray-400";
          input.placeholder = "Buscar área...";
          const btnClear = document.createElement("button");
          btnClear.type = "button";
          btnClear.className = "btn-clear-zona";
          btnClear.setAttribute("aria-label", "Limpiar");
          btnClear.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
          const chevronInner = document.createElement("span");
          chevronInner.className = "select-zona-chevron-inner";
          chevronInner.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
          triggerWrap.appendChild(input);
          triggerWrap.appendChild(btnClear);
          triggerWrap.appendChild(chevronInner);

          const dropdown = document.createElement("div");
          dropdown.className = "select-zona-dropdown hidden";
          dropdown.setAttribute("role", "listbox");
          dropdown._selectZonaWrapper = wrapper;

          const optionsData = () => [...select.options].filter(opt => !opt.disabled).map(opt => ({ value: opt.value, text: (opt.textContent || "").trim() }));

          function renderOptions(filterText) {
            const q = (filterText || "").trim().toLowerCase();
            dropdown.innerHTML = "";
            optionsData().forEach(({ value, text }) => {
              if (q && !text.toLowerCase().includes(q)) return;
              const div = document.createElement("div");
              div.className = "select-zona-option" + (value === select.value ? " selected" : "");
              div.textContent = text;
              div.dataset.value = value;
              div.setAttribute("role", "option");
              div.addEventListener("click", (e) => {
                e.stopPropagation();
                select.value = value;
                select.dispatchEvent(new Event("change", { bubbles: true }));
                input.value = text;
                dropdown.classList.add("hidden");
                if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
                toggleClearVisibility();
              });
              dropdown.appendChild(div);
            });
            dropdown.classList.toggle("hidden", dropdown.children.length === 0);
          }

          function toggleClearVisibility() {
            const hasText = (input.value || "").trim().length > 0;
            wrapper.classList.toggle("has-value-zona", hasText);
            btnClear.classList.toggle("visible", hasText);
          }

          function updateInputFromSelect() {
            const opt = select.options[select.selectedIndex];
            if (!opt || opt.disabled || !opt.value) {
              input.value = "";
            } else {
              input.value = (opt.textContent || "").trim();
            }
            toggleClearVisibility();
          }
          updateInputFromSelect();
          wrapper._selectZonaUpdateInput = updateInputFromSelect;

          select.classList.add("sr-only", "absolute", "opacity-0", "pointer-events-none", "overflow-hidden");
          select.style.cssText = "position:absolute;width:1px;height:1px;margin:-1px;padding:0;border:0;clip:rect(0,0,0,0);";
          wrapper.appendChild(triggerWrap);
          wrapper.appendChild(dropdown);

          function positionAndShowDropdown(forceShowAll) {
            const opts = 4;
            const maxH = opts * 2.5 * parseFloat(getComputedStyle(document.documentElement).fontSize);
            const gap = 4;
            const isInTable = wrapper.closest("#wrapTablaZonas") || wrapper.closest("table");

            const filterText = (forceShowAll || wrapper._reopenShowAll) ? "" : input.value;
            if (wrapper._reopenShowAll) delete wrapper._reopenShowAll;
            renderOptions(filterText);
            if (dropdown.children.length === 0) return;

            function applyPosition(rect) {
              const spaceBelow = window.innerHeight - rect.bottom;
              const spaceAbove = rect.top;
              const inBottomThird = rect.top >= window.innerHeight * (2 / 3);
              const tr = wrapper.closest("tr");
              const tbody = wrapper.closest("#tablaInstructores tbody");
              const isLastRow = tbody && tr && tbody.lastElementChild === tr;
              const forceDropup = inBottomThird || isLastRow;
              const openDown = !forceDropup && (spaceBelow >= maxH + gap);

              dropdown.style.width = rect.width + "px";
              dropdown.style.position = "fixed";
              dropdown.style.zIndex = "9999";
              dropdown.style.marginTop = "0";
              dropdown.style.marginBottom = "0";
              dropdown.style.left = rect.left + "px";

              if (openDown) {
                dropdown.style.maxHeight = Math.min(maxH, Math.max(60, spaceBelow - gap)) + "px";
                dropdown.style.removeProperty("bottom");
                dropdown.style.minHeight = "";
                dropdown.style.top = (rect.bottom + gap) + "px";
              } else {
                const upMaxH = Math.min(maxH, Math.max(60, spaceAbove - gap));
                dropdown.style.maxHeight = upMaxH + "px";
                dropdown.style.removeProperty("top");
                dropdown.style.bottom = (window.innerHeight - rect.top) + "px";
              }
            }

            if (isInTable) {
              resetZonaComboboxes();
              dropdown.style.visibility = "hidden";
              dropdown.style.display = "block";
              document.body.appendChild(dropdown);
              dropdown.classList.add("select-zona-dropdown-over-table");
              requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                  const rect = triggerWrap.getBoundingClientRect();
                  applyPosition(rect);
                  dropdown.style.visibility = "visible";
                  dropdown.classList.remove("hidden");
                  dropdown._selectZonaJustOpened = Date.now();
                });
              });
            } else {
              dropdown.style.maxHeight = maxH + "px";
              dropdown.classList.remove("hidden");
              dropdown._selectZonaJustOpened = Date.now();
            }
          }

          function openDropdownFromTrigger(ev) {
            if (ev) { ev.preventDefault(); ev.stopPropagation(); }
            const open = !dropdown.classList.contains("hidden");
            if (!open) {
              positionAndShowDropdown();
              setTimeout(function () { input.focus(); }, 0);
            }
          }
          wrapper._selectZonaOpen = openDropdownFromTrigger;
          triggerWrap.addEventListener("mousedown", openDropdownFromTrigger);
          triggerWrap.addEventListener("click", (e) => { e.preventDefault(); e.stopPropagation(); });
          input.addEventListener("focus", () => {
            resetZonaComboboxes();
            positionAndShowDropdown();
          });
          input.addEventListener("input", () => { renderOptions(input.value); toggleClearVisibility(); });
          input.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
              dropdown.classList.add("hidden");
              if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
              input.blur();
            }
          });
          btnClear.addEventListener("click", (e) => {
            e.stopPropagation();
            e.preventDefault();
            input.value = "";
            const firstValid = Array.from(select.options).find(o => !o.disabled && o.value);
            if (firstValid) { select.value = firstValid.value; select.dispatchEvent(new Event("change", { bubbles: true })); }
            dropdown.classList.add("hidden");
            if (dropdown.parentNode === document.body) wrapper.appendChild(dropdown);
            toggleClearVisibility();
            wrapper._reopenShowAll = true;
            requestAnimationFrame(() => {
              positionAndShowDropdown();
              setTimeout(() => input.focus(), 0);
            });
          });
          select.addEventListener("change", updateInputFromSelect);
        });

        if (!window._zonaComboboxDocClick) {
          window._zonaComboboxDocClick = true;
          function closeZonaDropdownIfOutside(target) {
            document.querySelectorAll(".select-zona-dropdown").forEach((d) => {
              if (d.classList.contains("hidden")) return;
              const w = d._selectZonaWrapper;
              if (!w || w.contains(target) || d.contains(target)) return;
              if (d._selectZonaJustOpened && (Date.now() - d._selectZonaJustOpened) < 250) return;
              d.classList.add("hidden");
              if (d.parentNode === document.body && w) w.appendChild(d);
              d.style.cssText = "";
              const inp = w && w.querySelector(".select-zona-combobox-input");
              if (inp && !(inp.value || "").trim() && typeof w._selectZonaUpdateInput === "function") w._selectZonaUpdateInput();
            });
          }
          function onPossibleOutsideClick(e) {
            closeZonaDropdownIfOutside(e.target);
          }
          document.addEventListener("mousedown", onPossibleOutsideClick, true);
          document.addEventListener("click", onPossibleOutsideClick, true);
          const modalPanel = document.getElementById("modalPanel");
          const modalBackdrop = document.getElementById("modalBackdrop");
          if (modalPanel) {
            modalPanel.addEventListener("mousedown", onPossibleOutsideClick, true);
            modalPanel.addEventListener("click", onPossibleOutsideClick, true);
          }
          if (modalBackdrop) {
            modalBackdrop.addEventListener("mousedown", onPossibleOutsideClick, true);
            modalBackdrop.addEventListener("click", onPossibleOutsideClick, true);
          }
        }
      }

      (function () {
        var tableEl = document.getElementById("tablaInstructores");
        if (tableEl) {
          tableEl.addEventListener("mousedown", function (e) {
            var trigger = e.target.closest(".select-zona-combobox-trigger");
            if (!trigger) return;
            var wrapper = trigger.closest(".select-zona-wrapper");
            if (wrapper && typeof wrapper._selectZonaOpen === "function") {
              wrapper._selectZonaOpen(e);
              e.stopPropagation();
              e.preventDefault();
            }
          }, true);
        }
      })();

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

            tablaBody.innerHTML = json.data.map((z) => `
              <tr data-id="${z.id_zona}" data-id-area="${z.id_area ?? ""}" data-area-nombre="${z.nombre_area}" class="border-b">
                <td class="px-6 py-4">${z.id_zona}</td>
                <td class="px-6 py-4 text-center">
                  <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
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
                      <div class="w-11 h-6 bg-gray-200 rounded-full transition peer-checked:bg-[#39A900]"></div>
                      <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition peer-checked:translate-x-5"></div>
                    </label>
                  </div>
                </td>
              </tr>
            `).join("");

            ajustarAltoTablaZonas();
            aplicarFiltrosCombinados();
          } else {
            tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">${json.message || "Error al listar"}</td></tr>`;
            ajustarAltoTablaZonas();
          }
        } catch (err) {
          console.error("Error al cargar zonas:", err);
          tablaBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Error al cargar zonas</td></tr>`;
          ajustarAltoTablaZonas();
        }
      }

      // =======================
      // FILTROS
      // =======================
      function aplicarFiltrosCombinados() {
        const filtroArea = document.getElementById('filtroArea');
        const buscador = document.getElementById('buscadorZonas');

        const areaSeleccionada = filtroArea?.value || 'todas';
        const terminoBusqueda = buscador?.value.toLowerCase().trim() || '';
        const filas = document.querySelectorAll('#tablaInstructores tbody tr');
        let filasVisibles = 0;

        document.getElementById('fila-no-resultados')?.remove();

        filas.forEach(fila => {
          if (fila.children.length === 1) return;

          const numeroZona = fila.children[0]?.textContent.toLowerCase() || '';
          const areaSpan = fila.querySelector('td:nth-child(2) span');
          const areaTexto = areaSpan ? areaSpan.textContent : '';
          const areaFila = fila.dataset.idArea;

          const coincideArea =
            areaSeleccionada === 'todas' ||
            String(areaFila) === String(areaSeleccionada);

          const coincideBusqueda =
            terminoBusqueda === '' ||
            numeroZona.includes(terminoBusqueda) ||
            normalizarTexto(areaTexto).includes(terminoBusqueda);

          const mostrar = coincideArea && coincideBusqueda;
          fila.style.display = mostrar ? '' : 'none';

          if (mostrar) filasVisibles++;
        });

        if (filasVisibles === 0) {
          const tbody = document.querySelector('#tablaInstructores tbody');
          const fila = document.createElement('tr');
          fila.id = 'fila-no-resultados';
          fila.innerHTML = `
            <td colspan="3" class="text-center p-4 text-gray-500">
              No se encontraron zonas
            </td>`;
          tbody.appendChild(fila);
        }
      }

      function inicializarFiltros() {
        const filtroArea = document.getElementById('filtroArea');
        const buscador = document.getElementById('buscadorZonas');
        if (filtroArea) filtroArea.addEventListener('change', aplicarFiltrosCombinados);
        if (buscador) buscador.addEventListener('input', aplicarFiltrosCombinados);
      }

      // =======================
      // CREAR ZONA
      // =======================
      formZona?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id_zona = formZona.id_zona?.value?.trim();
        const id_area = formZona.id_area?.value?.trim();

        if (!id_zona || !id_area) {
          Toast.fire({ icon: "warning", title: "Debes ingresar el número de zona y seleccionar un área." });
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
            Toast.fire({ icon: "success", title: json.message || "Zona creada correctamente." });
            closeModal();
            await Promise.all([cargarZonas(), cargarAreasParaFiltro()]);
            ajustarAltoTablaZonas();
          } else {
            Toast.fire({
              icon: esErrorDuplicado(json.message) ? "warning" : "error",
              title: json.message || "No se pudo crear la zona."
            });
          }
        } catch (err) {
          console.error("Error al crear zona:", err);
          Toast.fire({ icon: "error", title: "Error al crear la zona." });
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
          Toast.fire({ icon: "error", title: "No se pudo identificar la zona." });
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
            title: json.message || (json.status === "success" ? "Estado actualizado" : "Error al actualizar")
          });
        } catch (err) {
          console.error("Error al cambiar estado:", err);
          Toast.fire({ icon: "error", title: "Error al cambiar el estado." });
        }
      });

      // =======================
      // EDITAR ZONA
      // =======================
      tablaBody?.addEventListener("click", async (e) => {
        const btnEditar = e.target.closest(".btn-editar");
        if (!btnEditar) return;

        const tr = btnEditar.closest("tr");
        if (!tr) return;

        if (filaEnEdicion !== null && filaEnEdicion !== tr) {
          Toast.fire({ icon: "info", title: "Por favor, guarda o cancela los cambios actuales antes de editar otra zona." });
          return;
        }

        const id_zona_actual = tr?.dataset?.id;
        const id_area_actual = tr?.dataset?.idArea;
        const tdZona = tr.children[0];
        const tdArea = tr.children[1];
        const tdAcc = tr.children[2];
        const zonaOriginal = tdZona.textContent.trim();
        const areaOriginal = tdArea.textContent.trim();

        let opcionesHTML = `<option disabled selected value="">Cargando áreas...</option>`;
        try {
          const res = await fetch(`${API_AREA_URL}?accion=listar`);
          const json = await res.json();

          if (json.status === "success" && Array.isArray(json.data)) {
            const areasUnicas = new Set();
            opcionesHTML = json.data
              .filter(a => {
                const nombre = a.nombre_area.trim();
                if (!areasUnicas.has(nombre)) {
                  areasUnicas.add(nombre);
                  return true;
                }
                return false;
              })
              .map(a => `<option value="${a.id_area}" ${a.nombre_area.trim() === areaOriginal.trim() ? "selected" : ""}>${a.nombre_area.trim()}</option>`)
              .join("");
          }
        } catch (err) {
          console.error("Error al cargar áreas:", err);
        }

        tdZona.innerHTML = `<input type="number" value="${zonaOriginal}" class="w-20 rounded-lg border border-gray-200 px-3 py-2 text-center focus:outline-none focus:border-gray-300">`;
        tdArea.innerHTML = `
          <div class="relative max-w-[220px] mx-auto">
            <select class="select-zona w-full appearance-none rounded-lg border border-gray-200 bg-white px-3 py-2 pr-8 focus:outline-none focus:border-gray-300">
              ${opcionesHTML}
            </select>
            <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 select-zona-chevron" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
          </div>`;
        tdAcc.innerHTML = `
          <button class="btn-guardar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-green-600 text-green-600 hover:bg-green-50 transition">Guardar</button>
          <button class="btn-cancelar inline-flex items-center gap-2 px-5 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</button>
        `;

        filaEnEdicion = tr;
        tablaBody.querySelectorAll(".btn-editar").forEach((btn) => { btn.disabled = true; });

        tdAcc.querySelector(".btn-cancelar").addEventListener("click", async () => {
          filaEnEdicion = null;
          await cargarZonas();
          ajustarAltoTablaZonas();
        });

        enhanceSelectsZona();

        tdAcc.querySelector(".btn-guardar").addEventListener("click", async () => {
          const id_zona_nueva = tdZona.querySelector("input").value.trim();
          const id_area_nueva = tdArea.querySelector("select").value.trim();

          if (!id_zona_nueva || !id_area_nueva) {
            Toast.fire({ icon: "warning", title: "Completa todos los campos." });
            return;
          }

          if (id_zona_nueva === id_zona_actual && id_area_nueva === id_area_actual) {
            Toast.fire({ icon: "info", title: "No hay cambios. El número de zona y el área son los mismos que antes." });
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
            const json = await res.json();

            if (json.status === "success") {
              filaEnEdicion = null;
              Toast.fire({ icon: "success", title: "Zona actualizada." });
              await Promise.all([cargarZonas(), cargarAreasParaFiltro()]);
              ajustarAltoTablaZonas();
            } else {
              Toast.fire({
                icon: esErrorDuplicado(json.message) ? "warning" : "error",
                title: json.message || "Error al actualizar."
              });
            }
          } catch (err) {
            console.error("Error al actualizar:", err);
            Toast.fire({ icon: "error", title: "Error al actualizar." });
          }
        });
      });

      // =======================
      // INICIALIZAR
      // =======================
      async function inicializar() {
        await Promise.all([
          cargarZonas(),
          cargarAreasParaFiltro(),
          cargarAreasParaModal()
          
        ]);
        inicializarFiltros();
        ajustarAltoTablaZonas();
      }

      inicializar();
    })();
  </script>
</body>
</html>