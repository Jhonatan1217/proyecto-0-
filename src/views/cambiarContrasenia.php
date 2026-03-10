<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Obtener token de la URL (prioridad) o de sesión
$token = $_GET['token'] ?? $_SESSION['reset_token'] ?? '';

// Verificar que tenga token
if (empty($token)) {
    header("Location: restablecerContrasenia.php?error=token");
    exit;
}

// Guardar token en sesión si viene por URL
if (isset($_GET['token']) && !isset($_SESSION['reset_token'])) {
    $_SESSION['reset_token'] = $token;
}

$error = $_SESSION['error_password'] ?? '';
unset($_SESSION['error_password']);

include __DIR__ . '/../includes/header-public.php';
?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="flex-1 min-h-screen flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat" 
style="background-image: url(../assets/img/fondo_restablecer_contrasenia.png);">
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de cambio de contraseña -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8">
            <h1 class="text-3xl text-center font-bold text-[#39A900] mb-6">Restablecer Contraseña</h1>

            <!-- Mensajes de error -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="../controllers/RecuperarContrasenia.php?accion=cambiar" class="space-y-6">
                <!-- Token oculto -->
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <!-- Nueva contraseña -->
                <div>
                    <label for="password" class="block text-sm font-bold text-black mb-2">
                        Contraseña nueva
                    </label>
                    <div class="relative">
                        <img
                            src="../assets/img/lock.svg"
                            alt="Contraseña"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none"
                        >
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="••••••••"
                            minlength="8"
                            required
                        >
                    </div>
                </div>

                <!-- Confirmar contraseña -->
                <div>
                    <label for="confirmar_password" class="block text-sm font-bold text-black mb-2">
                        Confirmar contraseña
                    </label>
                    <div class="relative">
                        <img
                            src="../assets/img/lock.svg"
                            alt="Confirmar contraseña"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none"
                        >
                        <input
                            type="password"
                            id="confirmar_password"
                            name="confirmar_password"
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-400 focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="••••••••"
                            minlength="8"
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
                        Aceptar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para validar que las contraseñas coincidan en tiempo real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmar = document.getElementById('confirmar_password');
            
            function validateMatch() {
                if (confirmar.value.length > 0) {
                    if (password.value !== confirmar.value) {
                        confirmar.classList.add('border-red-500', 'focus:ring-red-500');
                        confirmar.classList.remove('border-gray-400', 'focus:ring-[#39A900]');
                    } else {
                        confirmar.classList.remove('border-red-500', 'focus:ring-red-500');
                        confirmar.classList.add('border-gray-400', 'focus:ring-[#39A900]');
                    }
                }
            }
            
            password.addEventListener('keyup', validateMatch);
            confirmar.addEventListener('keyup', validateMatch);
        });
    </script>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>