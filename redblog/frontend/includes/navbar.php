<?php
// frontend/includes/navbar.php
// RUTA CORREGIDA - usar __DIR__ para mayor seguridad
$config_path = __DIR__ . '/../config.php';
if (file_exists($config_path)) {
    include_once $config_path;
} else {
    // Fallback: definir rutas básicas
    define('BASE_URL', '/redblog/frontend');
    function url($path = '') { 
        $base = BASE_URL;
        if (!empty($path) && $path[0] !== '/') $path = '/' . $path;
        return $base . $path; 
    }
    function asset($path) { 
        if ($path[0] !== '/') $path = '/' . $path;
        return BASE_URL . $path; 
    }
}

$current_user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="<?php echo url('/index.php'); ?>" class="nav-logo">RedBlog</a>
        
        <div class="nav-links">
            <!-- Botón de cambio de tema -->
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                🌙
            </button>
            <!-- Agrega esta línea en la sección de nav-links, antes de los enlaces condicionales -->
            <a href="<?php echo url('/pages/about.php'); ?>" class="nav-link">Acerca de</a>
            <?php if($current_user): ?>
                <a href="<?php echo url('/pages/create-post.php'); ?>" class="nav-link">Crear Post</a>
                <a href="<?php echo url('/pages/profile.php'); ?>" class="nav-link">Perfil</a>
                <?php if($current_user['role'] === 'admin'): ?>
                    <a href="<?php echo url('/pages/admin.php'); ?>" class="nav-link">Admin</a>
                <?php endif; ?>
                <a href="<?php echo url('/logout.php'); ?>" class="nav-button">Cerrar Sesión</a>
                <span class="user-welcome">Hola, <?php echo htmlspecialchars($current_user['username']); ?></span>
            <?php else: ?>
                <a href="<?php echo url('/pages/login.php'); ?>" class="nav-link">Iniciar Sesión</a>
                <a href="<?php echo url('/pages/register.php'); ?>" class="nav-button">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>