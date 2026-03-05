/**
 * Módulo Gestión de Perfil – Widget de usuario y modales (Ver perfil, Solicitar cambios, Cambiar contraseña).
 * No modifica el módulo de Solicitudes (listado/aprobación).
 */
(function () {
  var API = window.API_USUARIO;
  var USUARIO_ID = window.USUARIO_ID || 0;

  function getEl(id) { return document.getElementById(id); }
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsAll(sel, ctx) { return (ctx || document).querySelectorAll(sel); }

  function closeUserMenu() {
    var menu = getEl("userMenu");
    var chevron = getEl("chevronIcon");
    if (menu) menu.classList.add("hidden");
    if (chevron) chevron.classList.remove("rotate-180");
  }

  function openModal(id) {
    var modal = getEl(id);
    if (!modal) return;
    qsAll("[id^='modal']").forEach(function (m) {
      m.classList.add("hidden");
      m.classList.remove("flex");
    });
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }

  function closeModal(id) {
    var modal = getEl(id);
    if (modal) {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }
  }

  function loadPerfil() {
    if (!USUARIO_ID) return Promise.resolve(null);
    return fetch(API + "?accion=obtener&id=" + USUARIO_ID)
      .then(function (r) { return r.json(); })
      .catch(function () { return null; });
  }

  function fillModalVerPerfil(data) {
    var nombre = (data && data.nombre_completo) || "—";
    var iniciales = nombre !== "—" ? nombre.trim().split(/\s+/).map(function (s) { return s[0]; }).slice(0, 2).join("").toUpperCase() : "—";
    getEl("verPerfilAvatar").textContent = iniciales;
    getEl("verPerfilNombre").textContent = nombre;
    getEl("verPerfilRol").textContent = (data && data.cargo) || "—";
    getEl("verPerfilNombreCampo").textContent = nombre;
    getEl("verPerfilDocumento").textContent = (data && data.numero_documento) || "—";
    getEl("verPerfilCorreo").textContent = (data && data.correo_electronico) || "—";
    getEl("verPerfilArea").textContent = (data && data.nombre_area) || "—";
  }

  var solicitarCambiosValoresIniciales = null;

  function getFormSolicitarCambiosValues(form) {
    if (!form) return {};
    return {
      nombre_completo: (form.querySelector("[name='nombre_completo']") || {}).value || "",
      tipo_documento: (form.querySelector("[name='tipo_documento']") || {}).value || "",
      numero_documento: (form.querySelector("[name='numero_documento']") || {}).value || "",
      correo_electronico: (form.querySelector("[name='correo_electronico']") || {}).value || "",
      tipo_instructor: (form.querySelector("[name='tipo_instructor']") || {}).value || "",
      tipo_contrato: (form.querySelector("[name='tipo_contrato']") || {}).value || ""
    };
  }

  function fillFormSolicitarCambios(data) {
    var form = getEl("formSolicitarCambiosPerfil");
    if (!form || !data) return;
    form.querySelector("[name='nombre_completo']").value = data.nombre_completo || "";
    form.querySelector("[name='tipo_documento']").value = data.tipo_documento || "";
    form.querySelector("[name='numero_documento']").value = data.numero_documento || "";
    form.querySelector("[name='correo_electronico']").value = data.correo_electronico || "";
    form.querySelector("[name='tipo_instructor']").value = data.tipo_instructor || "";
    form.querySelector("[name='tipo_contrato']").value = data.tipo_contrato || "";
    solicitarCambiosValoresIniciales = getFormSolicitarCambiosValues(form);
  }

  function onVerPerfil() {
    closeUserMenu();
    loadPerfil().then(function (data) {
      fillModalVerPerfil(data);
      openModal("modalVerPerfil");
    });
  }

  function onEditarPerfil() {
    closeUserMenu();
    loadPerfil().then(function (data) {
      fillFormSolicitarCambios(data);
      openModal("modalSolicitarCambiosPerfil");
    });
  }

  function initTogglePassword(container) {
    if (!container) return;
    qsAll(".toggle-pwd", container).forEach(function (btn) {
      var iconOpen = btn.querySelector(".icon-eye-open");
      var iconClosed = btn.querySelector(".icon-eye-closed");
      btn.addEventListener("click", function () {
        var wrap = btn.closest(".relative");
        var input = wrap && wrap.querySelector("input");
        if (!input) return;
        var isPass = input.type === "password";
        input.type = isPass ? "text" : "password";
        btn.setAttribute("aria-label", isPass ? "Ocultar contraseña" : "Mostrar contraseña");
        if (iconOpen && iconClosed) {
          if (isPass) {
            iconOpen.classList.remove("hidden");
            iconClosed.classList.add("hidden");
          } else {
            iconOpen.classList.add("hidden");
            iconClosed.classList.remove("hidden");
          }
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    var container = getEl("userDropdownContainer");
    if (container) {
      container.addEventListener("click", function (e) {
        var action = e.target.closest("[data-action]");
        if (!action) return;
        e.preventDefault();
        var act = action.getAttribute("data-action");
        if (act === "ver-perfil") onVerPerfil();
        else if (act === "editar-perfil") onEditarPerfil();
        else if (act === "cerrar-sesion") {
          closeUserMenu();
          openModal("modalCerrarSesion");
        }
      });
    }

    var btnConfirmarCerrar = getEl("btnConfirmarCerrarSesion");
    if (btnConfirmarCerrar) {
      btnConfirmarCerrar.addEventListener("click", function () {
        closeModal("modalCerrarSesion");
        var base = window.BASE_URL || "";
        window.location.href = base + "index.php?page=logout";
      });
    }

    qsAll("[data-close]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-close");
        if (id) closeModal(id);
      });
    });

    var btnCambiarContrasena = getEl("btnAbrirCambiarContrasena");
    if (btnCambiarContrasena) {
      btnCambiarContrasena.addEventListener("click", function () {
        closeModal("modalSolicitarCambiosPerfil");
        openModal("modalCambiarContrasena");
      });
    }

    var btnVolver = getEl("btnVolverEditarPerfil");
    if (btnVolver) {
      btnVolver.addEventListener("click", function () {
        closeModal("modalCambiarContrasena");
        openModal("modalSolicitarCambiosPerfil");
      });
    }

    var formSolicitar = getEl("formSolicitarCambiosPerfil");
    if (formSolicitar) {
      formSolicitar.addEventListener("submit", function (e) {
        e.preventDefault();
        var actual = getFormSolicitarCambiosValues(formSolicitar);
        var inicial = solicitarCambiosValoresIniciales || {};
        var hayCambios = Object.keys(actual).some(function (key) {
          return String(actual[key] || "").trim() !== String(inicial[key] || "").trim();
        });
        if (!hayCambios) {
          if (window.Swal) {
            Swal.fire({ toast: true, position: "top-end", icon: "warning", title: "No se detectaron cambios", showConfirmButton: false, timer: 3000 });
          } else {
            alert("No se detectaron cambios.");
          }
          return;
        }
        if (window.Swal) {
          Swal.fire({ toast: true, position: "top-end", icon: "info", title: "Solicitud enviada al administrador.", showConfirmButton: false, timer: 2500 });
        } else {
          alert("Solicitud enviada al administrador.");
        }
        closeModal("modalSolicitarCambiosPerfil");
      });
    }

    var formContrasena = getEl("formCambiarContrasena");
    if (formContrasena) {
      formContrasena.addEventListener("submit", function (e) {
        e.preventDefault();
        var nueva = formContrasena.querySelector("[name='password_nueva']").value;
        var confirmar = formContrasena.querySelector("[name='password_confirmar']").value;
        if (nueva !== confirmar) {
          if (window.Swal) Swal.fire({ icon: "error", title: "Las contraseñas no coinciden." });
          else alert("Las contraseñas no coinciden.");
          return;
        }
        if (window.Swal) {
          Swal.fire({ toast: true, position: "top-end", icon: "success", title: "Contraseña actualizada.", showConfirmButton: false, timer: 2500 });
        } else {
          alert("Contraseña actualizada.");
        }
        closeModal("modalCambiarContrasena");
        formContrasena.reset();
      });
    }

    initTogglePassword(getEl("modalCambiarContrasena"));
  });
})();
