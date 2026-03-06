// src/assets/js/formulario_trimestralizacion.js
if (!window.TRIMESTRALIZACION_INIT) {
  window.TRIMESTRALIZACION_INIT = true;

  document.addEventListener("DOMContentLoaded", () => {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2600,
      timerProgressBar: true
    });

    const TOAST_TIME = 2600;

    function redirectToHorario() {
      const base = (window.BASE_URL || '');
      const redirect = `${base}index.php?page=src/views/register_tables`;
      window.location.replace(redirect);
    }

    function cerrarModalCrear() {
      const modal = document.getElementById("modalCrearLanding");
      if (modal) modal.classList.add("hidden");
    }

    function validarFormularioHorario(form, overrideDia = null) {
      const zona = form.querySelector("[name='zona']").value.trim();

      let areaField = form.querySelector("[name='area']");
      let id_area = areaField ? areaField.value.trim() : "";

      if (!id_area) {
        const optZona = form.querySelector("[name='zona'] option:checked");
        if (optZona && optZona.dataset && optZona.dataset.area) {
          id_area = optZona.dataset.area;
        }
      }

      const numeroFicha = form.querySelector("[name='numero_ficha']").value.trim();
      const instructor = form.querySelector("[name='nombre_instructor']").value.trim();

      const diaForm = form.querySelector("[name='dia_semana']").value.trim();
      const dia = (overrideDia !== null && overrideDia !== undefined)
        ? String(overrideDia).trim()
        : diaForm;

      const horaInicio = form.querySelector("[name='hora_inicio']").value.trim();
      const horaFin = form.querySelector("[name='hora_fin']").value.trim();

      const id_competencia = form.querySelector("[name='id_competencia']")
        ? form.querySelector("[name='id_competencia']").value.trim()
        : "";

      const idRaeField = form.querySelector("[name='id_rae']");
      const id_rae = idRaeField ? idRaeField.value.trim() : "";

      const campos = [zona, numeroFicha, instructor, dia, horaInicio, horaFin, id_competencia];
      const vacios = campos.filter((v) => v === "").length;

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

      if (!id_rae) {
        Toast.fire({
          icon: "warning",
          title: "Debe seleccionar al menos una RAE asociada a la competencia."
        });
        return { ok: false };
      }

      return {
        ok: true,
        zona,
        id_area,
        numeroFicha,
        instructor,
        dia,
        horaInicio,
        horaFin,
        id_competencia
      };
    }

    // ================== MODAL DUPLICAR ==================
    const modalDup       = document.getElementById("modalDuplicarHorario");
    const backdropDup    = document.getElementById("modalDuplicarBackdrop");
    const selDiaDup      = document.getElementById("selectDiaDuplicar");
    const checklistDias  = document.getElementById("checklistDias");
    const msgErrorDup    = document.getElementById("mensajeErrorDuplicar");
    const btnSoloEsteDia = document.getElementById("btnSoloEsteDia");
    const btnDuplicarDia = document.getElementById("btnDuplicarDia");
    const btnCerrarDup   = document.getElementById("btnCerrarModalDuplicar");

    let duplicacionCtx = {
      form: null,
      diaOriginal: "",
      id_area: "",
      id_competencia: ""
    };

    function limpiarModalDuplicar() {
      if (selDiaDup) {
        Array.from(selDiaDup.options).forEach(opt => {
          opt.selected = false;
          opt.hidden = false;
          opt.disabled = false;
        });
      }
      if (checklistDias) {
        checklistDias.querySelectorAll(".chk-dia-duplicar").forEach(ch => {
          ch.checked = false;
          ch.disabled = false;
          if (ch.parentElement) {
            ch.parentElement.classList.remove("opacity-50", "pointer-events-none");
          }
        });
      }
      if (msgErrorDup) msgErrorDup.classList.add("hidden");
    }

    function abrirModalDuplicar(ctx) {
      if (!modalDup || !selDiaDup) {
        cerrarModalCrear();
        redirectToHorario();
        return;
      }

      duplicacionCtx = ctx;
      limpiarModalDuplicar();

      const { diaOriginal } = duplicacionCtx;

      if (diaOriginal) {
        Array.from(selDiaDup.options).forEach(opt => {
          if (!opt.value) return;
          if (opt.value === diaOriginal) {
            opt.disabled = true;
            opt.hidden = true;
          }
        });

        if (checklistDias) {
          checklistDias.querySelectorAll(".chk-dia-duplicar").forEach(ch => {
            if (ch.value === diaOriginal) {
              ch.checked = false;
              ch.disabled = true;
              if (ch.parentElement) {
                ch.parentElement.classList.add("opacity-50", "pointer-events-none");
              }
            }
          });
        }
      }

      modalDup.classList.remove("hidden");
    }

    function cerrarModalDuplicar(soloCerrar = false) {
      if (modalDup) modalDup.classList.add("hidden");
      if (!soloCerrar) {
        cerrarModalCrear();
        redirectToHorario();
      }
    }

    function confirmarSoloEsteDia() {
      if (modalDup) modalDup.classList.add("hidden");
      Toast.fire({
        icon: "success",
        title: "¡Horario creado correctamente!"
      });
      cerrarModalCrear();
      setTimeout(() => {
        redirectToHorario();
      }, TOAST_TIME);
    }

    // 🔄 Sincronizar checklist -> select oculto
    if (checklistDias && selDiaDup) {
      checklistDias.addEventListener("change", (e) => {
        const target = e.target;
        if (!target.classList.contains("chk-dia-duplicar")) return;
        const value = target.value;
        const opt = Array.from(selDiaDup.options).find(o => o.value === value);
        if (opt) {
          opt.selected = target.checked;
        }
      });
    }

    if (btnSoloEsteDia) {
      btnSoloEsteDia.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    if (btnCerrarDup) {
      btnCerrarDup.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    if (backdropDup) {
      backdropDup.addEventListener("click", () => {
        confirmarSoloEsteDia();
      });
    }

    if (btnDuplicarDia) {
      btnDuplicarDia.addEventListener("click", async () => {
        if (!selDiaDup) return;

        const { form, diaOriginal } = duplicacionCtx;

        if (!form) {
          cerrarModalDuplicar(false);
          return;
        }

        const diasSeleccionados = Array.from(selDiaDup.options)
          .filter(opt => opt.selected && opt.value && opt.value !== diaOriginal)
          .map(opt => opt.value);

        if (!diasSeleccionados.length) {
          if (msgErrorDup) msgErrorDup.classList.remove("hidden");
          return;
        }
        if (msgErrorDup) msgErrorDup.classList.add("hidden");

        let huboError = false;
        let mensajeError = "";

        for (const diaDestino of diasSeleccionados) {
          const resultadoVal = validarFormularioHorario(form, diaDestino);
          if (!resultadoVal.ok) {
            huboError = true;
            mensajeError = "El horario original se guardó, pero uno de los duplicados no es válido.";
            break;
          }

          const { id_area, id_competencia } = resultadoVal;

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

            const programa = selOpt && selOpt.dataset ? (selOpt.dataset.programa || "") : "";
            fd2.set("id_programa", programa);

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
                "El horario original se guardó, pero hubo un error al duplicarlo en uno de los días seleccionados.";

              huboError = true;
              mensajeError = mensaje2;
              break;
            }

          } catch (err) {
            console.error("Error duplicando horario:", err);
            huboError = true;
            mensajeError = "Error al duplicar horario en uno de los días seleccionados.";
            break;
          }
        }

        if (huboError) {
          Toast.fire({
            icon: "warning",
            title: mensajeError || "El horario original se guardó, pero hubo errores al duplicar."
          });
          return;
        }

        Toast.fire({
          icon: "success",
          title: "¡Horario creado y duplicado correctamente!"
        });

        cerrarModalDuplicar(true);
        cerrarModalCrear();
        setTimeout(() => {
          redirectToHorario();
        }, TOAST_TIME);
      });
    }

    

    // ================= LÓGICA PRINCIPAL DEL FORM =================
    document.querySelectorAll(".trimestralizacion-form").forEach((form) => {
      form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const resultado = validarFormularioHorario(form);
        if (!resultado.ok) {
          return;
        }

        const {
          id_area,
          dia,
          id_competencia
        } = resultado;

        const fd = new FormData(form);
        fd.set("area", id_area);

        try {
          const selOpt = form.querySelector("[name='id_competencia'] option:checked");
          const id_rae_field = form.querySelector("[name='id_rae']");

          if (id_competencia) fd.set("id_competencia", id_competencia);

          const programa = selOpt && selOpt.dataset ? selOpt.dataset.programa || "" : "";
          fd.set("id_programa", programa);

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

          if (!res.ok || data.status === "error" || data.error) {
            const mensaje = data.mensaje || data.error || "Ocurrió un error en el servidor.";
            return Toast.fire({ icon: "error", title: mensaje });
          }

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


