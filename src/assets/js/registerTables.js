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

    const dias = ["LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO"];
    const horas = Array.from({ length: 16 }, (_, i) => i + 6);
    tbody.innerHTML = "";

    horas.forEach((hora, idx) => {
      const fila = document.createElement("tr");
      fila.className = idx % 2 === 0 ? "bg-gray-50" : "bg-white";
      fila.innerHTML = `<td class="border border-gray-700 p-2 font-medium">${hora}:00-${hora + 1}:00</td>`;

      dias.forEach((dia) => {
        const registros = data.filter(
          (r) => r.dia?.toUpperCase() === dia && parseInt(r.hora_inicio) === hora
        );

        if (registros.length) {
          const contenido = registros
            .map(
              (r) => `
                <div class="registro" data-id="${r.id_horario}">
                  <div class="ficha">${r.numero_ficha || ""}</div>
                  <div class="instructor">${r.nombre_instructor || ""}</div>
                  <div class="tipo_instructor">${r.tipo_instructor || ""}</div>
                  <div class="competencia">${r.descripcion || ""}</div>
                </div>
              `
            )
            .join("<hr class='my-1 border-dashed border-gray-300'>");

          fila.innerHTML += `<td class="border border-gray-700 p-2 text-left text-sm">${contenido}</td>`;
        } else {
          fila.innerHTML += `<td class="border border-gray-700 p-2">&nbsp;</td>`;
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
  document.getElementById("btn-actualizar")?.addEventListener("click", activarEdicion);
});
