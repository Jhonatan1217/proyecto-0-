<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Necesario para manejar mensajes de sesión desde el controlador

/* ================= BASE_URL AUTO ================= */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https://'
        : 'http://';

    $host = $_SERVER['HTTP_HOST'];

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $project = preg_replace('#/src/.*$#', '', $scriptDir);

    define('BASE_URL', $protocol . $host . $project . '/');
}

// Obtener mensajes de sesión
$error = $_SESSION['error_recuperacion'] ?? '';
$success = $_SESSION['success_recuperacion'] ?? '';

// Limpiar mensajes de sesión después de obtenerlos
unset($_SESSION['error_recuperacion'], $_SESSION['success_recuperacion']);

// Determinar si se debe mostrar el modal de éxito
$showModal = !empty($success);
$modalMessage = $success;

// Incluir header
include '../includes/header-public.php';
?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="flex-1 flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de restablecimiento -->
        <div class="relative bg-white/95 backdrop-blur-sm rounded-2xl shadow-[0_0_32px_rgba(0,0,0,0.26)] p-8">
            <button onclick="window.location.href='<?= BASE_URL ?>index.php?page=login';" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700" aria-label="Cerrar">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-8">
                <div class="w-12 h-12 bg-[#BFBFBF] rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="../assets/img/mail2.svg" alt="Correo" class="w-8 h-8">
                </div>
                <h1 class="text-3xl text-center font-bold text-black mb-6">Restablecer Contraseña</h1>
                <p class="text-gray-600 mb-8 text-center">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>
            </div>

            <!-- Mostrar mensajes de error (solo errores) -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-gray-100 border border-[#F90000] text-[#CE1313] rounded-lg flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="../controllers/RecuperarContrasenia.php?accion=solicitar" class="space-y-6">
                <!-- Campo de correo electrónico -->
                <div>
                    <label for="correo" class="block text-sm font-bold text-black mb-2">
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <img
                            src="../assets/img/mail.svg"
                            alt="Correo"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none"
                        >
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-400 focus:outline-none focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="correo_ejemplo@gmail.com"
                            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        class="inline-flex items-center justify-center gap-2 w-full py-3 text-sm bg-[#9FA0A2] text-white font-medium rounded-2xl hover:bg-[#000000] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-white focus:ring-opacity-50"
                    >
                        <img src="../assets/img/send.svg" alt="Enviar" class="w-4 h-4">
                        Enviar enlace de restablecimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de éxito (solo se muestra si hay mensaje de éxito) -->
<?php if ($showModal): ?>
<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 text-center">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700" aria-label="Cerrar modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
        </button>
        <div class="flex flex-col items-center justify-center gap-5 py-2">
            <div class="w-12 h-12 bg-[#C5E7B5] rounded-full flex items-center justify-center mx-auto">
                <img src="../assets/img/circle-check.svg" alt="Correo" class="w-8 h-8">
            </div>
            <h2 class="text-xl font-bold text-black">Enlace enviado</h2>
            <p class="text-gray-600"><?= htmlspecialchars($modalMessage) ?></p>
            <div class="inline-flex items-center gap-1 whitespace-nowrap text-gray-600">
                <span>No recibiste el correo?</span>
                <button type="button" onclick="closeModal()" class="bg-transparent border-0 p-0 m-0 text-black font-medium">Reintentar</button>
            </div>
        </div>
    </div>
</div>
<script>
function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}
</script>
<?php endif; ?>

<?php
// Incluir footer
include '../includes/footer.php';
?>