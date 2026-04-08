/**
 * Arranque único: listeners y carga inicial.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const GH = RT.gestionHoras;
  const UI = RT.ui;
  const ED = RT.edit;
  const MD = RT.modals;

  async function initRegisterTables() {
    if (typeof ComboboxComponent !== "undefined" && typeof ComboboxComponent.enhanceSelectStyled === "function") {
      ComboboxComponent.enhanceSelectStyled({
        selector: "#selectModalidad",
        placeholder: "Seleccione la modalidad",
        placeholderValues: [""],
      });
    }

    UI.cargarFiltrosSuperiores();
    await UI.cargarAreasYZonas();
    UI.configurarFiltros();
    RT.data.cargarFichas();
    RT.data.cargarInstructores();
    RT.data.cargarCompetencias();
    RT.data.configurarModalidadFormulario();

    if (S.id_zona) {
      RT.data.cargarTrimestralizacion();
    } else {
      UI.toggleTabla(false);
    }

    const btnActualizar = document.getElementById("btn-actualizar");
    if (btnActualizar) {
      btnActualizar.addEventListener("click", GH.abrirModalGestionHoras);
    }

    document.getElementById("btnCerrarGestionHoras")?.addEventListener("click", GH.cerrarModalGestionHoras);
    document.getElementById("btnAceptarGestionHoras")?.addEventListener("click", GH.cerrarModalGestionHoras);
    document.getElementById("tabGestionHorasInstructores")?.addEventListener("click", () => {
      S.gestionHorasTabActual = "instructores";
      GH.renderGestionHoras();
    });
    document.getElementById("tabGestionHorasGrupos")?.addEventListener("click", () => {
      S.gestionHorasTabActual = "grupos";
      GH.renderGestionHoras();
    });
    document.querySelector("#modalGestionHoras .gh-backdrop")?.addEventListener("click", GH.cerrarModalGestionHoras);
    document.getElementById("btnIrGestionInstructores")?.addEventListener("click", () => {
      const cfg = GH.getGestionHorasAccionConfig();
      w.location.href = cfg.url;
    });
    document.addEventListener("keydown", (event) => {
      if (event.key !== "Escape") return;
      const modal = document.getElementById("modalGestionHoras");
      if (!modal || modal.classList.contains("hidden")) return;
      GH.cerrarModalGestionHoras();
    });

    const mostrarAlertaSinConexion = () => {
      Swal.fire({
        icon: "error",
        title: "Sin conexión a la red",
        text: "No se pudo completar la consulta. Verifica tu conexión e intenta nuevamente.",
        showCancelButton: true,
        confirmButtonText: "Volver al inicio",
        cancelButtonText: "Cerrar",
      }).then((result) => {
        if (result.isConfirmed) {
          w.location.href = `${RT.API_BASE}index.php?page=landing`;
        }
      });
    };

    w.addEventListener("offline", mostrarAlertaSinConexion);

    document.addEventListener("click", (event) => {
      if (!navigator.onLine) {
        const objetivo = event.target.closest("button, a, select, input");
        if (objetivo) {
          mostrarAlertaSinConexion();
        }
      }
    });

    const btnAbrirModal = document.getElementById("btnAbrirModal");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const backdrop = document.getElementById("modalBackdrop");

    if (btnAbrirModal) {
      btnAbrirModal.addEventListener("click", MD.abrirModal);
    }

    if (btnCerrarModal) {
      btnCerrarModal.addEventListener("click", MD.cerrarModalCrear);
    }

    if (backdrop) {
      backdrop.addEventListener("click", MD.cerrarModalCrear);
    }

    const btnConfElimTrim = document.getElementById("btnConfirmarEliminarTrimestral");
    if (btnConfElimTrim) {
      btnConfElimTrim.addEventListener("click", () => {
        MD.confirmarEliminar();
      });
    }

    document.querySelectorAll('[data-close="modalEliminar"]').forEach((btn) => {
      btn.addEventListener("click", () => MD.cerrarModalEliminar());
    });

    document.querySelectorAll('[data-close="modalEditarHorario"]').forEach((btn) => {
      btn.addEventListener("click", () => ED.cerrarModalEditarHorario());
    });

    const modalEditar = document.getElementById("modalEditarHorario");
    if (modalEditar) {
      modalEditar.addEventListener("change", (e) => {
        const t = e.target;
        if (t && t.id === "editCompetencia") {
          ED.renderRAEsPopup(t.value, []);
        }
      });
    }

    const btnGuardarEdit = document.getElementById("btnGuardarEditarHorario");
    if (btnGuardarEdit) {
      btnGuardarEdit.addEventListener("click", () => {
        ED.enviarEdicionHorarioDesdeModal();
      });
    }

    ED.ensureEditarHorarioNativeSelectsEnhanced();

    w.descargarPDF = MD.descargarPDF;
    w.mostrarModalEliminar = MD.mostrarModalEliminar;
  }

  document.addEventListener("DOMContentLoaded", () => {
    void initRegisterTables();
  });
})(window);
