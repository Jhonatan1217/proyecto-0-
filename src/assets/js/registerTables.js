// ===============================
// REGISTER TABLES - FUNCIONAL 2025 (ÁREAS, ZONAS, HORARIOS + TOASTS)
// ===============================

const urlParams = new URLSearchParams(window.location.search);
const id_zona = urlParams.get("id_zona");

// =======================
// CONFIGURACIÓN DE SWEETALERT TOAST
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
// UTILIDAD: Mostrar/Ocultar tabla y botones
// =======================
function toggleTabla(mostrar = true) {
  const tabla = document.querySelector(".tabla-trimestralizacion") || document.querySelector("table");
  const botones = document.querySelectorAll("button");

  if (tabla) tabla.style.display = mostrar ? "" : "none";
  botones.forEach((btn) => (btn.style.display = mostrar ? "" : "none"));
}

// =======================
// CARGAR ÁREAS Y ZONAS
// =======================
async function cargarAreasYZonas() {
  const selectArea = document.getElementById("selectArea");
  const selectZona = document.getElementById("selectZona");
  if (!selectArea || !selectZona) return;

  // Ocultar tabla al inicio
  toggleTabla(false);

  try {
    // 🔹 1. Cargar Áreas
    const resAreas = await fetch(`${BASE_URL}src/controllers/AreaController.php?accion=listar`);
    const dataAreas = await resAreas.json();

    if (dataAreas.status === "success" && Array.isArray(dataAreas.data)) {
      selectArea.innerHTML = `<option value="" hidden selected>SELECCIONE EL ÁREA</option>`;
      dataAreas.data.forEach((a) => {
        const opt = document.createElement("option");
        opt.value = a.id_area;
        opt.textContent = a.nombre_area;
        selectArea.appendChild(opt);
      });
    } else {
      Toast.fire({ icon: "warning", title: "No se encontraron áreas." });
    }

    // 🔹 2. Evento: cargar zonas según área seleccionada
    selectArea.addEventListener("change", async (e) => {
      const id_area = e.target.value;
      selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;

      // Ocultar tabla al cambiar de área
      toggleTabla(false);

      if (!id_area) return;

      try {
        const resZonas = await fetch(`${BASE_URL}src/controllers/ZonaController.php?accion=listarPorArea&id_area=${id_area}`);
        const dataZonas = await resZonas.json();

        if (dataZonas.status === "success" && Array.isArray(dataZonas.data)) {
          if (dataZonas.data.length === 0) {
            Toast.fire({ icon: "warning", title: "No hay zonas registradas en esta área." });
          } else {
            dataZonas.data.forEach((z) => {
              const opt = document.createElement("option");
              opt.value = z.id_zona;
              opt.textContent = `Zona ${z.id_zona} (${z.nombre_area || "Sin área"})`;
              selectZona.appendChild(opt);
            });

            Toast.fire({ icon: "success", title: "Zonas cargadas correctamente ✅" });
          }
        } else {
          Toast.fire({ icon: "warning", title: "No se pudieron obtener las zonas de esta área." });
        }
      } catch (err) {
        console.error("Error cargando zonas:", err);
        Toast.fire({ icon: "error", title: "Error al cargar las zonas del servidor." });
      }
    });

    // 🔹 3. Evento: cambiar zona = mostrar tabla
    selectZona.addEventListener("change", (e) => {
      const id_zona = e.target.value;

      if (!id_zona) {
        toggleTabla(false);
        return;
      }

      // Mostrar tabla solo si hay zona seleccionada
      toggleTabla(true);

      // Actualizar título principal
      const h1 = document.querySelector("#cabecera-trimestralizacion h1");
      if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}`;

      // Actualizar URL sin recargar
      const nuevaURL = new URL(window.location);
      nuevaURL.searchParams.set("id_zona", id_zona);
      window.history.replaceState({}, "", nuevaURL);

      // Cargar horarios/trimestralización
      cargarTrimestralizacion();

      Toast.fire({ icon: "info", title: `Zona ${id_zona} seleccionada` });
    });
  } catch (error) {
    console.error("Error cargando áreas y zonas:", error);
    Toast.fire({ icon: "error", title: "Error al cargar áreas o zonas." });
  }
}

// =======================
// CARGAR TRIMESTRALIZACIÓN
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  const urlParams = new URLSearchParams(window.location.search);
  const id_zona_actual = urlParams.get("id_zona");

  if (!id_zona_actual) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">⚠️ No se especificó la zona.</td></tr>`;
    toggleTabla(false);
    return;
  }

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=listar&id_zona=${id_zona_actual}`);
    const data = await res.json();
    tbody.innerHTML = "";

    const activos = Array.isArray(data)
      ? data.filter((d) => d && (d.estado === 1 || d.estado === "1" || d.estado === true || d.estado === "true"))
      : [];

    if (activos.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">No hay registros activos para esta zona.</td></tr>`;
      Toast.fire({ icon: "warning", title: "No hay registros activos para esta zona." });
      return;
    }

    const dias = ["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"];
    const horas = Array.from({ length: 16 }, (_, i) => i + 6);

    horas.forEach((hora, idx) => {
      const fila = document.createElement("tr");
      fila.className = idx % 2 === 0 ? "bg-gray-50" : "bg-white";
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}:00-${hora + 1}:00</td>`;

      dias.forEach((dia) => {
        const registros = activos.filter((r) => {
          if (!r.dia || r.dia.toUpperCase() !== dia) return false;
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStart + 1;
          return hora >= rStart && hora < rEnd;
        });

        let contenido = "";
        registros.forEach((r) => {
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStart + 1;
          if (hora === rStart) {
            contenido += `
              <div class="registro border-gray-300 pb-1 mb-1" data-id="${r.id_horario || ""}">
                <div><strong>Ficha:</strong> <span class="ficha">${r.numero_ficha ?? ""}</span>
                  (<span class="nivel_ficha">${(r.nivel_ficha ?? "").toUpperCase()}</span>)
                </div>
                <div><strong>Competencia:</strong> <span class="competencia">${r.descripcion ?? "Sin especificar"}</span></div>
              </div>`;
          } else if (hora > rStart && hora < rEnd) {
            contenido += `<div class="mb-1 border-gray-200 pb-1">
                <strong>Instructor:</strong> ${r.nombre_instructor ?? ""} (${r.tipo_instructor ?? ""})
              </div>`;
          }
        });

        fila.innerHTML += `
          <td class="border border-gray-700 p-2 text-sm text-left leading-tight">
            ${contenido || '<span class="text-gray-400 italic">zona libre</span>'}
          </td>`;
      });

      tbody.appendChild(fila);
    });

    Toast.fire({ icon: "success", title: "Trimestralización cargada correctamente ✅" });
  } catch (error) {
    console.error("Error al cargar trimestralización:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
    Toast.fire({ icon: "error", title: "Error al cargar la trimestralización." });
  }
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarAreasYZonas();
  toggleTabla(false); // 🔹 Ocultar tabla hasta que se elija zona
});
