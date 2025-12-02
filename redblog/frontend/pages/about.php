<?php
session_start();

// Configuración de rutas
$base_url = '/redblog/frontend';

function get_url($path = '') {
    global $base_url;
    if (!empty($path) && $path[0] !== '/') $path = '/' . $path;
    return $base_url . $path;
}

function get_asset($path) {
    global $base_url;
    if ($path[0] !== '/') $path = '/' . $path;
    return $base_url . $path;
}

// Obtener estadísticas básicas
include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';
include_once '../../backend/models/Post.php';
include_once '../../backend/models/Comment.php';

$database = new Database();
$db = $database->getConnection();

$user_model = new User($db);
$post_model = new Post($db);
$comment_model = new Comment($db);

// Contar estadísticas
$users_result = $user_model->getAllUsers();
$users = $users_result->fetchAll(PDO::FETCH_ASSOC);

$posts_result = $post_model->read();
$posts = $posts_result->fetchAll(PDO::FETCH_ASSOC);

$total_users = count($users);
$total_posts = count($posts);
$active_users = array_filter($users, function($user) {
    return $user['is_active'] == 1;
});
$total_active_users = count($active_users);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de RedBlog</title>
    <link rel="stylesheet" href="<?php echo get_asset('/css/style.css'); ?>">
    <style>
        .about-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.25rem;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        
        .about-description {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.7;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 1.75rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .tech-stack {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 2rem;
            margin: 3rem 0;
            border: 1px solid var(--border-color);
        }
        
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .tech-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: 8px;
        }
        
        .tech-icon {
            font-size: 2rem;
        }
        
        .tech-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .tech-role {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .role-icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        .role-content h3 {
            margin-bottom: 0.5rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php 
    $navbar_path = __DIR__ . '/../includes/navbar.php';
    if (file_exists($navbar_path)) include $navbar_path;
    ?>

    <div class="about-container">
        <!-- Encabezado -->
        <div class="about-header">
            <div class="about-logo">📝</div>
            <h1>RedBlog – Plataforma de Publicaciones Interactivas</h1>
            <p class="about-description">
                RedBlog es una aplicación web inspirada en plataformas como Reddit. 
                Su propósito es permitir a los usuarios compartir publicaciones, comentar y reaccionar a contenidos, 
                mientras que los administradores gestionan la comunidad y los visitantes pueden explorar publicaciones de manera limitada.
            </p>
        </div>
        
        <!-- Objetivos -->
        <div class="feature-card">
            <h2>🎯 Objetivos</h2>
            <ul style="color: var(--text-secondary); line-height: 1.7; padding-left: 1.5rem;">
                <li>Implementar un sistema de publicaciones tipo blog comunitario.</li>
                <li>Ofrecer diferentes experiencias según el rol: visitante, usuario registrado y administrador.</li>
                <li>Practicar conceptos de autenticación, autorización y manejo de roles en aplicaciones web modernas.</li>
                <li>Aplicar arquitectura full-stack con tecnologías modernas.</li>
            </ul>
        </div>
        
        <!-- Roles y Vistas -->
        <h2 style="margin-top: 3rem; margin-bottom: 1.5rem;">👥 Roles y Vistas</h2>
        
        <div class="tech-role">
            <div class="role-icon">🔹</div>
            <div class="role-content">
                <h3>Visitante (no autenticado)</h3>
                <p>Puede navegar publicaciones públicas, ver comentarios, registrarse o iniciar sesión. Acceso limitado (no puede crear publicaciones ni comentar).</p>
            </div>
        </div>
        
        <div class="tech-role">
            <div class="role-icon">🔹</div>
            <div class="role-content">
                <h3>Usuario (autenticado)</h3>
                <p>Puede crear publicaciones (texto, imágenes, links), comentar y reaccionar a publicaciones, editar o eliminar sus publicaciones y comentarios. Acceso a un perfil personal con su información básica.</p>
            </div>
        </div>
        
        <div class="tech-role">
            <div class="role-icon">🔹</div>
            <div class="role-content">
                <h3>Administrador</h3>
                <p>Puede gestionar usuarios (activar/desactivar cuentas), moderar publicaciones y comentarios (eliminarlos si infringen normas). Tiene un panel de control para estadísticas básicas de la plataforma.</p>
            </div>
        </div>
        
        <!-- Tecnologías -->
        <div class="tech-stack">
            <h2>🛠️ Tecnologías Utilizadas</h2>
            <div class="tech-grid">
                <div class="tech-item">
                    <div class="tech-icon">🌐</div>
                    <div>
                        <div class="tech-name">Frontend</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">HTML5, CSS3, JavaScript, PHP</div>
                    </div>
                </div>
                
                <div class="tech-item">
                    <div class="tech-icon">⚙️</div>
                    <div>
                        <div class="tech-name">Backend</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">PHP, MySQL</div>
                    </div>
                </div>
                
                <div class="tech-item">
                    <div class="tech-icon">🗄️</div>
                    <div>
                        <div class="tech-name">Base de Datos</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">MySQL</div>
                    </div>
                </div>
                
                <div class="tech-item">
                    <div class="tech-icon">🔐</div>
                    <div>
                        <div class="tech-name">Autenticación</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Sesiones PHP</div>
                    </div>
                </div>
                
                <div class="tech-item">
                    <div class="tech-icon">👑</div>
                    <div>
                        <div class="tech-name">Control de Roles</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Middleware en PHP</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas en tiempo real -->
        <h2 style="margin-top: 3rem; margin-bottom: 1.5rem;">📊 Estadísticas de la Plataforma</h2>
        <div class="stats-container">
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_users; ?></span>
                <span class="stat-label">Usuarios Registrados</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_posts; ?></span>
                <span class="stat-label">Publicaciones</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_active_users; ?></span>
                <span class="stat-label">Usuarios Activos</span>
            </div>
            
            <div class="stat-card">
                <span class="stat-number">7</span>
                <span class="stat-label">Categorías</span>
            </div>
        </div>
        
        <!-- Funcionalidades Clave -->
        <h2 style="margin-top: 3rem; margin-bottom: 1.5rem;">📂 Funcionalidades Clave</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3 class="feature-title">Autenticación y Autorización</h3>
                <p class="feature-description">Sistema de autenticación seguro con roles de usuario (usuario, administrador).</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">✏️</div>
                <h3 class="feature-title">CRUD Completo</h3>
                <p class="feature-description">Crear, leer, actualizar y eliminar publicaciones y comentarios.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">👍</div>
                <h3 class="feature-title">Sistema de Reacciones</h3>
                <p class="feature-description">Los usuarios pueden reaccionar (like, dislike) a publicaciones y comentarios.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">👑</div>
                <h3 class="feature-title">Panel de Administración</h3>
                <p class="feature-description">Gestión de usuarios, moderación de contenido y estadísticas de la plataforma.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3 class="feature-title">Interfaz Adaptativa</h3>
                <p class="feature-description">Diseño responsive que se adapta a diferentes dispositivos y preferencias (modo oscuro/claro).</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3 class="feature-title">Comentarios Anidados</h3>
                <p class="feature-description">Sistema de comentarios jerárquicos que permite conversaciones profundas.</p>
            </div>
        </div>
        
        <!-- Extensiones Futuras -->
        <div class="feature-card" style="margin-top: 3rem;">
            <h2>🔮 Posibles Extensiones Futuras</h2>
            <ul style="color: var(--text-secondary); line-height: 1.7; padding-left: 1.5rem;">
                <li>Sistema de notificaciones en tiempo real.</li>
                <li>Subcomunidades temáticas (subreddits).</li>
                <li>Buscador avanzado por etiquetas o categorías.</li>
                <li>Soporte multimedia (videos, GIFs, imágenes).</li>
                <li>Sistema de mensajería privada entre usuarios.</li>
                <li>Aplicación móvil nativa.</li>
                <li>API pública para desarrolladores.</li>
                <li>Sistema de insignias y logros.</li>
            </ul>
        </div>
        
        <!-- Enlace para volver -->
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?php echo get_url('/index.php'); ?>" class="btn btn-primary">
                ← Volver a la página principal
            </a>
        </div>
    </div>
</body>
</html>