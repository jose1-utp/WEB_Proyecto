<?php
// frontend/pages/admin.php - PANEL DE ADMIN COMPLETO
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Configuración de rutas
$base_url = '/redblog/frontend';

function get_url($path = '') {
    global $base_url;
    if (!empty($path) && $path[0] !== '/') $path = '/' . $path;
    return $base_url . $path;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';
include_once '../../backend/models/Post.php';

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);
$post_model = new Post($db);

// Obtener estadísticas
$users_result = $user_model->getAllUsers();
$users = $users_result->fetchAll(PDO::FETCH_ASSOC);

$posts_result = $post_model->read();
$posts = $posts_result->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas
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
    <title>Panel de Administración - RedBlog</title>
    <link rel="stylesheet" href="<?php echo get_url('/css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Panel de Administración</h1>
        
        <!-- Estadísticas -->
        <div class="admin-panel">
            <div class="admin-card">
                <h3>📊 Estadísticas Generales</h3>
                <div class="admin-stats">
                    <div class="admin-stat">
                        <span class="admin-stat-number"><?php echo $total_users; ?></span>
                        <span class="admin-stat-label">Usuarios</span>
                    </div>
                    <div class="admin-stat">
                        <span class="admin-stat-number"><?php echo $total_active_users; ?></span>
                        <span class="admin-stat-label">Activos</span>
                    </div>
                    <div class="admin-stat">
                        <span class="admin-stat-number"><?php echo $total_posts; ?></span>
                        <span class="admin-stat-label">Publicaciones</span>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3>⚙️ Acciones Rápidas</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                    <a href="#users-section" class="btn btn-secondary">Gestionar Usuarios</a>
                    <a href="#posts-section" class="btn btn-secondary">Gestionar Publicaciones</a>
                    <a href="<?php echo get_url('/pages/create-post.php'); ?>" class="btn btn-primary">Crear Publicación</a>
                </div>
            </div>
        </div>
        
        <!-- Sección de Usuarios -->
        <div id="users-section" style="margin-top: 3rem;">
            <h2 class="section-title">Gestión de Usuarios</h2>
            
            <div class="search-container">
                <input type="text" id="user-search" class="form-input" placeholder="Buscar usuarios...">
            </div>
            
            <div class="posts-container" style="max-height: 500px; overflow-y: auto;">
                <?php foreach($users as $user): ?>
                <div class="post-card user-card" data-user-id="<?php echo $user['id']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin-bottom: 0.5rem;">
                                <a href="<?php echo get_url('/pages/profile.php?user_id=' . $user['id']); ?>" 
                                   class="author-link">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </a>
                                <?php if($user['role'] == 'admin'): ?>
                                    <span style="background: var(--accent-primary); color: white; padding: 0.25rem 0.5rem; 
                                          border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">Admin</span>
                                <?php endif; ?>
                            </h3>
                            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.875rem;">
                                <span>Tema: <?php echo htmlspecialchars($user['preferred_theme']); ?></span>
                                <span>País: <?php echo htmlspecialchars($user['country']); ?></span>
                                <span>Publicaciones: <?php echo $user['post_count']; ?></span>
                            </div>
                        </div>
                        
                        <div class="post-actions">
                            <a href="<?php echo get_url('/pages/profile.php?user_id=' . $user['id']); ?>" 
                               class="edit-link">Ver Perfil</a>
                            <?php if($user['id'] != $_SESSION['user']['id']): ?>
                            <span class="separator">•</span>
                            <a href="<?php echo get_url('/api/delete-user.php?user_id=' . $user['id']); ?>" 
                               class="delete-link" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sección de Publicaciones -->
        <div id="posts-section" style="margin-top: 3rem;">
            <h2 class="section-title">Gestión de Publicaciones</h2>
            
            <div class="search-container">
                <input type="text" id="post-search" class="form-input" placeholder="Buscar publicaciones...">
            </div>
            
            <div class="posts-container">
                <?php foreach($posts as $post): ?>
                <div class="post-card">
                    <div class="post-header">
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <span class="post-theme" data-theme="<?php echo htmlspecialchars($post['theme']); ?>">
                            <?php echo htmlspecialchars($post['theme']); ?>
                        </span>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...
                    </div>
                    
                    <div class="post-meta">
                        <div class="author-info">
                            <span>Por <a href="<?php echo get_url('/pages/profile.php?user_id=' . $post['author_id']); ?>" 
                                       class="author-link"><?php echo htmlspecialchars($post['author_name']); ?></a></span>
                            <span class="separator">•</span>
                            <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                            <span class="separator">•</span>
                            <span><?php echo $post['upvotes']; ?> 👍</span>
                            <span class="separator">•</span>
                            <span><?php echo $post['downvotes']; ?> 👎</span>
                        </div>
                        
                        <div class="post-actions">
                            <a href="<?php echo get_url('/pages/post-detail.php?id=' . $post['id']); ?>" class="edit-link">Ver</a>
                            <span class="separator">•</span>
                            <a href="<?php echo get_url('/pages/edit-post.php?id=' . $post['id']); ?>" class="edit-link">Editar</a>
                            <span class="separator">•</span>
                            <a href="<?php echo get_url('/pages/delete-post.php?id=' . $post['id']); ?>" 
                               class="delete-link" onclick="return confirm('¿Eliminar publicación?')">Eliminar</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Búsqueda de usuarios
        document.getElementById('user-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const username = card.querySelector('h3 a').textContent.toLowerCase();
                const email = card.querySelector('p').textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Búsqueda de posts
        document.getElementById('post-search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const postCards = document.querySelectorAll('#posts-section .post-card');
            
            postCards.forEach(card => {
                const title = card.querySelector('.post-title').textContent.toLowerCase();
                const content = card.querySelector('.post-content').textContent.toLowerCase();
                const author = card.querySelector('.author-link').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || content.includes(searchTerm) || author.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>