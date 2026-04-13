/**
 * Modal crear trimestralización, eliminar y exportación PDF.
 */
(function (w) {
  "use strict";
  const RT = w.RegisterTables;
  const S = RT.state;
  const T = RT.Toast;

  const M = {};

  M.confirmarEliminar = async function () {
    if (w.PUEDE_GESTION_HORAS_Y_LIMPIAR === false) {
      T.fire({ icon: "warning", title: "No tienes permiso para esta acción." });
      return;
    }
    const selectArea = document.getElementById("selectArea");
    const id_area = selectArea ? selectArea.value : "";

    if (!S.id_zona || !id_area) {
      T.fire({
        icon: "warning",
        title: "Debes seleccionar un área y una zona antes de limpiar la trimestralización",
      });
      return;
    }

    try {
      const res = await fetch(
        `${RT.API_BASE}src/controllers/TrimestralizacionController.php?accion=eliminar&id_zona=${S.id_zona}&id_area=${id_area}`
      );
      const data = await res.json();

      T.fire({
        icon: data.status === "success" || data.success ? "success" : "warning",
        title: data.message || "Trimestralización eliminada correctamente",
      });

      RT.data.cargarTrimestralizacion();
    } catch (err) {
      console.error("confirmarEliminar error:", err);
      T.fire({ icon: "error", title: "Error al eliminar" });
    } finally {
      M.cerrarModalEliminar();
    }
  };

  M.mostrarModalEliminar = function () {
    if (w.PUEDE_GESTION_HORAS_Y_LIMPIAR === false) return;
    const modal = document.getElementById("modalEliminar");
    if (!modal) return;
    modal.classList.remove("hidden");
    modal.classList.add("flex", "items-center", "justify-center");
    modal.style.display = "";
    modal.style.pointerEvents = "";
    modal.style.visibility = "";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "";
    });
    document.body.classList.add("overflow-hidden");
    document.body.style.overflow = "hidden";
  };

  M.cerrarModalEliminar = function () {
    const modal = document.getElementById("modalEliminar");
    if (!modal) return;
    const activeEl = document.activeElement;
    if (activeEl && modal.contains(activeEl)) activeEl.blur();
    modal.classList.add("hidden");
    modal.classList.remove("flex", "block", "items-center", "justify-center");
    modal.style.display = "none";
    modal.style.pointerEvents = "none";
    modal.style.visibility = "hidden";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "none";
    });
    document.body.style.overflow = "";
    document.body.classList.remove("overflow-hidden");
  };

  M.descargarPDF = async function () {
    const { jsPDF } = w.jspdf;
    const elementoOriginal = document.querySelector("#tabla-horarios");

    if (!elementoOriginal) {
      T.fire({
        icon: "error",
        title: "No se encontró la tabla para exportar",
      });
      return;
    }

    const elementoClonado = elementoOriginal.cloneNode(true);
    elementoClonado.style.maxHeight = "none";
    elementoClonado.style.overflow = "visible";
    elementoClonado.style.height = "auto";
    elementoClonado.style.width = "100%";
    elementoClonado.style.position = "absolute";
    elementoClonado.style.top = "0";
    elementoClonado.style.left = "-9999px";

    document.body.appendChild(elementoClonado);

    await new Promise((r) => setTimeout(r, 300));

    const canvas = await html2canvas(elementoClonado, {
      scale: 1.5,
      useCORS: true,
      backgroundColor: "#ffffff",
      scrollX: 0,
      scrollY: 0,
      windowWidth: elementoClonado.scrollWidth,
      windowHeight: elementoClonado.scrollHeight,
      logging: false,
    });

    document.body.removeChild(elementoClonado);

    const imgData = canvas.toDataURL("image/jpeg", 0.75);
    const pdf = new jsPDF({
      orientation: "landscape",
      unit: "mm",
      format: "a4",
      compress: true,
    });

    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = pdf.internal.pageSize.getHeight();

    const marginX = 10;
    const marginY = 15;

    const imgWidth = pdfWidth - marginX * 2;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    let position = marginY;
    let heightLeft = imgHeight;

    pdf.setFontSize(16);
    pdf.text(`Trimestralización - Zona ${S.id_zona}`, pdfWidth / 2, 10, { align: "center" });

    pdf.addImage(imgData, "jpeg", marginX, position, imgWidth, imgHeight);
    heightLeft -= pdfHeight - position;

    while (heightLeft > 0) {
      pdf.addPage();
      position = 0;
      pdf.addImage(imgData, "jpeg", marginX, position - heightLeft, imgWidth, imgHeight);
      heightLeft -= pdfHeight;
    }

    pdf.save(`trimestralizacion_zona_${S.id_zona}.pdf`);
  };

  M.abrirModal = function () {
    const modal = document.getElementById("modalCrearLanding");
    if (!modal) {
      console.error("❌ No existe #modalCrearLanding");
      return;
    }
    modal.classList.remove("hidden");
    modal.style.display = "";
    modal.style.pointerEvents = "";
    modal.style.visibility = "";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "";
    });
  };

  M.cerrarModalCrear = function () {
    const modal = document.getElementById("modalCrearLanding");
    if (!modal) return;
    const activeEl = document.activeElement;
    if (activeEl && modal.contains(activeEl)) activeEl.blur();
    modal.classList.add("hidden");
    modal.classList.remove("flex", "block", "items-center", "justify-center");
    modal.style.display = "none";
    modal.style.pointerEvents = "none";
    modal.style.visibility = "hidden";
    modal.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => {
      el.style.pointerEvents = "none";
    });
    document.body.style.overflow = "";
    document.body.classList.remove("overflow-hidden");
    const btnAbrir = document.getElementById("btnAbrirModal");
    if (btnAbrir?.focus) {
      try {
        btnAbrir.focus();
      } catch (e) {}
    }
  };

  RT.modals.abrirModal = M.abrirModal;
  RT.modals.cerrarModalCrear = M.cerrarModalCrear;
  RT.modals.confirmarEliminar = M.confirmarEliminar;
  RT.modals.mostrarModalEliminar = M.mostrarModalEliminar;
  RT.modals.cerrarModalEliminar = M.cerrarModalEliminar;
  RT.modals.descargarPDF = M.descargarPDF;
})(window);
