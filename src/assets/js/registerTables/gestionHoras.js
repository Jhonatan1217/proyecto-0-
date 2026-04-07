/**
 * Modal de resumen de gestión de horas (instructores / grupos).
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const U = RT.util;
  const T = RT.Toast;
  const GH = {};

  GH.getGestionHorasAccionConfig = function () {
    if (S.gestionHorasTabActual === "grupos") {
      return {
        text: "Gestionar grupos",
        title: "Ir a gestión de grupos",
        url: `${RT.API_BASE}index.php?page=src/views/gestionGrupos`,
      };
    }
    return {
      text: "Gestionar usuarios",
      title: "Ir a gestión de usuarios",
      url: `${RT.API_BASE}index.php?page=src/views/gestionUsuarios`,
    };
  };

  GH.syncGestionHorasAccionBtn = function () {
    const btn = document.getElementById("btnIrGestionInstructores");
    if (!btn) return;
    const cfg = GH.getGestionHorasAccionConfig();
    btn.textContent = cfg.text;
    btn.setAttribute("title", cfg.title);
    btn.dataset.href = cfg.url;
  };

  GH.dispararToastsExcedente = function (warnings) {
    const instructores = Array.isArray(warnings?.instructores) ? warnings.instructores : [];
    const grupos = Array.isArray(warnings?.grupos) ? warnings.grupos : [];

    instructores.forEach((inst) => {
      T.fire({
        icon: "warning",
        title: `Atención: El instructor ha superado su límite de carga horaria (${inst.nombre_instructor})`,
      });
    });

    grupos.forEach((grupo) => {
      T.fire({
        icon: "info",
        title: `Aviso: El grupo ${grupo.id_grupo} ha excedido las 30 horas reglamentarias`,
      });
    });
  };

  GH.cargarResumenGestionHoras = async function () {
    const res = await fetch(`${RT.API_BASE}src/controllers/TrimestralizacionController.php?accion=resumenHoras`);
    const data = await res.json();
    if (!res.ok || data.status !== "success") {
      throw new Error(data.mensaje || data.error || "No fue posible cargar el resumen de horas");
    }
    S.gestionHorasCache = {
      instructores: Array.isArray(data?.data?.instructores) ? data.data.instructores : [],
      grupos: Array.isArray(data?.data?.grupos) ? data.data.grupos : [],
    };
  };

  GH.getGestionHorasFiltrados = function () {
    const search = U.normalizarTextoFiltro(document.getElementById("gestionHorasSearch")?.value || "");
    const extra = U.normalizarTextoFiltro(document.getElementById("gestionHorasExtraFiltro")?.value || "");

    if (S.gestionHorasTabActual === "instructores") {
      return S.gestionHorasCache.instructores.filter((item) => {
        const bySearch =
          !search ||
          U.normalizarTextoFiltro(item.nombre_instructor || "").includes(search) ||
          U.normalizarTextoFiltro(item.id_instructor || "").includes(search);
        const byExtra = !extra || U.normalizarTextoFiltro(item.tipo_contrato || "") === extra;
        return bySearch && byExtra;
      });
    }

    return S.gestionHorasCache.grupos.filter((item) => {
      const bySearch =
        !search ||
        U.normalizarTextoFiltro(item.id_grupo || "").includes(search) ||
        U.normalizarTextoFiltro(item.id_ficha || "").includes(search);
      const byExtra = !extra || U.normalizarTextoFiltro(item.nivel_grupo || "") === extra;
      return bySearch && byExtra;
    });
  };

  GH.renderGestionHorasResumen = function (rows) {
    const resumen = document.getElementById("gestionHorasResumen");
    if (!resumen) return;
    const excedidos = rows.filter(
      (item) => Number(item?.horas_actuales ?? 0) > Number(item?.horas_maximas ?? 0)
    );
    const totalExcedidos = excedidos.length;
    const alertHtml = totalExcedidos
      ? `<div class="gh-alert gh-alert--danger">Se detectaron ${totalExcedidos} ${S.gestionHorasTabActual === "instructores" ? "instructor(es)" : "grupo(s)"} por encima del límite de horas.</div>`
      : `<div class="gh-alert gh-alert--ok">Sin excedentes de horas en ${S.gestionHorasTabActual === "instructores" ? "instructores" : "grupos"}.</div>`;

    if (S.gestionHorasTabActual === "instructores") {
      resumen.innerHTML = `
      <p class="gh-resumen-title">Instructores</p>
      <p class="gh-resumen-sub">(Instructores Planta 32h, Instructores Contratista 40h)</p>
      ${alertHtml}`;
    } else {
      resumen.innerHTML = `
      <p class="gh-resumen-title">Grupos</p>
      <p class="gh-resumen-sub">(Cada grupo tiene un máximo de 30 horas semanales)</p>
      ${alertHtml}`;
    }
  };

  GH.renderGestionHoras = function () {
    const filtros = document.getElementById("gestionHorasFiltros");
    const head = document.getElementById("gestionHorasHead");
    const body = document.getElementById("gestionHorasBody");
    const tabInst = document.getElementById("tabGestionHorasInstructores");
    const tabGrupos = document.getElementById("tabGestionHorasGrupos");
    if (!filtros || !head || !body) return;

    if (tabInst) tabInst.classList.toggle("is-active", S.gestionHorasTabActual === "instructores");
    if (tabGrupos) tabGrupos.classList.toggle("is-active", S.gestionHorasTabActual === "grupos");
    GH.syncGestionHorasAccionBtn();

    if (S.gestionHorasTabActual === "instructores") {
      filtros.innerHTML = `
      <input id="gestionHorasSearch" type="text" placeholder="Buscar instructores" class="gh-filtros-input" />
      <select id="gestionHorasExtraFiltro" class="gh-filtros-select select-styled">
        <option value="">Todos los tipos de contrato</option>
        <option value="planta">Planta</option>
        <option value="contratista">Contratista</option>
      </select>`;

      head.innerHTML = `
      <tr>
        <th>Instructor</th>
        <th>Tipo contrato</th>
        <th class="center">Horas actuales</th>
        <th class="center">Horas máxima</th>
        <th class="center">Excedente</th>
        <th class="center">Acciones</th>
      </tr>`;
    } else {
      filtros.innerHTML = `
      <input id="gestionHorasSearch" type="text" placeholder="Buscar grupos" class="gh-filtros-input" />
      <select id="gestionHorasExtraFiltro" class="gh-filtros-select select-styled">
        <option value="">Todos los niveles</option>
        <option value="técnico">Técnico</option>
        <option value="tecnólogo">Tecnólogo</option>
        <option value="sin nivel">Sin nivel</option>
      </select>`;

      head.innerHTML = `
      <tr>
        <th>ID Grupo</th>
        <th>Nivel de grupo</th>
        <th class="center">Horas actuales</th>
        <th class="center">Horas máxima</th>
        <th class="center">Excedente</th>
      </tr>`;
    }

    document.getElementById("gestionHorasSearch")?.addEventListener("input", GH.renderGestionHorasTabla);
    document.getElementById("gestionHorasExtraFiltro")?.addEventListener("change", GH.renderGestionHorasTabla);
    if (typeof ComboboxComponent !== "undefined" && typeof ComboboxComponent.enhanceSelectStyled === "function") {
      ComboboxComponent.enhanceSelectStyled({
        selector: "#modalGestionHoras select.select-styled",
        forceDropup: true,
        placeholderValues: [""],
        maxDropdownItems: 6,
        allowClear: true,
      });
    }
    GH.renderGestionHorasTabla();
  };

  GH.renderGestionHorasTabla = function () {
    const body = document.getElementById("gestionHorasBody");
    if (!body) return;

    const rows = GH.getGestionHorasFiltrados();
    const colspan = S.gestionHorasTabActual === "instructores" ? 6 : 5;
    GH.renderGestionHorasResumen(rows);

    if (!rows.length) {
      body.innerHTML = `<tr><td colspan="${colspan}" style="text-align:center;padding:24px;color:#6b7280;">Sin datos disponibles</td></tr>`;
      return;
    }

    if (S.gestionHorasTabActual === "instructores") {
      body.innerHTML = rows
        .map((item) => {
          const exc = Number(item.excedente ?? 0);
          const excHTML = exc < 0 ? `<span class="gh-excedente-neg">${U.formatHourNumber(exc)}</span>` : `--`;
          return `
        <tr>
          <td>${U.escapeHtml(item.nombre_instructor || "Sin nombre")}</td>
          <td>${U.escapeHtml(item.tipo_contrato || "Contratista")}</td>
          <td class="center">${U.formatHourNumber(item.horas_actuales)}</td>
          <td class="center">${U.formatHourNumber(item.horas_maximas)}</td>
          <td class="center">${excHTML}</td>
          <td class="center">
            <button type="button" class="gh-action-btn" onclick="window.location.href='${RT.API_BASE}index.php?page=src/views/gestionUsuarios'" title="Gestionar usuarios">
              <svg viewBox="0 0 24 24" fill="none" width="14" height="14"><path d="M4 20H8L18.5 9.5C19.33 8.67 19.33 7.33 18.5 6.5C17.67 5.67 16.33 5.67 15.5 6.5L5 17V20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.5 8.5L16.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
          </td>
        </tr>`;
        })
        .join("");
      return;
    }

    body.innerHTML = rows
      .map((item) => {
        const exc = Number(item.excedente ?? 0);
        const excHTML = exc < 0 ? `<span class="gh-excedente-neg">${U.formatHourNumber(exc)}</span>` : `--`;
        return `
      <tr>
        <td>${U.escapeHtml(item.id_grupo || "—")}</td>
        <td>${U.escapeHtml(item.nivel_grupo || "Sin nivel")}</td>
        <td class="center">${U.formatHourNumber(item.horas_actuales)}</td>
        <td class="center">${U.formatHourNumber(item.horas_maximas)}</td>
        <td class="center">${excHTML}</td>
      </tr>`;
      })
      .join("");
  };

  GH.abrirModalGestionHoras = async function () {
    const modal = document.getElementById("modalGestionHoras");
    if (!modal) return;
    try {
      await GH.cargarResumenGestionHoras();
      S.gestionHorasTabActual = "instructores";
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      document.body.style.overflow = "hidden";
      GH.renderGestionHoras();
    } catch (e) {
      T.fire({ icon: "error", title: e.message || "No se pudo abrir la gestión de horas" });
    }
  };

  GH.cerrarModalGestionHoras = function () {
    const modal = document.getElementById("modalGestionHoras");
    if (!modal) return;
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.style.overflow = "";
  };

  RT.gestionHoras = GH;
})(window);
