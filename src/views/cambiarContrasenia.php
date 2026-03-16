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

<div class="flex-1 flex items-center justify-center p-4"> 
    
    <div class="w-full max-w-md">
        <!-- Tarjeta de cambio de contraseña -->
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8">
            <button onclick="window.location.href='<?= BASE_URL ?>index.php?page=login';" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700" aria-label="Cerrar modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="mb-8">
                <div class="w-12 h-12 bg-[#C5E7B5] rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="../assets/img/lockGreen.svg" alt="Candado" class="w-8 h-8">
                </div>
                <h1 class="text-3xl text-center font-bold text-[#000000] mb-6">Nueva Contraseña</h1>
                <p class="text-gray-600 mb-8 text-center">
                    Ingresa tu nueva contraseña
                </p>
            </div>
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
                            class="w-full pl-12 pr-12 py-3 rounded-2xl border border-gray-400 focus:outline-none focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="Ingrese contraseña"
                            minlength="8"
                            required
                        >
                        <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Ver contraseña">
                            <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
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
                            class="w-full pl-12 pr-12 py-3 rounded-2xl border border-gray-400 focus:outline-none focus:ring-2 focus:ring-[#39A900] focus:border-[#39A900] transition-colors"
                            placeholder="Confirmar contraseña"
                            minlength="8"
                            required
                        >
                        <button type="button" onclick="togglePassword('confirmar_password', 'eyeIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Ver contraseña">
                            <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-6 pt-4 justify-center">
                    <button 
                        type="submit" 
                        class="inline-flex items-center justify-center gap-2 w-full py-3 text-sm bg-[#9FA0A2] text-white font-medium rounded-2xl hover:bg-[#000000] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-white focus:ring-opacity-50"
                    >
                        <img src="../assets/img/lockWhite.svg" alt="Enviar" class="w-4 h-4">
                        Restablecer contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para validar que las contraseñas coincidan en tiempo real -->
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            }
        }

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