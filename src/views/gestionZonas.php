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

    /* Estilo para el select de filtro */
    #filtroArea {
      cursor: pointer;
      width: 200px;
    }
    
    #filtroArea option {
      padding: 8px;
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
      <!-- Header card con título y botón -->
      <div class="card-header flex items-center justify-between p-6 border-b">
        <h2 class="text-xl font-semibold">Todas las áreas</h2>
        <button 
          id="btnAbrirModalZonas"
          class="bg-[#0a3a57] text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-[#00304D] active:scale-[0.99] transition"
          type="button"
        >
          <span class="text-sm font-medium">+ Nueva Zona</span>
        </button>
      </div>

      <!-- Filtros - AMBOS DEL MISMO TAMAÑO -->
      <div class="filtros-container flex items-center gap-4 p-6 border-b">
        <!-- SELECT "Todas las áreas" - MISMO TAMAÑO QUE BUSCAR ZONA -->
        <div class="relative w-[200px]">
          <select 
            id="filtroArea"
            class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-2 pr-8 text-sm focus:outline-none focus:border-gray-300"
          >
            <option value="todas">Todas las áreas</option>
            <!-- Las áreas se cargarán dinámicamente desde la BD -->
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.18l3.71-3.95a.75.75 0 111.08 1.04l-4.24 4.52a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </div>

        <!-- BUSCADOR - MISMO TAMAÑO QUE EL SELECT -->
        <div class="relative w-[200px]">
          <input 
            type="text"
            id="buscadorZonas"
            placeholder="Buscar zona..."
            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2 pr-8 text-sm focus:outline-none focus:border-gray-300"
          />
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
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
                <th class="px-6 py-3 font-medium">N° Zona</th>
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
              <div class="relative">
                <select id="id_area" name="id_area"
                  class="w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 py-3 pr-10 shadow-sm
                         focus:ring-0 focus:outline-none focus:border-gray-300">
                  <option disabled selected value="">Cargando áreas...</option>
                </select>
                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
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
        wrapTabla.style.maxHeight = `${Math.ceil(maxH)}px`;
        wrapTabla.style.overflowY = filas > maxFilas ? "auto" : "visible";
        wrapTabla.style.overscrollBehavior = "contain";
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
            Toast.fire({ icon: "warning", title: "Completa todos los campos." });
            return;
          }

          if (id_zona_nueva === id_zona_actual && id_area_nueva === id_area_actual) {
            Toast.fire({ icon: "warning", title: "No se detectaron cambios." });
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