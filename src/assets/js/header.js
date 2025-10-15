document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-hamburguesa");
  const menu = document.getElementById("menu");

  menuBtn.addEventListener("click", () => {
    menu.classList.toggle("hidden");
  });

  // Cierra el menú al hacer clic fuera
  document.addEventListener("click", (e) => {
    if (!menu.contains(e.target) && !menuBtn.contains(e.target)) {
      menu.classList.add("hidden");
    }
  });
});
