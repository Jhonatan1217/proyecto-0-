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

    const TOAST_TIME = 2600; // ⏱ mismo tiempo que el toast

    // 🔁 Helper para redirigir a la vista de horario
    function redirectToHorario() {
      const base = (window.BASE_URL || '');
      const redirect = `${base}index.php?page=src/views/register_tables`;
      window.location.replace(redirect);
    }

    // 🔁 Helper para cerrar el modal de crear trimestralización (si existe)
    function cerrarModalCrear() {
      const modal = document.getElementById("modalCrearLanding");
      if (modal) modal.classList.add("hidden");
    }

    // ================== FUNCIÓN DE VALIDACIÓN REUTILIZABLE ==================
    /**
     * Valida el formulario de horario.
     * - form: elemento <form>
     * - overrideDia: si se pasa, se usa este día en lugar del seleccionado en el form
     * Devuelve:
     *   { ok:false } si algo falla (ya muestra el Toast correspondiente),
     *   { ok:true, zona, id_area, nivel, numeroFicha, instructor, dia, horaInicio, horaFin, id_competencia }
     *    si todo está correcto.
     */
    function validarFormularioHorario(form, overrideDia = null) {
      // ========== OBTENER VALORES ==========
      const zona = form.querySelector("[name='zona']").value.trim();

      // Intentamos obtener el id_area
      let areaField = form.querySelector("[name='area']");
      let id_area = areaField ? areaField.value.trim() : "";

      // Si no hay campo "area", buscamos un data-area en la opción seleccionada
      if (!id_area) {
        const optZona = form.querySelector("[name='zona'] option:checked");
        if (optZona && optZona.dataset && optZona.dataset.area) {
          id_area = optZona.dataset.area;
        }
      }

      const nivel = form.querySelector("[name='nivel_ficha']").value.trim();
      const numeroFicha = form.querySelector("[name='numero_ficha']").value.trim();
      const instructor = form.querySelector("[name='nombre_instructor']").value.trim();

      const diaForm = form.querySelector("[name='dia_semana']").value.trim();
      const dia = (overrideDia !== null && overrideDia !== undefined)
        ? String(overrideDia).trim()
        : diaForm;

      const horaInicio = form.querySelector("[name='hora_inicio']").value.trim();
      const horaFin = form.querySelector("[name='hora_fin']").value.trim();

      // id de la competencia seleccionada
      const id_competencia = form.querySelector("[name='id_competencia']")
        ? form.querySelector("[name='id_competencia']").value.trim()
        : "";

      // ✅ NUEVO: id de la(s) RAE(s) seleccionada(s) desde el hidden
      const idRaeField = form.querySelector("[name='id_rae']");
      const id_rae = idRaeField ? idRaeField.value.trim() : "";

      const campos = [zona, nivel, numeroFicha, instructor, dia, horaInicio, horaFin, id_competencia];
      const vacios = campos.filter((v) => v === "").length;

      // ========== VALIDACIONES ==========
      if (vacios === campos.length) {
        Toast.fire({ icon: "warning", title: "Por favor llenar todos los campos" });
        return { ok: false };
      }

      if (vacios > 1) {
        Toast.fire({
          icon: "warning",
          title: "Por favor completa todos los campos antes de enviar"
        });
        return { ok: false };
      }

      if (!zona) {
        Toast.fire({ icon: "warning", title: "Seleccione la zona" });
        return { ok: false };
      }

      if (!id_area) {
        Toast.fire({
          icon: "warning",
          title: "No se identificó el área. Recarga la página o seleccione un área válida."
        });
        return { ok: false };
      }

      if (!nivel) {
        Toast.fire({ icon: "warning", title: "Seleccione el nivel de la ficha" });
        return { ok: false };
      }

      if (!numeroFicha || isNaN(numeroFicha)) {
        Toast.fire({ icon: "warning", title: "Ingrese un número de ficha válido" });
        return { ok: false };
      }

      if (!instructor) {
        Toast.fire({ icon: "warning", title: "Ingrese el nombre del instructor" });
        return { ok: false };
      }

      if (!dia) {
        Toast.fire({ icon: "warning", title: "Seleccione un día de la semana" });
        return { ok: false };
      }

      if (!horaInicio) {
        Toast.fire({ icon: "warning", title: "Seleccione la hora de inicio" });
        return { ok: false };
      }

      if (!horaFin) {
        Toast.fire({ icon: "warning", title: "Seleccione la hora de fin" });
        return { ok: false };
      }

      if (parseInt(horaFin) <= parseInt(horaInicio)) {
        Toast.fire({
          icon: "error",
          title: "La hora de fin debe ser mayor a la de inicio"
        });
        return { ok: false };
      }

      if (!id_competencia) {
        Toast.fire({ icon: "warning", title: "Seleccione la competencia" });
        return { ok: false };
      }

      // ✅ NUEVO: validación formal de RAE obligatoria
      if (!id_rae) {
        Toast.fire({
          icon: "warning",
          title: "Debe seleccionar al menos una RAE asociada a la competencia."
        });
        return { ok: false };
      }

      // Si todo pasó las validaciones:
      return {
        ok: true,
        zona,
        id_area,
        nivel,
        numeroFicha,
        instructor,
        dia,
        horaInicio,
        horaFin,
        id_competencia
      };
    }

    // ================== REFERENCIAS AL MODAL PHP DE DUPLICAR ==================
    const modalDup          = document.getElementById("modalDuplicarHorario");
    const backdropDup       = document.getElementById("modalDuplicarBackdrop");
    const selDiaDup         = document.getElementById("selectDiaDuplicar");
    const msgErrorDup       = document.getElementById("mensajeErrorDuplicar");
    const btnSoloEsteDia    = document.getElementById("btnSoloEsteDia");
    const btnDuplicarDia    = document.getElementById("btnDuplicarDia");
    const btnCerrarDup      = document.getElementById("btnCerrarModalDuplicar");

    // Contexto que usará el modal de duplicar
    let duplicacionCtx = {
      form: null,
      diaOriginal: "",
      id_area: "",
      id_competencia: ""
    };

    function limpiarModalDuplicar() {
      if (selDiaDup) {
        selDiaDup.value = "";
        // Mostrar todas las opciones por si antes se ocultó alguna
        Array.from(selDiaDup.options).forEach(opt => {
          opt.hidden = false;
          opt.disabled = false;
        });
      }
      if (msgErrorDup) msgErrorDup.classList.add("hidden");
    }

    function abrirModalDuplicar(ctx) {
      if (!modalDup || !selDiaDup) {
        // Si por alguna razón no existe el modal, solo redirigimos
        cerrarModalCrear();
        redirectToHorario();
        return;
      }

      duplicacionCtx = ctx;
      limpiarModalDuplicar();

      const { diaOriginal } = duplicacionCtx;

      // Ocultamos/deshabilitamos del select el día original
      if (diaOriginal) {
        Array.from(selDiaDup.options).forEach(opt => {
          if (!opt.value) return;
          if (opt.value === diaOriginal) {
            opt.disabled = true;
            opt.hidden = true;
          }
        });
      }

      modalDup.classList.remove("hidden");
    }

    function cerrarModalDuplicar(soloCerrar = false) {
      if (modalDup) modalDup.classList.add("hidden");

      // soloCerrar = true  -> solo cierra el modal
      // soloCerrar = false -> cerrar y redirigir (caso cancelar / solo este día)
      if (!soloCerrar) {
        cerrarModalCrear();
        redirectToHorario();
      }
    }

    // ✅ Helper para el caso "No, solo este día" (o cerrar el modal)
    // Aquí es donde mostramos la alerta de "Horario creado correctamente"
    function confirmarSoloEsteDia() {
      if (modalDup) modalDup.classList.add("hidden");
      Toast.fire({
        icon: "success",
        title: "¡Horario creado correctamente!"
      });
      cerrarModalCrear();
      // ⏱ Damos tiempo a que se vea el toast ANTES de redirigir
      setTimeout(() => {
        redirectToHorario();
      }, TOAST_TIME);
    }

    // ========== EVENTOS DEL MODAL DE DUPLICAR ==========

    // Botón: "No, solo este día" -> cierra modal, muestra toast y redirige
    if (btnSoloEsteDia) {
      btnSoloEsteDia.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    // Botón cerrar (X) -> se comporta como "solo este día"
    if (btnCerrarDup) {
      btnCerrarDup.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    // Clic en fondo del modal -> también como "solo este día"
    if (backdropDup) {
      backdropDup.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    // Botón: "Sí, duplicar horario"
    if (btnDuplicarDia) {
      btnDuplicarDia.addEventListener("click", async () => {
        if (!selDiaDup) return;

        const diaDestino = selDiaDup.value;
        const { form, diaOriginal } = duplicacionCtx;

        // Validación: debe escoger un día y no puede ser el mismo
        if (!diaDestino || diaDestino === diaOriginal) {
          if (msgErrorDup) msgErrorDup.classList.remove("hidden");
          return;
        }
        if (msgErrorDup) msgErrorDup.classList.add("hidden");

        // Si por alguna razón perdimos el form, cerramos y redirigimos
        if (!form) {
          cerrarModalDuplicar(false);
          return;
        }

        // 🛡️ MISMAS VALIDACIONES QUE EL FORMULARIO DE CREAR,
        // pero usando el día destino del select del modal.
        const resultadoVal = validarFormularioHorario(form, diaDestino);
        if (!resultadoVal.ok) {
          // Si algo falla, NO se envía el fetch de duplicar.
          return;
        }

        const { id_area, id_competencia } = resultadoVal;

        // 🔁 Armamos formData para la segunda inserción, cambiando solo el día
        const fd2 = new FormData(form);
        fd2.set("dia_semana", diaDestino);
        fd2.set("area", id_area);
        fd2.set("duplicar_desde", diaOriginal || "");

        try {
          const id_rae_field = form.querySelector("[name='id_rae']");
          const selOpt = form.querySelector("[name='id_competencia'] option:checked");

          if (id_competencia) {
            fd2.set("id_competencia", id_competencia);
          }

          // programa desde el data-attribute de la competencia
          const programa = selOpt && selOpt.dataset ? (selOpt.dataset.programa || "") : "";
          fd2.set("id_programa", programa);

          // RAEs desde el hidden
          const rae = id_rae_field ? (id_rae_field.value || "") : "";
          fd2.set("id_rae", rae);
        } catch (err) {
          console.warn("No se pudo anexar id_programa/id_rae al FormData (duplicado)", err);
        }

        try {
          const res2 = await fetch(form.action, {
            method: "POST",
            body: fd2,
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "Accept": "application/json"
            },
            credentials: "same-origin"
          });

          const data2 = await res2.json().catch(() => ({}));

          if (!res2.ok || data2.status === "error" || data2.error) {
            const mensaje2 =
              data2.mensaje ||
              data2.error ||
              "El horario original se guardó, pero hubo un error al duplicarlo en el otro día.";

            // ⚠️ MOSTRAMOS EL ERROR Y NO REDIRIGIMOS,
            // para que el usuario pueda ver la validación y corregir.
            Toast.fire({ icon: "warning", title: mensaje2 });
            // No cerramos ni redirigimos aquí.
            return;
          }

          // ✅ Todo bien: original + duplicado
          Toast.fire({
            icon: "success",
            title: "¡Horario creado correctamente!"
          });

          // Cerramos modales y redirigimos después del toast
          cerrarModalDuplicar(true); // solo cerrar modal duplicar
          cerrarModalCrear();
          setTimeout(() => {
            redirectToHorario();
          }, TOAST_TIME);

        } catch (err) {
          console.error("Error duplicando horario:", err);
          Toast.fire({
            icon: "error",
            title: "Error al duplicar horario"
          });
          // Tampoco redirigimos de una, para que se vea la alerta
          return;
        }
      });
    }

    // ================= LÓGICA PRINCIPAL DEL FORM =================
    document.querySelectorAll(".trimestralizacion-form").forEach((form) => {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // 🛡️ Usamos la MISMA función de validación
        const resultado = validarFormularioHorario(form);
        if (!resultado.ok) {
          // Si algo falla, no se envía nada
          return;
        }

        const {
          id_area,
          dia,
          id_competencia
        } = resultado;

        // ========== ENVÍO PRIMER HORARIO ==========
        const fd = new FormData(form);

        // Enviamos también el id_area
        fd.set("area", id_area);

        // Asegurar que id_competencia, id_programa e id_rae se envían explícitamente.
        try {
          const selOpt = form.querySelector("[name='id_competencia'] option:checked");
          const id_rae_field = form.querySelector("[name='id_rae']");

          if (id_competencia) fd.set("id_competencia", id_competencia);

          // Obtener programa desde el data-attribute de la competencia
          const programa = selOpt && selOpt.dataset ? selOpt.dataset.programa || "" : "";
          fd.set("id_programa", programa);

          // Obtener RAEs del campo hidden (que se rellenó en el modal de selección)
          const rae = id_rae_field ? id_rae_field.value || "" : "";
          fd.set("id_rae", rae);
        } catch (err) {
          console.warn("No se pudo anexar id_programa/id_rae al FormData", err);
        }

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
            // ⚠️ Solo mostramos el toast, NO redirigimos
            return Toast.fire({ icon: "error", title: mensaje });
          }

          // 🔥 Ya NO mostramos aquí el toast de éxito.
          // El mensaje de éxito se muestra cuando el usuario decide
          // si duplica o no el horario.

          // 🔥 Ahora abrimos el modal PHP de duplicar horario
          abrirModalDuplicar({
            form,
            diaOriginal: dia,
            id_area,
            id_competencia
          });

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
