document.addEventListener('DOMContentLoaded', () => {
  const rows = document.querySelectorAll('table tbody tr');
  console.log('Filas encontradas:', rows.length);

  rows.forEach(row => {
    const cells = Array.from(row.querySelectorAll('td'));
    cells.forEach((cell, index) => {
      // Omitir columna "Hora"
      if (index === 0) return;

      // Evitar duplicados
      if (cell.querySelector('.celda')) return;

      // Crear los dos primeros textareas
      for (let i = 0; i < 2; i++) {
        const ta = document.createElement('textarea');
        ta.className = 'celda';
        ta.rows = 1;
        ta.placeholder = i === 0 ? 'Ficha' : 'Instructor';
        ta.style.resize = 'none';
        ta.readOnly = true;
        ta.classList.add('bg-gray-100', 'cursor-not-allowed');
        ta.addEventListener('input', function () {
          this.style.height = 'auto';
          this.style.height = this.scrollHeight + 'px';
        });
        cell.appendChild(ta);
      }

      // Crear textarea grande
      const taBig = document.createElement('textarea');
      taBig.className = 'celda celda-big';
      taBig.rows = 2;
      taBig.placeholder = 'Competencia / Observaciones';
      taBig.style.resize = 'vertical';
      taBig.readOnly = true;
      taBig.classList.add('bg-gray-100', 'cursor-not-allowed');
      taBig.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
      });
      cell.appendChild(taBig);
    });
  });
});

// 🔹 Botón: Actualizar
function actualizar() {
  const textareas = document.querySelectorAll('.celda');
  textareas.forEach(ta => {
    ta.readOnly = false;
    ta.classList.remove('bg-gray-100', 'cursor-not-allowed');
    ta.classList.add('bg-white', 'cursor-text');
  });

  // Ocultar botones azules
  document.querySelector('.mt-6.mb-6.flex.gap-6').style.display = 'none';

  // Mostrar botones de confirmación y cancelación
  mostrarBotonesConfirmacion();

  alert("Ahora puedes editar la trimestralización.");
}

// 🔹 Botón: Eliminar
function eliminar() {
  if (confirm("¿Deseas eliminar esta trimestralización?")) {
    alert("Trimestralización eliminada correctamente.");
  }
}

// 🔹 Botón: Descargar PDF
function descargarPDF() {
  alert("Descargando archivo PDF...");
}

// 🔹 Mostrar botones de Confirmar y Cancelar
function mostrarBotonesConfirmacion() {
  if (document.querySelector('#botones-confirmacion')) return;

  const contenedor = document.createElement('div');
  contenedor.id = 'botones-confirmacion';
  contenedor.className = 'mt-4 flex justify-center gap-4';

  // Botón Guardar
  const btnGuardar = document.createElement('button');
  btnGuardar.textContent = 'Guardar cambios';
  btnGuardar.className =
    'bg-[#39A900] text-white px-6 py-2 rounded-lg hover:bg-green-700 transition';
  btnGuardar.onclick = guardarCambios;

  // Botón Cancelar
  const btnCancelar = document.createElement('button');
  btnCancelar.textContent = 'Cancelar edición';
  btnCancelar.className =
    'bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition';
  btnCancelar.onclick = cancelarEdicion;

  contenedor.appendChild(btnGuardar);
  contenedor.appendChild(btnCancelar);

  document.querySelector('main').appendChild(contenedor);
}

// 🔹 Guardar cambios
function guardarCambios() {
  const textareas = document.querySelectorAll('.celda');
  textareas.forEach(ta => {
    ta.readOnly = true;
    ta.classList.add('bg-gray-100', 'cursor-not-allowed');
    ta.classList.remove('bg-white', 'cursor-text');
  });

  alert('Cambios guardados correctamente.');
  eliminarBotonesConfirmacion();

  // Volver a mostrar los botones azules
  document.querySelector('.mt-6.mb-6.flex.gap-6').style.display = 'flex';
}

// 🔹 Cancelar edición
function cancelarEdicion() {
  const confirmar = confirm('¿Deseas cancelar los cambios realizados?');
  if (!confirmar) return;

  const textareas = document.querySelectorAll('.celda');
  textareas.forEach(ta => {
    ta.readOnly = true;
    ta.classList.add('bg-gray-100', 'cursor-not-allowed');
    ta.classList.remove('bg-white', 'cursor-text');
  });

  alert('Edición cancelada. Los campos vuelven a estar bloqueados.');
  eliminarBotonesConfirmacion();

  // Volver a mostrar los botones azules
  document.querySelector('.mt-6.mb-6.flex.gap-6').style.display = 'flex';
}

// 🔹 Eliminar los botones de Confirmar/Cancelar
function eliminarBotonesConfirmacion() {
  const contenedor = document.querySelector('#botones-confirmacion');
  if (contenedor) contenedor.remove();
}
