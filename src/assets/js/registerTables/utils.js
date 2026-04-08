/**
 * Utilidades puras y helpers de presentación sin efectos secundarios.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const U = {};

  U.registroActivo = function (estado) {
    if (estado === undefined || estado === null || estado === "") return true;
    const valor = String(estado).trim().toLowerCase();
    return valor === "1" || valor === "true" || valor === "activo";
  };

  U.timeToMinutes = function (t) {
    if (!t) return null;
    const [h, m] = t.split(":").map(Number);
    return Number.isFinite(h) ? h * 60 + (Number.isFinite(m) ? m : 0) : null;
  };

  U.formatHourNumber = function (value) {
    const n = Number(value || 0);
    if (!Number.isFinite(n)) return "0";
    return Number.isInteger(n) ? String(n) : n.toFixed(1).replace(/\.0$/, "");
  };

  U.normalizarHoraParaSelectEditar = function (hora) {
    if (hora == null || hora === "") return "";
    const parts = String(hora).trim().split(":");
    const h = parseInt(parts[0], 10);
    const m = parseInt(parts[1] ?? "0", 10);
    if (!Number.isFinite(h)) return "";
    return `${String(h).padStart(2, "0")}:${String(Number.isFinite(m) ? m : 0).padStart(2, "0")}`;
  };

  U.etiquetaNivelGrupo = function (f) {
    const raw = f?.nivel ?? f?.nivel_ficha ?? f?.nivel_formacion ?? "";
    const s = String(raw).trim();
    return s || "Sin nivel";
  };

  U.escapeHtml = function (value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  };

  U.getEstadoExcedente = function (value) {
    const n = Number(value || 0);
    if (n < 0) return "danger";
    if (n <= 4) return "neutral";
    return "ok";
  };

  U.renderExcedentePill = function (value) {
    const estado = U.getEstadoExcedente(value);
    const texto = Number(value) < 0 ? `${U.formatHourNumber(value)} h` : `${U.formatHourNumber(value)} h libres`;
    return `<span class="gestion-horas-pill gestion-horas-pill--${estado}">${texto}</span>`;
  };

  U.normalizarTextoFiltro = function (value) {
    return String(value ?? "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim()
      .toLowerCase();
  };

  RT.util = U;
})(window);
