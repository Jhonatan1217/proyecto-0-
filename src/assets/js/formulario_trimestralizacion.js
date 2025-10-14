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

    // Selecciona todos los formularios con la clase "trimestralizacion-form"
    document.querySelectorAll(".trimestralizacion-form").forEach((form) => {

      form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // ========== OBTENER VALORES ==========
        const zona = form.querySelector("[name='zona']").value.trim();
        const nivel = form.querySelector("[name='nivel_ficha']").value.trim();
        const numeroFicha = form.querySelector("[name='numero_ficha']").value.trim();
        const instructor = form.querySelector("[name='nombre_instructor']").value.trim();
        const tipo = form.querySelector("[name='tipo_instructor']").value.trim();
        const dia = form.querySelector("[name='dia_semana']").value.trim();
        const horaInicio = form.querySelector("[name='hora_inicio']").value.trim();
        const horaFin = form.querySelector("[name='hora_fin']").value.trim();
        const descripcion = form.querySelector("[name='descripcion']").value.trim();

        const campos = [zona, nivel, numeroFicha, instructor, tipo, dia, horaInicio, horaFin, descripcion];
        const vacios = campos.filter(v => v === "").length;

        // ========== VALIDACIONES ==========
        if (vacios === campos.length)
          return Toast.fire({ icon: "warning", title: "Por favor llenar todos los campos" });

        if (vacios > 1)
          return Toast.fire({ icon: "warning", title: "Por favor completa todos los campos antes de enviar" });

        if (!zona) return Toast.fire({ icon: "warning", title: "Seleccione la zona" });
        if (!nivel) return Toast.fire({ icon: "warning", title: "Seleccione el nivel de la ficha" });
        if (!numeroFicha || isNaN(numeroFicha))
          return Toast.fire({ icon: "warning", title: "Ingrese un número de ficha válido" });
        if (!instructor) return Toast.fire({ icon: "warning", title: "Ingrese el nombre del instructor" });
        if (!tipo) return Toast.fire({ icon: "warning", title: "Seleccione el tipo de instructor" });
        if (!dia) return Toast.fire({ icon: "warning", title: "Seleccione un día de la semana" });
        if (!horaInicio) return Toast.fire({ icon: "warning", title: "Seleccione la hora de inicio" });
        if (!horaFin) return Toast.fire({ icon: "warning", title: "Seleccione la hora de fin" });

        if (parseInt(horaFin) <= parseInt(horaInicio))
          return Toast.fire({ icon: "error", title: "La hora de fin debe ser mayor a la de inicio" });

        if (!descripcion)
          return Toast.fire({ icon: "warning", title: "Ingrese la competencia o descripción" });

        // ========== ENVÍO ==========
        const fd = new FormData(form);

        try {
          const res = await fetch(form.action, {
            method: "POST",
            body: fd,
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "Accept": "application/json"
            },
            credentials: "same-origin"
          });

          const data = await res.json().catch(() => ({}));

          // ---------- ERRORES DEL SERVIDOR ----------
          if (!res.ok || data.status === "error" || data.error) {
            const mensaje = data.mensaje || data.error || "Ocurrió un error en el servidor.";
            return Toast.fire({ icon: "error", title: mensaje });
          }

          // ---------- ÉXITO ----------
          Toast.fire({ icon: "success", title: "¡Trimestralización creada correctamente!" });

          // Cerrar modal si existe (por ejemplo, en landing)
          const modal = document.getElementById("modalCrearLanding");
          if (modal) modal.classList.add("hidden");

          // Redirigir a la tabla de la zona seleccionada
          const id_zona = zona;
          const redirect = `index.php?page=src/views/register_tables&id_zona=${id_zona}`;
          setTimeout(() => window.location.replace(redirect), 1600);

        } catch (err) {
          console.error("Error de red:", err);
          Toast.fire({
            icon: "error",
            title: "Error de red o respuesta inválida",
            text: "Verifica tu conexión e intenta de nuevo"
          });
        }
      });
    });
  });
}
// BORREN ESTO