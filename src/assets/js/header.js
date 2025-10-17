document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-hamburguesa");
  const menuLateral = document.getElementById("menu-lateral");
  const cerrarMenu = document.getElementById("cerrar-menu");

  // Aseguramos que no bloquee clics cuando está oculto
  menuLateral.classList.add("pointer-events-none");

  // Abrir el sidebar 
  menuBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    menuLateral.classList.remove("translate-x-full", "pointer-events-none");
  });

  // Cierre con el botón "x"
  cerrarMenu.addEventListener("click", () => {
    menuLateral.classList.add("translate-x-full", "pointer-events-none");
  });

  // Cierra al hacer clic fuera del sidebar
  document.addEventListener("click", (e) => {
    if (
      !menuLateral.classList.contains("translate-x-full") && 
      !menuLateral.contains(e.target) && 
      !menuBtn.contains(e.target)) {
      menuLateral.classList.add("translate-x-full", "pointer-events-none");
    }
  });
});
