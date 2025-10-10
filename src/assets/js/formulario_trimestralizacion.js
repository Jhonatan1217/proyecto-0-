// src/assets/js/formulario_trimestralizacion.js
document.addEventListener("DOMContentLoaded", () => {
  const REDIRECT_URL = "index.php?page=landing";

  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true
  });

  // Selecciona todos los formularios que tengan la clase "trimestralizacion-form"
  document.querySelectorAll(".trimestralizacion-form").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

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

      if (vacios === campos.length) return Toast.fire({ icon: "warning", title: "Por favor llenar todos los campos" });
      if (vacios > 1) return Toast.fire({ icon: "warning", title: "Por favor completa todos los campos antes de enviar" });
      if (!zona) return Toast.fire({ icon: "warning", title: "Seleccione la zona" });
      if (!nivel) return Toast.fire({ icon: "warning", title: "Seleccione el nivel de la ficha" });
      if (!numeroFicha || isNaN(numeroFicha)) return Toast.fire({ icon: "warning", title: "Ingrese un número de ficha válido" });
      if (!instructor) return Toast.fire({ icon: "warning", title: "Ingrese el nombre del instructor" });
      if (!tipo) return Toast.fire({ icon: "warning", title: "Seleccione el tipo de instructor" });
      if (!dia) return Toast.fire({ icon: "warning", title: "Seleccione un día de la semana" });
      if (!horaInicio) return Toast.fire({ icon: "warning", title: "Seleccione la hora de inicio" });
      if (!horaFin) return Toast.fire({ icon: "warning", title: "Seleccione la hora de fin" });
      if (parseInt(horaFin) <= parseInt(horaInicio)) return Toast.fire({ icon: "error", title: "La hora de fin debe ser mayor a la de inicio" });
      if (!descripcion) return Toast.fire({ icon: "warning", title: "Ingrese la competencia o descripción" });

      const fd = new FormData(form);

      fetch(form.action, {
        method: "POST",
        body: fd,
        redirect: "manual",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin"
      })
      .then(async (res) => {
        if (res.ok || res.type === "opaqueredirect") {
          Toast.fire({ icon: "success", title: "¡Trimestralización creada correctamente!" });
          setTimeout(() => window.location.replace(REDIRECT_URL), 1600);
        } else {
          let msg = "";
          try { msg = (await res.text()).slice(0, 200); } catch {}
          Toast.fire({
            icon: "error",
            title: "No se pudo crear",
            text: msg || "Ocurrió un error. Intenta de nuevo."
          });
        }
      })
      .catch((err) => {
        console.error(err);
        Toast.fire({
          icon: "error",
          title: "Error de red",
          text: "Revisa tu conexión e intenta de nuevo."
        });
      });
    });
  });
});
