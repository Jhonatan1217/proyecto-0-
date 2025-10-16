document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-hamburguesa");
  const menuLateral = document.getElementById("menu-lateral");
  const cerrarMenu = document.getElementById("cerrar-menu");

  // Aseguramos que no bloquee clics cuando está oculto
  menuLateral.classList.add("pointer-events-none");

  menuBtn.addEventListener("click", () => {
    menuLateral.classList.remove("translate-x-full", "pointer-events-none");
  });

  cerrarMenu.addEventListener("click", () => {
    menuLateral.classList.add("translate-x-full", "pointer-events-none");
  });
});
