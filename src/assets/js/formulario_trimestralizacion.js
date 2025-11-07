// src/assets/js/formulario_trimestralizacion.js
if (!window.TRIMESTRALIZACION_INIT) {
  window.TRIMESTRALIZACION_INIT = true; 

  document.addEventListener("DOMContentLoaded", () => {

    // Configuración del Toast (SweetAlert2)
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2600,
      timerProgressBar: true
    });

// ------- Autocomplete competencias (versión para SELECT) -------
const COMPETENCIAS_API = `${(window.BASE_URL || '')}src/controllers/CompetenciaController.php?accion=listar`;
let COMP_CACHE = [];

async function cargarCompetenciasDatalist() {
  const select = document.getElementById('descripcion'); // <-- antes era datalist
  if (!select) return;

  try {
    const res = await fetch(COMPETENCIAS_API, { cache: 'no-store' });
    const json = await res.json();
    const arr = Array.isArray(json) ? json : (Array.isArray(json.data) ? json.data : []);
    COMP_CACHE = arr;

    // limpiamos pero dejamos el placeholder inicial
    select.innerHTML = `
      <option value="">
        Buscar competencia por código o nombre (opcional para vincular existente)
      </option>
    `;

    arr.forEach(c => {
      const id = c.id_competencia ?? c.id ?? '';
      const codigo = c.codigo_competencia ?? c.codigo ?? '';
      const nombre = c.nombre_competencia ?? c.nombre ?? c.descripcion ?? '';
      const label = `${id} | ${codigo} | ${nombre}`;

      const opt = document.createElement('option');
      opt.value = id;         // <-- ahora value es el ID REAL
      opt.textContent = label;
      select.appendChild(opt);
    });

  } catch (err) {
    console.warn("No se pudieron cargar competencias:", err);
  }
}

    // sincronizar input de autocomplete con hidden id_competencia y textarea descripcion
    (function bindCompetenciaInput(){
      const inp = document.getElementById('competencia_autocomplete');
      const hid = document.getElementById('id_competencia_input');
      const desc = document.getElementById('descripcion');
      if (!inp || !hid) return;
      // al escribir/buscar intentamos encontrar coincidencia exacta en datalist
      inp.addEventListener('input', (e) => {
        const val = inp.value.trim();
        hid.value = '';
        if (!val) return;
        // buscar en cache por coincidencia exacta en la cadena mostrada o por id al inicio
        const found = COMP_CACHE.find(c => {
          const label = `${c.id_competencia ?? c.id ?? ''} | ${c.codigo_competencia ?? c.codigo ?? ''} | ${c.nombre_competencia ?? c.nombre ?? c.descripcion ?? ''}`;
          return label === val || String(c.id_competencia) === val || (label.toLowerCase() === val.toLowerCase());
        });
        if (found) {
          hid.value = String(found.id_competencia ?? found.id ?? '');
          // opcional: rellenar la descripción con la descripción existente de la competencia
          if (desc && (found.descripcion || found.nombre_competencia || found.codigo_competencia)) {
            desc.value = found.descripcion ?? found.nombre_competencia ?? '';
          }
        }
      });
      // también al perder foco confirmamos si lo escrito corresponde a alguna opción
      inp.addEventListener('blur', () => {
        const val = inp.value.trim();
        if (!val) { hid.value = ''; return; }
        const found = COMP_CACHE.find(c => {
          const label = `${c.id_competencia ?? c.id ?? ''} | ${c.codigo_competencia ?? c.codigo ?? ''} | ${c.nombre_competencia ?? c.nombre ?? c.descripcion ?? ''}`;
          return label === val || String(c.id_competencia) === val;
        });
        if (!found) hid.value = ''; // evitar enviar id inválido
      });
    })();

    // Selecciona todos los formularios con la clase "trimestralizacion-form"
    document.querySelectorAll(".trimestralizacion-form").forEach((form) => {

      form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // ========== OBTENER VALORES ==========
        const zona = form.querySelector("[name='zona']").value.trim();

        // Intentamos obtener el id_area
        let areaField = form.querySelector("[name='area']");
        let id_area = areaField ? areaField.value.trim() : "";

        // Si no hay campo "area", buscamos un data-area en la opción seleccionada
        if (!id_area) {
          const opt = form.querySelector("[name='zona'] option:checked");
          if (opt && opt.dataset && opt.dataset.area) {
            id_area = opt.dataset.area;
          }
        }

        const nivel = form.querySelector("[name='nivel_ficha']").value.trim();
        const numeroFicha = form.querySelector("[name='numero_ficha']").value.trim();
        const instructor = form.querySelector("[name='nombre_instructor']").value.trim();
        const dia = form.querySelector("[name='dia_semana']").value.trim();
        const horaInicio = form.querySelector("[name='hora_inicio']").value.trim();
        const horaFin = form.querySelector("[name='hora_fin']").value.trim();
        const descripcion = form.querySelector("[name='descripcion']").value.trim();
        const id_competencia = form.querySelector("[name='id_competencia']") ? form.querySelector("[name='id_competencia']").value.trim() : "";

        const campos = [zona, nivel, numeroFicha, instructor, dia, horaInicio, horaFin, descripcion];
        const vacios = campos.filter(v => v === "").length;

        // ========== VALIDACIONES ==========
        if (vacios === campos.length)
          return Toast.fire({ icon: "warning", title: "Por favor llenar todos los campos" });

        if (vacios > 1)
          return Toast.fire({ icon: "warning", title: "Por favor completa todos los campos antes de enviar" });

        if (!zona) return Toast.fire({ icon: "warning", title: "Seleccione la zona" });
        if (!id_area) return Toast.fire({ icon: "warning", title: "No se identificó el área. Recarga la página o seleccione un área válida." });
        if (!nivel) return Toast.fire({ icon: "warning", title: "Seleccione el nivel de la ficha" });
        if (!numeroFicha || isNaN(numeroFicha))
          return Toast.fire({ icon: "warning", title: "Ingrese un número de ficha válido" });
        if (!instructor) return Toast.fire({ icon: "warning", title: "Ingrese el nombre del instructor" });
        if (!dia) return Toast.fire({ icon: "warning", title: "Seleccione un día de la semana" });
        if (!horaInicio) return Toast.fire({ icon: "warning", title: "Seleccione la hora de inicio" });
        if (!horaFin) return Toast.fire({ icon: "warning", title: "Seleccione la hora de fin" });

        if (parseInt(horaFin) <= parseInt(horaInicio))
          return Toast.fire({ icon: "error", title: "La hora de fin debe ser mayor a la de inicio" });

        // ========== ENVÍO ==========
        const fd = new FormData(form);

        // Enviamos también el id_area
        fd.set("area", id_area);

        // Enviamos id_competencia si se seleccionó una existente
        if (id_competencia) fd.set("id_competencia", id_competencia);

        try {
          const resp = await fetch(form.action, {
            method: "POST",
            body: fd,
            credentials: "same-origin"
          });
          const json = await resp.json();

          if (json && (json.status === "success" || json.success === true)) {
            Toast.fire({ icon: "success", title: json.mensaje || "Trimestralización creada" });
            // limpiar form
            form.reset();
            // cerrar modal si existe botón de cierre
            document.getElementById('modalCrearLanding')?.classList.add('hidden');
          } else {
            console.error("Respuesta servidor:", json);
            Toast.fire({ icon: "error", title: json.mensaje || json.error || "Error al crear trimestralización" });
          }
        } catch (err) {
          console.error("Error al enviar formulario:", err);
          Toast.fire({ icon: "error", title: "Error de conexión al enviar" });
        }
      });
    });

    // iniciar carga de competencias para autocomplete
    cargarCompetenciasDatalist();
  });
}
