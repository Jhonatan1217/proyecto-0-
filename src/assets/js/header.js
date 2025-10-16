document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-hamburguesa");
  const menuLateral = document.getElementById("menu-lateral");
  const cerrarMenu = document.getElementById("cerrar-menu");

  menuBtn.addEventListener("click", () => {
    menuLateral.classList.remove("translate-x-full");
  });

  cerrarMenu.addEventListener("click", () => {
    menuLateral.classList.add("translate-x-full");
  });
});
