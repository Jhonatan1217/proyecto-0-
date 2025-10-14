// =======================
// CARGAR DATOS COMO TEXTO
// =======================
async function cargarTrimestralizacion() {
  const tbody = document.getElementById("tbody-horarios");
  tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=listar`);
    const data = await res.json();

    if (!Array.isArray(data)) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al cargar la información.</td></tr>`;
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
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}-${hora+1}</td>`;

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
        fila.innerHTML += `<td class="border border-gray-700 p-2 text-center text-gray-500 italic"></td>`;
      }
      });

      tbody.appendChild(fila);
    });
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
  }
}

// =======================
// MODO EDICIÓN (CONVERTIR DIVS A TEXTAREAS)
// =======================
function activarEdicion() {
  const celdas = document.querySelectorAll("#tbody-horarios td:not(:first-child)");
  celdas.forEach(celda => {
    if (celda.innerHTML.trim() === "&nbsp;" || celda.innerHTML.trim() === "") return;

    const contenido = celda.innerText.trim();
    const textarea = document.createElement("textarea");
    textarea.value = contenido;
    textarea.className = "w-full p-1 border border-gray-300 rounded bg-white resize-none";
    celda.innerHTML = "";
    celda.appendChild(textarea);
  });

  document.getElementById("botones-principales").style.display = "none";
  mostrarBotonesEdicion();
}

// =======================
// BOTONES EDICIÓN
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
// GUARDAR Y CANCELAR
// =======================
function guardarCambios() {
  const textareas = document.querySelectorAll("#tbody-horarios textarea");
  textareas.forEach(ta => {
    const valor = ta.value.trim();
    const div = document.createElement("div");
    div.textContent = valor;
    ta.parentElement.innerHTML = div.outerHTML;
  });

  document.getElementById("botones-edicion").remove();
  document.getElementById("botones-principales").style.display = "flex";
  alert("Cambios guardados (por ahora solo visualmente).");
}

function cancelarEdicion() {
  if (!confirm("¿Deseas cancelar los cambios realizados?")) return;
  cargarTrimestralizacion();
  document.getElementById("botones-edicion").remove();
  document.getElementById("botones-principales").style.display = "flex";
}

// =======================
// ACTUALIZAR (MODO EDICIÓN CON 3 INPUTS)
// =======================
function activarEdicion() {
  const celdas = document.querySelectorAll("#tbody-horarios td:not(:first-child)");

  celdas.forEach(celda => {
    // Si la celda está vacía, no crear inputs
    if (celda.innerHTML.trim() === "&nbsp;" || celda.innerHTML.trim() === "") return;

    // Extraer los textos actuales
    const fichaMatch = celda.innerHTML.match(/Ficha:<\/strong>\s*([^<]*)/);
    const instructorMatch = celda.innerHTML.match(/Instructor:<\/strong>\s*([^<]*)/);
    const competenciaMatch = celda.innerHTML.match(/Competencia:<\/strong>\s*([^<]*)/);

    const ficha = fichaMatch ? fichaMatch[1].trim() : "";
    const instructor = instructorMatch ? instructorMatch[1].trim() : "";
    const competencia = competenciaMatch ? competenciaMatch[1].trim() : "";

    // Crear inputs
    celda.innerHTML = `
      <input type="text" value="${ficha}" placeholder="Ficha"
        class="block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm">
      <input type="text" value="${instructor}" placeholder="Instructor"
        class="block w-full mb-1 px-2 py-1 border border-gray-400 rounded text-sm">
      <textarea placeholder="Competencia / Observaciones"
        class="w-full px-2 py-1 border border-gray-400 rounded text-sm resize-none">${competencia}</textarea>
    `;
  });

  // Ocultar botones principales y mostrar los de edición
  document.getElementById("botones-principales").style.display = "none";
  mostrarBotonesEdicion();
}

// =======================
// BOTONES EDICIÓN
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
// GUARDAR CAMBIOS (volver a texto)
// =======================
function guardarCambios() {
  const celdas = document.querySelectorAll("#tbody-horarios td:not(:first-child)");

  celdas.forEach(celda => {
    const inputs = celda.querySelectorAll("input, textarea");
    if (!inputs.length) return;

    const ficha = inputs[0].value.trim();
    const instructor = inputs[1].value.trim();
    const competencia = inputs[2].value.trim();

    celda.innerHTML = `
      <div><strong>Ficha:</strong> ${ficha}</div>
      <div><strong>Instructor:</strong> ${instructor}</div>
      <div><strong>Competencia:</strong> ${competencia}</div>
    `;
  });

  document.getElementById("botones-edicion").remove();
  document.getElementById("botones-principales").style.display = "flex";
  alert("Cambios guardados visualmente (aún no conectados a la base de datos).");
}

// =======================
// CANCELAR EDICIÓNZZZZZZ
// =======================
function cancelarEdicion() {
  if (!confirm("¿Deseas cancelar los cambios realizados?")) return;
  cargarTrimestralizacion();
  document.getElementById("botones-edicion").remove();
  document.getElementById("botones-principales").style.display = "flex";
}

// =======================
// MODAL ELIMINAR
// =======================
function mostrarModalEliminar() {
  document.getElementById("modalEliminar").classList.remove("hidden");
}
function cerrarModal() {
  document.getElementById("modalEliminar").classList.add("hidden");
}
async function confirmarEliminar() {
  try {
    const res = await fetch(`${BASE_URL}src/controllers/trimestralizacionController.php?accion=eliminar`);
    const data = await res.json();
    alert(data.mensaje || "Trimestralización eliminada correctamente.");
    cargarTrimestralizacion();
  } catch {
    alert("Error al eliminar.");
  } finally {
    cerrarModal();
  }
}

// =======================
// DESCARGAR PDF
// =======================
function descargarPDF() {
  alert("Función de descarga en desarrollo.");
}

// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarTrimestralizacion();
  document.getElementById("btn-actualizar").addEventListener("click", activarEdicion);
});
