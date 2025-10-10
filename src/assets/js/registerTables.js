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

    const dias = ["LUNES","MARTES","MIERCOLES","JUEVES","VIERNES","SABADO"];
    const horas = Array.from({ length: 16 }, (_, i) => i + 6);
    tbody.innerHTML = "";

    horas.forEach((hora, idx) => {
      const fila = document.createElement("tr");
      fila.className = idx % 2 === 0 ? "bg-gray-50" : "bg-white";
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}-${hora+1}</td>`;

      dias.forEach(dia => {
        const reg = data.find(r => 
          r.dia?.toUpperCase() === dia && 
          parseInt(r.hora_inicio.split(":")[0]) === hora
        );

        if (reg) {
          fila.innerHTML += `
            <td class="border border-gray-700 p-2 text-sm text-left leading-tight">
              <div><strong>Ficha:</strong> ${reg.numero_ficha ?? ""}</div>
              <div><strong>Instructor:</strong> ${reg.nombre_instructor ?? ""} (${reg.tipo_instructor ?? ""})</div>
              <div><strong>Competencia:</strong> ${reg.descripcion ?? "Sin especificar"}</div>
            </td>`;
        } else {
          fila.innerHTML += `<td class="border border-gray-700 p-2">&nbsp;</td>`;
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
// CANCELAR EDICIÓN
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
// DESCARGAR PDF ROBUSTO
// =======================
async function descargarPDF() {
  try {
    const { jsPDF } = window.jspdf;

    const original = document.getElementById('contenido-pdf');
    if (!original) return alert("No se encontró #contenido-pdf");

    // 1) Clonar y limpiar la copia (no tocar la UI visible)
    const clone = original.cloneNode(true);

    // eliminar botones, modales y elementos con clase no-print
    clone.querySelectorAll('#botones-principales, #modalEliminar, .no-print').forEach(n => n.remove());

    // quitar clases/sticky que causan problemas
    clone.querySelectorAll('thead, .sticky, .top-0').forEach(el => {
      el.style.position = 'static';
      el.style.top = 'auto';
      el.classList.remove('sticky', 'top-0');
    });

    // asegurar que la sección de la tabla no tenga overflow
    clone.querySelectorAll('#tabla-horarios').forEach(s => {
      s.style.maxHeight = 'none';
      s.style.overflow = 'visible';
    });

    // 2) Insertar la copia off-screen para que se renderice estilos computados
    const wrapper = document.createElement('div');
    wrapper.style.position = 'fixed';
    wrapper.style.left = '-10000px';
    wrapper.style.top = '0';
    // opcional: dar el ancho real para que las fuentes/bordes se comporten igual
    wrapper.style.width = original.offsetWidth + 'px';
    wrapper.style.background = '#ffffff';
    wrapper.appendChild(clone);
    document.body.appendChild(wrapper);

    // 3) Esperar a que las fuentes se carguen (evita desajustes tipográficos)
    if (document.fonts && document.fonts.ready) {
      await document.fonts.ready;
    }

    // 4) Capturar con html2canvas
    const canvas = await html2canvas(wrapper, {
      scale: 2,            // mejora resolución
      useCORS: true,       // si hay imágenes externas con CORS
      backgroundColor: '#ffffff'
    });

    // eliminar la copia del DOM
    document.body.removeChild(wrapper);

    // 5) Preparar pdf y paginar porciones del canvas
    const pdf = new jsPDF('landscape', 'pt', 'a4');
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();

    const canvasWidth = canvas.width;
    const canvasHeight = canvas.height;

    // ratio px -> pts
    const ratio = pdfWidth / canvasWidth;
    const sliceHeightPx = Math.floor(pdfHeight / ratio);

    let positionY = 0;
    let pageIndex = 0;

    while (positionY < canvasHeight) {
      const pageCanvas = document.createElement('canvas');
      pageCanvas.width = canvasWidth;
      pageCanvas.height = Math.min(sliceHeightPx, canvasHeight - positionY);
      const ctx = pageCanvas.getContext('2d');

      // copiar segmento
      ctx.drawImage(canvas, 0, positionY, canvasWidth, pageCanvas.height, 0, 0, canvasWidth, pageCanvas.height);

      const pageData = pageCanvas.toDataURL('image/png');

      if (pageIndex > 0) pdf.addPage();
      pdf.addImage(pageData, 'PNG', 0, 0, pdfWidth, pageCanvas.height * ratio);

      positionY += sliceHeightPx;
      pageIndex++;
    }

    // 6) Guardar
    pdf.save('Trimestralizacion.pdf');

  } catch (err) {
    console.error("Error generando PDF:", err);
    alert("Hubo un error generando el PDF. Revisa la consola");
  }
}



// =======================
// INICIO
// =======================
document.addEventListener("DOMContentLoaded", () => {
  cargarTrimestralizacion();
  document.getElementById("btn-actualizar").addEventListener("click", activarEdicion);
});
