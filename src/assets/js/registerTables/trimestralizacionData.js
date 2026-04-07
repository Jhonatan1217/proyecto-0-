/**
 * Carga de datos (trimestralización, fichas, competencias, instructores) y formulario modalidad.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const T = RT.Toast;
  const U = RT.util;

  const D = {};

  D.configurarModalidadFormulario = function () {
    const modalidadForm = document.getElementById("modalidad");
    const areaField = document.getElementById("id_area")?.closest(".field");
    const zonaField = document.getElementById("id_zona")?.closest(".field");

    if (!modalidadForm) return;

    modalidadForm.addEventListener("change", () => {
      const modalidad = modalidadForm.value;

      if (modalidad === "presencial") {
        if (areaField) areaField.style.display = "";
        if (zonaField) zonaField.style.display = "";
      } else if (modalidad === "virtual" || modalidad === "mixto") {
        if (areaField) areaField.style.display = "none";
        if (zonaField) zonaField.style.display = "none";
      }
    });
  };

  D.cargarTrimestralizacionPorGrupo = async function (grupo) {
    const tbody = document.getElementById("tbody-horarios");
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

    try {
      const res = await fetch(
        `${RT.API_BASE}src/controllers/TrimestralizacionController.php?accion=listarPorGrupo&numero_ficha=${grupo}`
      );
      const data = await res.json();
      const registrosServer = Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : [];

      RT.grid.renderizarTablaDesdeRegistros(registrosServer, "", { filtersApplied: true });
    } catch (e) {
      console.error(e);
      T.fire({ icon: "error", title: "Error al cargar datos por grupo" });
    }
  };

  D.cargarTrimestralizacion = async function () {
    const tbody = document.getElementById("tbody-horarios");
    const selectArea = document.getElementById("selectArea");
    const id_area = selectArea ? selectArea.value : "";

    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-gray-500">Cargando datos...</td></tr>`;

    if (!S.id_zona || !id_area) {
      RT.ui.toggleTabla(false);
      return;
    }

    try {
      const modalidad = String(document.getElementById("selectModalidad")?.value || "presencial")
        .trim()
        .toLowerCase();
      const modalidadParam = modalidad === "mixta" ? "MIXTO" : modalidad.toUpperCase();

      const res = await fetch(
        `${RT.API_BASE}src/controllers/TrimestralizacionController.php?accion=listar&id_zona=${S.id_zona}&id_area=${id_area}&modalidad=${encodeURIComponent(modalidadParam)}`
      );
      const data = await res.json();
      console.log("Datos recibidos del servidor:", data);
      const registrosServer = Array.isArray(data) ? data : Array.isArray(data.data) ? data.data : [];
      const conDatos = RT.grid.renderizarTablaDesdeRegistros(registrosServer, "", {
        filtersApplied: true,
      });

      if (conDatos) {
        T.fire({
          icon: "success",
          title: "Trimestralización cargada correctamente",
        });
      } else {
        T.fire({
          icon: "info",
          title: "Sin registros con los filtros actuales",
        });
      }
    } catch (error) {
      console.error("Error al cargar:", error);
      tbody.innerHTML = `<tr><td colspan="7" class="text-red-600 p-4">Error al conectar con el servidor.</td></tr>`;
      T.fire({
        icon: "error",
        title: "Error al cargar trimestralización",
      });
    }
  };

  D.extraerFichasDeHorarios = function () {
    if (S.horariosCache && S.horariosCache.length > 0) {
      const fichasSet = new Set();
      S.horariosCache.forEach((h) => {
        if (h.numero_ficha) {
          fichasSet.add(
            JSON.stringify({
              numero_ficha: h.numero_ficha,
              nivel_ficha: h.nivel_ficha || "Sin nivel",
            })
          );
        }
      });

      S.listaFichas = Array.from(fichasSet).map((f) => JSON.parse(f));
    }

    if (!S.listaFichas.length) {
      console.warn("No se encontraron fichas en los horarios");
      S.listaFichas = [];
    }
  };

  D.cargarFichas = async function () {
    try {
      const res = await fetch(`${RT.API_BASE}src/controllers/FichaController.php?accion=listar`);

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();

      const array = Array.isArray(data) ? data : Array.isArray(data.data) ? data.data : [];

      if (array.length > 0) {
        S.listaFichas = array.filter((f) => U.registroActivo(f.estado));
      }

      if (!S.listaFichas.length) {
        console.warn("No hay fichas activas en API, extrayendo de datos cargados...");
        D.extraerFichasDeHorarios();
      }
    } catch (error) {
      console.error("Error cargando fichas del API:", error);
      D.extraerFichasDeHorarios();
    }
  };

  D.cargarCompetencias = async function () {
    try {
      const res = await fetch(`${RT.API_BASE}src/controllers/CompetenciaController.php?accion=listar`);
      const data = await res.json();

      const array = Array.isArray(data) ? data : Array.isArray(data.data) ? data.data : [];

      S.listaCompetencias = array.filter((c) => U.registroActivo(c.estado));

      if (!S.listaCompetencias.length) {
        console.warn("No hay competencias activas");
      }
    } catch (error) {
      console.error("Error cargando competencias:", error);
      S.listaCompetencias = [];
    }
  };

  D.llenarSelectInstructores = function (instructores) {
    const selectInstructor = document.getElementById("selectInstructor");
    if (!selectInstructor) return;
    selectInstructor.innerHTML = '<option value="">Seleccione un instructor</option>';

    instructores.forEach((i) => {
      const option = document.createElement("option");
      option.value = i.id_instructor;
      option.textContent = `${i.nombre_instructor} - ${i.tipo_instructor}`;
      selectInstructor.appendChild(option);
    });
  };

  D.cargarInstructores = async function () {
    try {
      let instructoresArray = [];

      const resInstructor = await fetch(`${RT.API_BASE}src/controllers/InstructorController.php?accion=listar`);

      if (resInstructor.ok) {
        const data = await resInstructor.json();
        instructoresArray = Array.isArray(data) ? data : Array.isArray(data.data) ? data.data : [];
      } else {
        const resUsuarios = await fetch(`${RT.API_BASE}src/controllers/UsuarioController.php?accion=listar&cargo=INSTRUCTOR`);
        const dataUsuarios = await resUsuarios.json();
        const usuariosArray = Array.isArray(dataUsuarios)
          ? dataUsuarios
          : Array.isArray(dataUsuarios.data)
          ? dataUsuarios.data
          : [];

        instructoresArray = usuariosArray.map((u) => ({
          id_instructor: u.id_instructor ?? u.id_usuario,
          nombre_instructor: u.nombre_instructor ?? u.nombre_completo ?? "",
          tipo_instructor: u.tipo_instructor ?? u.tipo_contrato ?? "",
          estado: u.estado ?? 1,
        }));
      }

      S.listaInstructores = instructoresArray.filter((i) => U.registroActivo(i.estado));

      if (S.listaInstructores.length > 0) {
        D.llenarSelectInstructores(S.listaInstructores);
      } else {
        T.fire({
          icon: "warning",
          title: "No hay instructores activos",
        });
        S.listaInstructores = [];
      }
    } catch (error) {
      console.error("Error al cargar instructores:", error);
      T.fire({
        icon: "error",
        title: "No se pudo cargar instructores",
      });
      S.listaInstructores = [];
    }
  };

  D.obtenerRoesPorCompetencia = async function (id_competencia) {
    try {
      const res = await fetch(
        `${RT.API_BASE}src/controllers/RaeController.php?accion=porCompetencia&id_competencia=${id_competencia}`
      );
      const data = await res.json();

      if (Array.isArray(data)) return data;
      return [];
    } catch (e) {
      console.error("Error obteniendo RAEs:", e);
      return [];
    }
  };

  Object.assign(RT.data, D);
})(window);
