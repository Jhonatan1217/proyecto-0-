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

    function dispararToastsExcedente(warnings) {
      const instructores = Array.isArray(warnings?.instructores) ? warnings.instructores : [];
      const grupos = Array.isArray(warnings?.grupos) ? warnings.grupos : [];

      instructores.forEach((inst) => {
        Toast.fire({
          icon: "warning",
          title: `Atención: El instructor ha superado su límite de carga horaria (${inst.nombre_instructor})`
        });
      });

      grupos.forEach((grupo) => {
        Toast.fire({
          icon: "info",
          title: `Aviso: El grupo ${grupo.id_grupo} ha excedido las 30 horas reglamentarias`
        });
      });
    }

    function redirectToHorario(filtros = {}) {
      const base = (window.BASE_URL || '');
      const params = new URLSearchParams();
      params.set('page', 'src/views/register_tables');

      const modalidadRaw = String(filtros?.modalidad || 'presencial').trim().toLowerCase();
      const modalidad = modalidadRaw === 'mixta' ? 'mixto' : modalidadRaw;
      params.set('modalidad', modalidad || 'presencial');

      if (modalidad === 'virtual' || modalidad === 'mixto') {
        const numeroFicha = String(filtros?.numero_ficha || filtros?.numeroFicha || '').trim();
        if (numeroFicha) params.set('numero_ficha', numeroFicha);
      } else {
        const idArea = String(filtros?.id_area || '').trim();
        const idZona = String(filtros?.id_zona || filtros?.zona || '').trim();
        if (idArea) params.set('id_area', idArea);
        if (idZona) params.set('id_zona', idZona);
      }

      const redirect = `${base}index.php?${params.toString()}`;
      window.location.replace(redirect);
    }

    function cerrarModalCrear() {
      const modal = document.getElementById("modalCrearLanding");
      if (!modal) return;
      const activeEl = document.activeElement;
      if (activeEl && modal.contains(activeEl)) activeEl.blur();
      modal.classList.add("hidden");
      modal.classList.remove("flex", "block", "items-center", "justify-center");
      modal.style.display = "none";
      modal.style.pointerEvents = "none";
      modal.style.visibility = "hidden";
      modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = "none"; });
      document.body.style.overflow = "";
      document.body.classList.remove("overflow-hidden");
      const btnAbrir = document.getElementById("btnAbrirModal");
      if (btnAbrir?.focus) try { btnAbrir.focus(); } catch (e) {}
    }

    function enModoVistaPreviaLocal() {
      return !!(window.RegisterTables && window.RegisterTables.state && Array.isArray(window.RegisterTables.state.horariosCache));
    }

    function construirRegistroHorarioLocal(form, resultadoVal) {
      const selInstructor = form.querySelector("[name='nombre_instructor'] option:checked");
      const selComp = form.querySelector("[name='id_competencia'] option:checked");
      const selFicha = form.querySelector("[name='numero_ficha'] option:checked");
      const idRaeField = form.querySelector("[name='id_rae']");
      const raesRaw = idRaeField ? String(idRaeField.value || "").trim() : "";
      const raes = raesRaw ? raesRaw.split(",").map((s) => s.trim()).filter(Boolean) : [];
      const descripcion = String(
        form.querySelector("[name='descripcion_jornada']")?.value ??
        form.querySelector("[name='descripcion']")?.value ??
        ""
      ).trim();
      const tmpId = "tmp_" + Date.now() + "_" + Math.floor(Math.random() * 10000);

      return {
        id_horario: tmpId,
        dia: resultadoVal.dia,
        hora_inicio: resultadoVal.horaInicio,
        hora_fin: resultadoVal.horaFin,
        numero_ficha: resultadoVal.numeroFicha,
        id_instructor: resultadoVal.instructor,
        id_competencia: resultadoVal.id_competencia,
        id_zona: resultadoVal.zona || "",
        id_area: resultadoVal.id_area || "",
        modalidad: (resultadoVal.modalidad || "").toUpperCase(),
        descripcion_jornada: descripcion,
        raes: raes,
        raesArray: raes,
        nombre_instructor: selInstructor ? selInstructor.textContent.trim() : "",
        nombre_competencia: selComp ? selComp.textContent.trim() : "",
        nombre_programa: selComp && selComp.dataset ? String(selComp.dataset.programa || "").trim() : "",
        nivel_ficha: selFicha && selFicha.dataset ? String(selFicha.dataset.nivel || "").trim() : "",
      };
    }

    function aplicarRegistroLocalYRefrescar(registro) {
      if (!enModoVistaPreviaLocal()) return false;
      const RT = window.RegisterTables;
      RT.state.horariosCache.push(registro);
      RT.state.huboCambios = true;
      if (RT.grid && typeof RT.grid.renderizarTablaDesdeRegistros === "function") {
        RT.grid.renderizarTablaDesdeRegistros(RT.state.horariosCache, "", { filtersApplied: true });
      }
      if (RT.solicitud && typeof RT.solicitud.detectarCambios === "function") {
        RT.solicitud.detectarCambios();
      }
      return true;
    }

    function validarFormularioHorario(form, overrideDia = null) {
      const modalidad = (form.querySelector("[name='modalidad']")?.value || "").trim().toLowerCase();
      const esPresencial = modalidad === "presencial";
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

      const campos = esPresencial
        ? [modalidad, zona, numeroFicha, instructor, dia, horaInicio, horaFin, id_competencia]
        : [modalidad, numeroFicha, instructor, dia, horaInicio, horaFin, id_competencia];
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

      if (!modalidad) {
        Toast.fire({ icon: "warning", title: "Seleccione la modalidad" });
        return { ok: false };
      }

      if (esPresencial && !zona) {
        Toast.fire({ icon: "warning", title: "Seleccione la zona" });
        return { ok: false };
      }

      if (esPresencial && !id_area) {
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
        modalidad,
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

    function configurarModalidadCrear() {
      const modalidadSel = document.getElementById("modalidad");
      const contAreaZona = document.getElementById("contenedorAreaZonaCrear");
      const selArea = document.getElementById("id_area");
      const selZona = document.getElementById("id_zona");
      const inpArea = document.getElementById("id_area_combo");
      const inpZona = document.getElementById("id_zona_combo");

      if (!modalidadSel || !contAreaZona) return;

      const aplicar = () => {
        const modalidad = String(modalidadSel.value || "").trim().toLowerCase();
        const ocultar = modalidad === "virtual" || modalidad === "mixto";

        contAreaZona.style.display = ocultar ? "none" : "";

        if (ocultar) {
          if (selArea) selArea.value = "";
          if (selZona) selZona.value = "";
          if (inpArea) inpArea.value = "";
          if (inpZona) {
            inpZona.value = "";
            inpZona.disabled = true;
          }
        }
      };

      modalidadSel.addEventListener("change", aplicar);
      aplicar();
    }

    // ================== MODAL DUPLICAR ==================
    configurarModalidadCrear();

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
      id_competencia: "",
      modalidad: "presencial",
      id_zona: "",
      numero_ficha: ""
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
        redirectToHorario(ctx || {});
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
      modalDup.style.display = "";
      modalDup.style.pointerEvents = "";
      modalDup.style.visibility = "";
      modalDup.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = ""; });
    }

    function cerrarModalDuplicar(soloCerrar = false) {
      if (modalDup) {
        const activeEl = document.activeElement;
        if (activeEl && modalDup.contains(activeEl)) activeEl.blur();
        modalDup.classList.add("hidden");
        modalDup.classList.remove("flex", "block", "items-center", "justify-center");
        modalDup.style.display = "none";
        modalDup.style.pointerEvents = "none";
        modalDup.style.visibility = "hidden";
        modalDup.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = "none"; });
      }
      if (!soloCerrar) {
        cerrarModalCrear();
        if (!enModoVistaPreviaLocal()) {
          redirectToHorario(duplicacionCtx);
        }
      }
    }

    function confirmarSoloEsteDia() {
      if (modalDup) {
        const activeEl = document.activeElement;
        if (activeEl && modalDup.contains(activeEl)) activeEl.blur();
        modalDup.classList.add("hidden");
        modalDup.classList.remove("flex", "block", "items-center", "justify-center");
        modalDup.style.display = "none";
        modalDup.style.pointerEvents = "none";
        modalDup.style.visibility = "hidden";
        modalDup.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = "none"; });
      }
      Swal.fire({
        icon: "success",
        title: enModoVistaPreviaLocal() ? "Horario agregado en vista previa" : "Trimestralización creada",
        text: enModoVistaPreviaLocal()
          ? "El horario solo es visible para usted hasta enviarlo."
          : "El horario se guardó correctamente.",
        confirmButtonText: "Aceptar"
      });
      cerrarModalCrear();
      if (!enModoVistaPreviaLocal()) {
        setTimeout(() => {
          redirectToHorario(duplicacionCtx);
        }, TOAST_TIME);
      }
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

          if (enModoVistaPreviaLocal()) {
            const regDup = construirRegistroHorarioLocal(form, resultadoVal);
            aplicarRegistroLocalYRefrescar(regDup);
            continue;
          }

          const { id_area, id_competencia } = resultadoVal;
          const fd2 = new FormData(form);
          fd2.set("dia_semana", diaDestino);
          fd2.set("area", id_area);
          fd2.set("duplicar_desde", diaOriginal || "");
          try {
            const id_rae_field = form.querySelector("[name='id_rae']");
            const selOpt = form.querySelector("[name='id_competencia'] option:checked");
            if (id_competencia) fd2.set("id_competencia", id_competencia);
            const programa = selOpt && selOpt.dataset ? (selOpt.dataset.programa || "") : "";
            fd2.set("id_programa", programa);
            const rae = id_rae_field ? (id_rae_field.value || "") : "";
            fd2.set("id_rae", rae);
            const res2 = await fetch(form.action, {
              method: "POST",
              body: fd2,
              headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
              credentials: "same-origin"
            });
            const data2 = await res2.json().catch(() => ({}));
            if (!res2.ok || data2.status === "error" || data2.error) {
              const mensaje2 = data2.mensaje || data2.error || "El horario original se guardó, pero hubo un error al duplicarlo.";
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
          Swal.fire({
            icon: "warning",
            title: "Creado con observaciones",
            text: mensajeError || "El horario original se guardó, pero hubo errores al duplicar.",
            confirmButtonText: "Entendido"
          });
          Toast.fire({
            icon: "warning",
            title: mensajeError || "El horario original se guardó, pero hubo errores al duplicar."
          });
          return;
        }

        Swal.fire({
          icon: "success",
          title: enModoVistaPreviaLocal() ? "Vista previa creada y duplicada" : "Trimestralización creada y duplicada",
          text: enModoVistaPreviaLocal()
            ? "Los horarios quedan en vista previa local hasta enviarlos."
            : "Se guardó el horario en los días seleccionados.",
          confirmButtonText: "Aceptar"
        });
        Toast.fire({
          icon: "success",
          title: enModoVistaPreviaLocal()
            ? "Horario agregado a vista previa"
            : "¡Horario creado y duplicado correctamente!"
        });

        cerrarModalDuplicar(true);
        cerrarModalCrear();
        if (!enModoVistaPreviaLocal()) {
          setTimeout(() => {
            redirectToHorario(duplicacionCtx);
          }, TOAST_TIME);
        }
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
          modalidad,
          zona,
          numeroFicha,
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
          if (enModoVistaPreviaLocal()) {
            const regNuevo = construirRegistroHorarioLocal(form, resultado);
            aplicarRegistroLocalYRefrescar(regNuevo);
            Toast.fire({
              icon: "success",
              title: "Horario agregado en vista previa",
              text: "Ahora puedes decidir si deseas duplicarlo en otros días."
            });
            abrirModalDuplicar({
              form,
              diaOriginal: dia,
              id_area,
              id_competencia,
              modalidad,
              id_zona: zona,
              numero_ficha: numeroFicha
            });
            return;
          }

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
            Swal.fire({
              icon: "error",
              title: "No se pudo crear",
              text: mensaje,
              confirmButtonText: "Entendido"
            });
            return Toast.fire({ icon: "error", title: mensaje });
          }

          dispararToastsExcedente(data.warnings);

          Toast.fire({
            icon: "success",
            title: "Trimestralización creada",
            text: "Ahora puedes decidir si deseas duplicarla en otros días."
          });

          abrirModalDuplicar({
            form,
            diaOriginal: dia,
            id_area,
            id_competencia,
            modalidad,
            id_zona: zona,
            numero_ficha: numeroFicha
          });

        } catch (err) {
          console.error("Error de red:", err);
          Swal.fire({
            icon: "error",
            title: "Error de conexión",
            text: "No fue posible crear la trimestralización. Verifica tu conexión e intenta de nuevo.",
            confirmButtonText: "Entendido"
          });
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


