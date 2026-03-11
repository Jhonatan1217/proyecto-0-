function togglePassword() {
    const input = document.getElementById("password");
    const btn = document.getElementById("togglePasswordBtn");
    const iconOpen = document.getElementById("iconEyeOpen");
    const iconClosed = document.getElementById("iconEyeClosed");
    if (!input || !btn || !iconOpen || !iconClosed) return;

    if (input.type === "password") {
        input.type = "text";
        iconOpen.classList.add("hidden");
        iconClosed.classList.remove("hidden");
        btn.setAttribute("aria-label", "Ocultar contraseña");
    } else {
        input.type = "password";
        iconOpen.classList.remove("hidden");
        iconClosed.classList.add("hidden");
        btn.setAttribute("aria-label", "Mostrar contraseña");
    }
}