/**
 * Estado global y configuración compartida de la vista register_tables.
 * @namespace RegisterTables
 */
(function (w) {
  "use strict";

  const RT = (w.RegisterTables = w.RegisterTables || {});

  const urlParams = new URLSearchParams(w.location.search);
  RT.urlParams = urlParams;
  RT.filtrosIniciales = {
    modalidad: String(urlParams.get("modalidad") || "").trim().toLowerCase(),
    id_area: String(urlParams.get("id_area") || "").trim(),
    id_zona: String(urlParams.get("id_zona") || "").trim(),
    numero_ficha: String(urlParams.get("numero_ficha") || "").trim(),
  };

  RT.state = {
    id_zona: urlParams.get("id_zona"),
    horariosCache: [],
    gestionHorasCache: { instructores: [], grupos: [] },
    gestionHorasTabActual: "instructores",
    listaInstructores: [],
    listaCompetencias: [],
    listaFichas: [],
    editarHorarioContext: {
      idHorario: "",
      id_zona_val: "",
      id_area_val: "",
      snapshotInicial: null,
    },
    /** Tras init, al cambiar modalidad se limpian área/zona (evita borrar query params al cargar). */
    cascadaFiltrosPresencialActiva: false,
    /** Evita recargas al aplicar filtros desde vista previa local */
    syncFiltrosDesdePreview: false,
    /** Snapshot JSON de horariosCache para comparar cambios pendientes de envío a coordinación */
    horariosOriginal: null,
    /** Hay ediciones locales no enviadas como solicitud */
    huboCambios: false,
  };

  RT.API_BASE = ((w && w.BASE_URL) || "").replace(/\/+$/, "/");
  RT.IS_AUTHENTICATED = Boolean(w && w.IS_AUTHENTICATED);

  RT.Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: "#fff",
    color: "#000",
  });

  RT.EMPTY_STATE_DEFAULT_TITLE = "Seleccione un horario";
  RT.EMPTY_STATE_DEFAULT_DESC =
    "Elige la modalidad y completa los filtros correspondientes para ver el horario.";
  RT.EMPTY_STATE_FILTERED_TITLE = "Sin trimestralización";
  /** Tailwind: color del título cuando emptyMode es filtered-empty */
  RT.EMPTY_STATE_FILTERED_TITLE_COLOR_CLASS = "text-red-600";
  RT.EMPTY_STATE_FILTERED_DESC =
    "No hay trimestralización registrada con los filtros seleccionados.";

  /** Rompe dependencias circulares hasta que carguen grid / data / edit */
  RT.grid = RT.grid || {};
  RT.data = RT.data || {};
  RT.edit = RT.edit || {};
  RT.ui = RT.ui || {};
  RT.ui.sincronizarFiltrosCabeceraDesdePreview =
    RT.ui.sincronizarFiltrosCabeceraDesdePreview || async function () {};
  RT.modals = RT.modals || {};
  RT.solicitud = RT.solicitud || {};
  RT.modals.abrirModal = RT.modals.abrirModal || function () {
    console.warn("RegisterTables.modals.abrirModal no inicializado aún");
  };

  const noopAsync = async function () {};
  RT.data.cargarTrimestralizacion = RT.data.cargarTrimestralizacion || noopAsync;
  RT.data.cargarTrimestralizacionPorGrupo = RT.data.cargarTrimestralizacionPorGrupo || noopAsync;
  RT.data.cargarFichas = RT.data.cargarFichas || noopAsync;
  RT.data.cargarCompetencias = RT.data.cargarCompetencias || noopAsync;
  RT.data.cargarInstructores = RT.data.cargarInstructores || noopAsync;
  RT.data.extraerFichasDeHorarios = RT.data.extraerFichasDeHorarios || function () {};

  RT.edit.editarTrimestralizacion = RT.edit.editarTrimestralizacion || function () {
    console.warn("RegisterTables.edit.editarTrimestralizacion no inicializado aún");
  };
  RT.grid.renderizarTablaDesdeRegistros =
    RT.grid.renderizarTablaDesdeRegistros ||
    function () {
      console.warn("RegisterTables.grid.renderizarTablaDesdeRegistros no inicializado aún");
      return false;
    };
})(window);
