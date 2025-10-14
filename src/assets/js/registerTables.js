// ===============================
// REGISTER TABLES - FUNCIONAL 2025 (EDICIÓN Y ELIMINACIÓN COMPLETAS)
// ===============================

// --- Obtener id_zona actual desde la URL ---
const urlParams = new URLSearchParams(window.location.search);
const id_zona = urlParams.get("id_zona");

// =======================
// CARGAR DATOS
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  if (!id_zona) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">⚠️ No se especificó la zona.</td></tr>`;
    return;
  }

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=listar&id_zona=${id_zona}`);
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-gray-500 p-4">No hay registros para esta zona.</td></tr>`;
      return;
    }

    // Lectura segura de selects (pueden estar vacíos)
    const selInicio = document.querySelector("select[name='hora_inicio']");
    const selFin = document.querySelector("select[name='hora_fin']");
    const horaInicioSel = selInicio && selInicio.value ? parseInt(selInicio.value.split(":")[0], 10) : null;
    const horaFinSel = selFin && selFin.value ? parseInt(selFin.value.split(":")[0], 10) : null;
    // Si ambos existen calculamos duracion, si no, duracion = 1 (comportamiento por fila)
    const duracion = (horaInicioSel !== null && horaFinSel !== null) ? (horaFinSel - horaInicioSel) : 1;

    const dias = ["LUNES","MARTES","MIERCOLES","JUEVES","VIERNES","SABADO"];
    const horas = Array.from({ length: 16 }, (_, i) => i + 6);
    tbody.innerHTML = "";

    horas.forEach((hora, idx) => {
      const fila = document.createElement("tr");
      fila.className = idx % 2 === 0 ? "bg-gray-50" : "bg-white";
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}:00-${hora + 1}:00</td>`;

      dias.forEach(dia => {
        // Filtrar registros: mismo día Y que su bloque horario se solape con la fila y (si hay selección) con el rango seleccionado
        const registros = data.filter(r => {
          if (!r.dia) return false;
          if (r.dia.toUpperCase() !== dia) return false;

          // parsear horas del registro (fall back si no existe hora_fin)
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : (rStart + 1);

          // Coincide si esta hora pertenece al bloque del registro
    const dentroDelBloque = hora >= rStart && hora < rEnd;

    // Y además, si el usuario seleccionó un rango, que esté dentro de ese rango
    const dentroDeSeleccion = (
      (horaInicioSel === null || hora >= horaInicioSel) &&
      (horaFinSel === null || hora < horaFinSel)
    );

    return dentroDelBloque && dentroDeSeleccion;
  });
        if (registros.length > 0) {
        let contenido = "";

        registros.forEach(r => {
          const rStart = parseInt((r.hora_inicio || "0:00").split(":")[0], 10);
          const rEnd = r.hora_fin ? parseInt(r.hora_fin.split(":")[0], 10) : rStart + 1;

          // Si la hora actual es la de inicio del bloque, mostramos todo el detalle
          if (hora === rStart) {
            contenido += `
              <div class="mb-1 border-gray-200 pb-1">
                <div><strong>Ficha:</strong> ${r.numero_ficha ?? ""}</div>
                <div><strong>Instructor:</strong> ${r.nombre_instructor ?? ""} (${r.tipo_instructor ?? ""})</div>
                <div><strong>Competencia:</strong> ${r.descripcion ?? "Sin especificar"}</div>
              </div>`;
          } 
          // Si es una hora dentro del bloque (no inicial), solo el nombre
          else if (hora > rStart && hora < rEnd) {
            contenido += `
              <div class="mb-1  border-gray-200 pb-1 ">
                <strong>Instructor:</strong> ${r.nombre_instructor ?? ""}
              </div>`;
          }
        });

        fila.innerHTML += `
          <td class="border border-gray-700 p-2 text-sm text-left leading-tight">
            ${contenido}
          </td>`;
      } else {
        fila.innerHTML += `<td class="border border-gray-700 p-2 text-center text-gray-500 italic">ZONA DISPONIBLE</td>`;
      }
      });

      tbody.appendChild(fila);
    });
  } catch (error) {
    console.error("Error al cargar:", error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
  }
}

// =======================
// ACTIVAR MODO EDICIÓN
// =======================
function activarEdicion() {
  const registros = document.querySelectorAll("#tbody-horarios .registro");

  registros.forEach((reg) => {
    const ficha = reg.querySelector(".ficha")?.innerText || "";
    const nombre_instructor = reg.querySelector(".instructor")?.innerText || "";
    const tipo_instructor = reg.querySelector(".tipo_instructor")?.innerText || "";
    const competencia = reg.querySelector(".competencia")?.innerText || "";

    reg.innerHTML = `
      <input type="text" value="${ficha}" placeholder="Número de ficha"
        class="block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm">

      <input type="text" value="${nombre_instructor}" placeholder="Nombre instructor"
        class="block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm">

      <input type="text" value="${tipo_instructor}" placeholder="Tipo instructor"
        readonly
        class="block w-full mb-1 px-2 py-1 border border-gray-300 rounded text-sm bg-gray-100 text-gray-600 cursor-not-allowed">

      <textarea placeholder="Competencia / Observaciones"
        class="w-full px-2 py-1 border border-gray-400 rounded text-sm resize-none">${competencia}</textarea>
    `;
  });

  document.getElementById("botones-principales").style.display = "none";
  mostrarBotonesEdicion();
}

// =======================
// BOTONES DE EDICIÓN
// =======================
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
// GUARDAR CAMBIOS EN BD
// =======================
async function guardarCambios() {
  const filas = [];
  const registros = document.querySelectorAll("#tbody-horarios .registro");

  registros.forEach((reg) => {
    const inputs = reg.querySelectorAll("input, textarea");
    if (!inputs.length) return;

    filas.push({
      id_horario: reg.getAttribute("data-id"),
      numero_ficha: inputs[0].value.trim(),
      nombre_instructor: inputs[1].value.trim(),
      tipo_instructor: inputs[2].value.trim(),
      descripcion: inputs[3].value.trim(),
    });
  });

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=actualizar&id_zona=${id_zona}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(filas),
    });

    const data = await res.json();

    if (data.success) {
      alert("✅ Cambios guardados correctamente.");
      document.getElementById("botones-edicion").remove();
      document.getElementById("botones-principales").style.display = "flex";
      cargarTrimestralizacion();
    } else {
      alert("⚠️ Error al guardar: " + (data.error || "Desconocido"));
    }
  } catch (err) {
    console.error("❌ Error al actualizar:", err);
    alert("No se pudo guardar los cambios.");
  }
}

// =======================
// CANCELAR EDICIÓN
// =======================
function cancelarEdicion() {
  if (!confirm("¿Deseas cancelar los cambios realizados?")) return;
  document.getElementById("botones-edicion").remove();
  document.getElementById("botones-principales").style.display = "flex";
  cargarTrimestralizacion();
}

// =======================
// ELIMINAR TODO
// =======================
function mostrarModalEliminar() {
  document.getElementById("modalEliminar").classList.remove("hidden");
}
function cerrarModal() {
  document.getElementById("modalEliminar").classList.add("hidden");
}
async function confirmarEliminar() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=eliminar&id_zona=${id_zona}`);
    const data = await res.json();
    alert(data.message || data.mensaje || "Trimestralización eliminada correctamente.");
    cargarTrimestralizacion();
  } catch {
    alert("Error al eliminar.");
  } finally {
    cerrarModal();
  }
}

// =======================
// DESCARGAR PDF (Encabezado + Títulos + Thead visible arriba)
// =======================
async function descargarPDF() {
  const { jsPDF } = window.jspdf;

  // 🔹 Elementos base
  const main = document.querySelector("main");

  // 🔹 Crear contenedor temporal
  const contenedor = document.createElement("div");
  contenedor.style.backgroundColor = "white";
  contenedor.style.padding = "20px";
  contenedor.style.width = "100%";
  contenedor.style.position = "fixed";
  contenedor.style.top = "-99999px";
  contenedor.style.left = "0";
  contenedor.style.zIndex = "0";
  contenedor.style.opacity = "1";
  contenedor.style.pointerEvents = "none";
  contenedor.style.display = "flex";
  contenedor.style.flexDirection = "column";
  document.body.appendChild(contenedor);

  // 🔹 Crear encabezado superior con títulos personalizados
  const encabezadoTop = document.createElement("div");
  encabezadoTop.style.textAlign = "center";
  encabezadoTop.style.marginBottom = "20px";
  encabezadoTop.innerHTML = `
    <h1 style="font-size:22px; font-weight:bold; color:#111;">
      VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA ${id_zona || ""}
    </h1>
    <h2 style="font-size:16px; color:#333;">
      Sistema de gestión de trimestralización<br>SENA
    </h2>
  `;
  contenedor.appendChild(encabezadoTop);

  // 🔹 Clonar tabla principal
  const tablaOriginal = document.querySelector("#tabla-horarios");
  if (tablaOriginal) {
    const tablaClone = tablaOriginal.cloneNode(true);

    // 🟢 Asegurar que el THEAD (verde) se vea siempre
    const thead = tablaClone.querySelector("thead");
    if (thead) {
      thead.style.position = "relative";
      thead.style.top = "0";
      thead.style.backgroundColor = "#16a34a"; // verde SENA
      thead.style.color = "white";
      thead.style.zIndex = "10";
    }

    tablaClone.style.width = "100%";
    tablaClone.style.borderCollapse = "collapse";
    tablaClone.style.maxHeight = "none";
    tablaClone.style.overflow = "visible";
    tablaClone.style.height = "auto";

    contenedor.appendChild(tablaClone);
  }

  // 🔹 Esperar render
  await new Promise((resolve) => setTimeout(resolve, 400));

  // 🔹 Capturar el contenedor entero
  const canvas = await html2canvas(contenedor, {
    scale: 2,
    useCORS: true,
    scrollY: 0,
    windowWidth: document.body.scrollWidth,
    windowHeight: contenedor.scrollHeight,
  });

  // 🔹 Crear PDF
  const pdf = new jsPDF({
    orientation: "landscape",
    unit: "mm",
    format: "a4",
  });

  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();
  const imgWidth = pageWidth;
  const imgHeight = (canvas.height * imgWidth) / canvas.width;

  let y = 0;
  while (y < imgHeight) {
    if (y > 0) pdf.addPage();
    pdf.addImage(canvas, "PNG", 0, -y, imgWidth, imgHeight);
    y += pageHeight;
  }

  pdf.save(`trimestralizacion_zona_${id_zona || "sin_id"}.pdf`);
  contenedor.remove();
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarTrimestralizacion();
  document.getElementById("btn-actualizar")?.addEventListener("click", activarEdicion);
});
