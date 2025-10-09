document.addEventListener("DOMContentLoaded", () => {
  cargarDatosGuardados(); // Solo carga el texto, sin inputs
});

// 🔹 Mostrar inputs al presionar "Actualizar"
function actualizar() {
  // Ocultar textos visibles mientras editas
  document.querySelectorAll(".texto-guardado").forEach((div) => {
    div.style.display = "none";
  });

  // Crear los inputs solo cuando se edita
  crearInputsParaEditar();

  document.querySelector(".mt-6.mb-6.flex.gap-6").style.display = "none";
  mostrarBotonesConfirmacion();
  alert("Ahora puedes editar la trimestralización.");
}

// 🔹 Crear inputs dinámicamente para edición
function crearInputsParaEditar() {
  const filas = document.querySelectorAll("table tbody tr");

  filas.forEach((fila) => {
    const hora = fila.cells[0].textContent.trim();

    fila.querySelectorAll("td").forEach((celda, colIndex) => {
      if (colIndex === 0) return;

      const texto = celda.querySelector(".texto-guardado");
      const ficha = texto?.querySelector("p strong")?.textContent || "";
      const instructor = texto?.querySelectorAll("p")[1]?.textContent || "";
      const observaciones = texto?.querySelector("small")?.textContent || "";

      const placeholders = ["Ficha", "Instructor", "Competencia / Observaciones"];
      const valores = [ficha, instructor, observaciones];

      // Crear los textareas editables
      placeholders.forEach((ph, i) => {
        const ta = document.createElement("textarea");
        ta.className = "celda";
        ta.rows = i === 2 ? 2 : 1;
        ta.placeholder = ph;
        ta.value = valores[i];
        ta.style.resize = i === 2 ? "vertical" : "none";
        ta.readOnly = false;
        ta.classList.add("bg-white", "cursor-text", "block", "w-full", "border", "border-gray-300", "rounded-md", "p-1", "mt-1");
        celda.appendChild(ta);
      });
    });
  });
}

// 🔹 Guardar cambios
function guardarCambios() {
  const datos = [];
  const filas = document.querySelectorAll("table tbody tr");

  filas.forEach((fila) => {
    const hora = fila.cells[0].textContent.trim();

    fila.querySelectorAll("td").forEach((celda, colIndex) => {
      if (colIndex === 0) return;
      const textareas = celda.querySelectorAll("textarea");
      if (textareas.length === 3) {
        const ficha = textareas[0].value.trim();
        const instructor = textareas[1].value.trim();
        const observaciones = textareas[2].value.trim();

        datos.push({ hora, dia: colIndex, ficha, instructor, observaciones });

        // Mostrar solo texto limpio
        celda.innerHTML = `
          <div class="texto-guardado">
            <p><strong>${ficha || ""}</strong></p>
            <p>${instructor || ""}</p>
            <small class="text-gray-600">${observaciones || ""}</small>
          </div>
        `;
      }
    });
  });

  // Guardar en localStorage
  localStorage.setItem("trimestralizacion_zona", JSON.stringify(datos));
  alert("✅ Cambios guardados correctamente.");

  eliminarBotonesConfirmacion();
  document.querySelector(".mt-6.mb-6.flex.gap-6").style.display = "flex";
}

// 🔹 Cargar datos guardados (solo texto)
function cargarDatosGuardados() {
  const guardado = localStorage.getItem("trimestralizacion_zona");
  if (!guardado) return;
  const datos = JSON.parse(guardado);

  const filas = document.querySelectorAll("table tbody tr");
  filas.forEach((fila) => {
    const hora = fila.cells[0].textContent.trim();
    fila.querySelectorAll("td").forEach((celda, colIndex) => {
      if (colIndex === 0) return;

      const existente = datos.find((d) => d.hora === hora && d.dia === colIndex);
      if (existente) {
        celda.innerHTML = `
          <div class="texto-guardado">
            <p><strong>${existente.ficha || ""}</strong></p>
            <p>${existente.instructor || ""}</p>
            <small class="text-gray-600">${existente.observaciones || ""}</small>
          </div>
        `;
      } else {
        celda.innerHTML = `<div class="texto-guardado"></div>`;
      }
    });
  });
}

// 🔹 Cancelar edición
function cancelarEdicion() {
  if (!confirm("¿Deseas cancelar los cambios?")) return;
  alert("Edición cancelada. Los campos vuelven a estar bloqueados.");
  eliminarBotonesConfirmacion();
  document.querySelector(".mt-6.mb-6.flex.gap-6").style.display = "flex";
  location.reload(); // recargar vista anterior
}

// 🔹 Mostrar botones de Guardar / Cancelar
function mostrarBotonesConfirmacion() {
  if (document.querySelector("#botones-confirmacion")) return;

  const contenedor = document.createElement("div");
  contenedor.id = "botones-confirmacion";
  contenedor.className = "mt-4 flex justify-center gap-4";

  const btnGuardar = document.createElement("button");
  btnGuardar.textContent = "Guardar cambios";
  btnGuardar.className =
    "bg-[#39A900] text-white px-6 py-2 rounded-lg hover:bg-green-700 transition";
  btnGuardar.onclick = guardarCambios;

  const btnCancelar = document.createElement("button");
  btnCancelar.textContent = "Cancelar edición";
  btnCancelar.className =
    "bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition";
  btnCancelar.onclick = cancelarEdicion;

  contenedor.appendChild(btnGuardar);
  contenedor.appendChild(btnCancelar);
  document.querySelector("main").appendChild(contenedor);
}

// 🔹 Eliminar botones de confirmación
function eliminarBotonesConfirmacion() {
  const contenedor = document.querySelector("#botones-confirmacion");
  if (contenedor) contenedor.remove();
}

// 🔹 Modal eliminar
function mostrarModalEliminar() {
  document.getElementById("modalEliminar").classList.add("active");
}
function cerrarModal() {
  document.getElementById("modalEliminar").classList.remove("active");
}

// 🔹 Confirmar eliminación
function confirmarEliminar() {
  localStorage.removeItem("trimestralizacion_zona");
  alert("🗑️ Trimestralización eliminada correctamente.");
  cerrarModal();
  location.reload();
}

// 🔹 Cerrar modal al hacer clic fuera
document.getElementById("modalEliminar").addEventListener("click", function (e) {
  if (e.target === this) cerrarModal();
});

// 🔹 Descargar PDF (simulado)
function descargarPDF() {
  alert("📄 Simulando descarga de PDF...");
}
