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

// Incluir header
include '../includes/header-public.php';
?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="flex-1 min-h-screen flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat" 
style="background-image: url(../assets/img/fondo_restablecer_contrasenia.png);">
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de restablecimiento -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8">
            <div class="mb-8">
                <h1 class="text-3xl text-center font-bold text-[#39A900] mb-6">Restablecer Contraseña</h1>
                <p class="text-gray-600 mb-8">
                    No te preocupes, a todos nos pasa. Ingresa el correo electrónico asociado a tu cuenta 
                    y te enviaremos un enlace para que puedas crear una nueva.
                </p>
            </div>

            <!-- Mostrar mensajes de éxito o error -->
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-gray-100 border border-[#39A900] text-[#007831] rounded-lg flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

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
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="correo_ejemplo@gmail.com"
                            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-6 pt-4 justify-center">
                    <button 
                        type="button" 
                        onclick="window.location.href='<?= BASE_URL ?>index.php?page=login';"
                        class="px-6 py-2.5 text-sm border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-sm bg-[#007831] text-white font-medium rounded-lg hover:bg-[#39A900] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-[#39A900] focus:ring-opacity-50"
                    >
                        Enviar Enlace
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../includes/footer.php';
?>