// ===============================
// REGISTER TABLES - 2025 FINAL (ÁREAS/ZONAS + EDICIÓN + TOASTS + PDF + ELIMINAR)
// ===============================

const urlParams = new URLSearchParams(window.location.search);
let id_zona = urlParams.get("id_zona");

// =======================
// CONFIG TOAST (SweetAlert2)
// =======================
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  background: "#fff",
  color: "#000",
});

// =======================
// Mostrar/Ocultar tabla y botones
// =======================
function toggleTabla(mostrar = true) {
  const tabla = document.querySelector("#tabla-horarios");
  const botones = document.querySelector("#botones-principales");
  if (tabla) tabla.style.display = mostrar ? "" : "none";
  if (botones) botones.style.display = mostrar ? "flex" : "none";
}

// =======================
// CARGAR ÁREAS Y ZONAS
// =======================
async function cargarAreasYZonas() {
  const selectArea = document.getElementById("selectArea");
  const selectZona = document.getElementById("selectZona");

  if (!selectArea || !selectZona) return;
  toggleTabla(false);

  try {
    // Cargar ÁREAS
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
      Toast.fire({ icon: "warning", title: "No se encontraron áreas" });
    }

    // Cambiar área → cargar zonas
    selectArea.addEventListener("change", async (e) => {
      const id_area = e.target.value;
      selectZona.innerHTML = `<option value="" hidden selected>SELECCIONE LA ZONA</option>`;
      toggleTabla(false);
      if (!id_area) return;

      try {
        const resZonas = await fetch(`${BASE_URL}src/controllers/ZonaController.php?accion=listarPorArea&id_area=${id_area}`);
        const dataZonas = await resZonas.json();

        if (dataZonas.status === "success" && Array.isArray(dataZonas.data)) {
          if (dataZonas.data.length === 0) {
            Toast.fire({ icon: "info", title: "No hay zonas en esta área" });
            return;
          }
          dataZonas.data.forEach((z) => {
            const opt = document.createElement("option");
            opt.value = z.id_zona;
            opt.textContent = `Zona ${z.id_zona}`;
            selectZona.appendChild(opt);
          });
          Toast.fire({ icon: "success", title: "Zonas cargadas correctamente" });
        }
      } catch {
        Toast.fire({ icon: "error", title: "Error al cargar zonas" });
      }
    });

    // Cambiar zona → mostrar tabla
    selectZona.addEventListener("change", (e) => {
      id_zona = e.target.value;
      if (!id_zona) {
        toggleTabla(false);
        return;
      }
      const h1 = document.querySelector("#cabecera-trimestralizacion h1");
      if (h1) h1.innerHTML = `VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}`;
      toggleTabla(true);
      cargarTrimestralizacion();
      Toast.fire({ icon: "info", title: `Zona ${id_zona} seleccionada` });
    });
  } catch {
    Toast.fire({ icon: "error", title: "Error al conectar con el servidor" });
  }
}

// =======================
// CARGAR TRIMESTRALIZACIÓN
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  const selectArea = document.getElementById("selectArea");
  const id_area = selectArea ? selectArea.value : "";

  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  if (!id_zona || !id_area) {
    toggleTabla(false);
    return;
  }

  try {
    // Ahora enviamos también el área
    const res = await fetch(`${BASE_URL}src/controllers/TrimestralizacionController.php?accion=listar&id_zona=${id_zona}&id_area=${id_area}`);
    const data = await res.json();
    tbody.innerHTML = "";

    const activos = Array.isArray(data)
      ? data.filter((d) => d && (d.estado === 1 || d.estado === "1"))
      : [];

    if (!activos.length) {
      tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">No hay registros activos para esta zona y área.</td></tr>`;
      Toast.fire({ icon: "info", title: "Sin registros activos" });
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

    Toast.fire({ icon: "success", title: "Trimestralización cargada correctamente" });
  } catch (error) {
    console.error("Error al cargar:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
    Toast.fire({ icon: "error", title: "Error al cargar trimestralización" });
  }
}
// =======================
// MODO EDICIÓN
// =======================
function activarEdicion() {
  const registros = document.querySelectorAll("#tbody-horarios .registro");
  if (!registros.length) {
    Toast.fire({ icon: "warning", title: "No hay datos para editar" });
    return;
  }

  registros.forEach((reg) => {
    const ficha = reg.querySelector(".ficha")?.textContent.trim() || "";
    const competencia = reg.querySelector(".competencia")?.textContent.trim() || "";
    const nivel_ficha = reg.querySelector(".nivel_ficha")?.textContent.trim() || "";

    reg.innerHTML = `
      <input type="text" value="${ficha}" placeholder="Número de ficha"
        class="block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm">
      <textarea placeholder="Competencia / Observaciones"
        class="w-full px-2 py-1 border border-gray-400 rounded text-sm resize-none">${competencia}</textarea>
      <div class="text-xs text-gray-500 mt-1">Nivel: ${nivel_ficha}</div>
    `;
  });

  document.getElementById("botones-principales").style.display = "none";
  mostrarBotonesEdicion();
}

function mostrarBotonesEdicion() {
  const div = document.createElement("div");
  div.id = "botones-edicion";
  div.className = "mt-4 flex justify-center gap-4";

  const guardar = document.createElement("button");
  guardar.textContent = "Guardar cambios";
  guardar.className = "bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition";
  guardar.onclick = guardarCambios;

  const cancelar = document.createElement("button");
  cancelar.textContent = "Cancelar edición";
  cancelar.className = "bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition";
  cancelar.onclick = cancelarEdicion;

  div.appendChild(guardar);
  div.appendChild(cancelar);
  document.querySelector("main").appendChild(div);
}

// =======================
// GUARDAR / CANCELAR EDICIÓN
// =======================
async function guardarCambios() {
  const registros = document.querySelectorAll("#tbody-horarios .registro");
  const filas = Array.from(registros).map((r) => ({
    id_horario: r.getAttribute("data-id"),
    numero_ficha: r.querySelector("input")?.value || "",
    descripcion: r.querySelector("textarea")?.value || "",
  }));

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=actualizar&id_zona=${id_zona}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(filas),
    });

    const data = await res.json();
    if (data.success) {
      Toast.fire({ icon: "success", title: "Cambios guardados correctamente" });
      document.getElementById("botones-edicion").remove();
      document.getElementById("botones-principales").style.display = "flex";
      cargarTrimestralizacion();
    } else {
      Toast.fire({ icon: "error", title: "Error al guardar cambios" });
    }
  } catch {
    Toast.fire({ icon: "error", title: "Error de conexión al guardar" });
  }
}

function cancelarEdicion() {
  Swal.fire({
    title: "¿Deseas cancelar los cambios realizados?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, continuar",
    reverseButtons: true,
  }).then((res) => {
    if (res.isConfirmed) {
      document.getElementById("botones-edicion").remove();
      document.getElementById("botones-principales").style.display = "flex";
      cargarTrimestralizacion();
      Toast.fire({ icon: "info", title: "Edición cancelada" });
    }
  });
}

// =======================
// ELIMINAR TODO
// =======================
async function confirmarEliminar() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=eliminar&id_zona=${id_zona}`);
    const data = await res.json();
    Toast.fire({ icon: "success", title: data.message || "Trimestralización eliminada correctamente" });
    cargarTrimestralizacion();
  } catch {
    Toast.fire({ icon: "error", title: "Error al eliminar" });
  } finally {
    cerrarModal();
  }
}

function mostrarModalEliminar() {
  document.getElementById("modalEliminar").classList.remove("hidden");
}
function cerrarModal() {
  document.getElementById("modalEliminar").classList.add("hidden");
}

// =======================
// DESCARGAR PDF
// =======================
async function descargarPDF() {
  const { jsPDF } = window.jspdf;
  const contenedor = document.createElement("div");
  contenedor.style.backgroundColor = "white";
  contenedor.style.padding = "20px";
  contenedor.style.position = "fixed";
  contenedor.style.top = "-9999px";
  document.body.appendChild(contenedor);

  contenedor.innerHTML = `
    <h1 style="text-align:center;font-weight:bold;font-size:22px;margin-bottom:10px;">
      VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona}
    </h1>
    <h2 style="text-align:center;color:#333;font-size:16px;margin-bottom:20px;">
      Sistema de gestión de trimestralización - SENA
    </h2>
    ${document.querySelector("#tabla-horarios").outerHTML}
  `;

  await new Promise((r) => setTimeout(r, 300));
  const canvas = await html2canvas(contenedor, { scale: 2, useCORS: true });
  const pdf = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });

  const imgWidth = pdf.internal.pageSize.getWidth();
  const imgHeight = (canvas.height * imgWidth) / canvas.width;
  pdf.addImage(canvas, "PNG", 0, 0, imgWidth, imgHeight);
  pdf.save(`trimestralizacion_zona_${id_zona}.pdf`);
  contenedor.remove();
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarAreasYZonas();
  if (id_zona) {
    toggleTabla(true);
    cargarTrimestralizacion();
  } else toggleTabla(false);

  document.getElementById("btn-actualizar")?.addEventListener("click", activarEdicion);
});
