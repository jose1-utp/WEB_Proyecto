<?php
// frontend/pages/profile.php - PERFIL COMPLETO
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

// Obtener ID del usuario
$user_id = $_GET['user_id'] ?? ($_SESSION['user']['id'] ?? null);
if (!$user_id) {
    header('Location: ' . get_url('/index.php'));
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';
include_once '../../backend/models/Post.php';

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);
$post_model = new Post($db);

// Obtener información del usuario
$user = $user_model->getById($user_id);
if (!$user) {
    header('Location: ' . get_url('/index.php'));
    exit;
}

// Obtener posts del usuario
$user_posts_result = $user_model->getUserPosts($user_id);
$user_posts = $user_posts_result->fetchAll(PDO::FETCH_ASSOC);

// Verificar permisos
$is_own_profile = isset($_SESSION['user']) && $_SESSION['user']['id'] == $user_id;
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['role'] == 'admin';

// Procesar actualización del perfil
$update_message = '';
$update_error = '';

if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_data = [
        'username' => $_POST['username'] ?? '',
        'preferred_theme' => $_POST['preferred_theme'] ?? '',
        'country' => $_POST['country'] ?? '',
        'bio' => $_POST['bio'] ?? ''
    ];
    
    // Si se proporciona nueva contraseña
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $update_error = 'Las contraseñas no coinciden';
        } else {
            $update_data['password'] = $_POST['new_password'];
        }
    }
    
    if (!$update_error) {
        $result = $user_model->updateProfile($user_id, $update_data);
        if ($result['success']) {
            $update_message = $result['message'];
            // Actualizar datos en sesión
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['username'] = $update_data['username'];
                $_SESSION['user']['preferred_theme'] = $update_data['preferred_theme'];
            }
            // Recargar datos del usuario
            $user = $user_model->getById($user_id);
        } else {
            $update_error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['username']); ?> - RedBlog</title>
    <link rel="stylesheet" href="<?php echo get_asset('/css/style.css'); ?>">
</head>
<body>
    <?php 
    $navbar_path = __DIR__ . '/../includes/navbar.php';
    if (file_exists($navbar_path)) include $navbar_path;
    ?>

    <div class="container">
        <div class="profile-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title" style="text-align: left; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h1>
                    <p style="color: var(--text-muted); margin-bottom: 1rem;">
                        Miembro desde <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
                
                <?php if ($is_admin && !$is_own_profile): ?>
                <div class="post-actions">
                    <button onclick="confirmDeleteUser(<?php echo $user['id']; ?>)" 
                            class="btn btn-danger" style="font-size: 0.875rem;">
                        Eliminar Usuario
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($update_message): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $update_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($update_error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $update_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-info">
                <div class="profile-item">
                    <strong>Email</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="profile-item">
                    <strong>Tema Preferido</strong>
                    <span><?php echo htmlspecialchars($user['preferred_theme']); ?></span>
                </div>
                
                <div class="profile-item">
                    <strong>País</strong>
                    <span><?php echo htmlspecialchars($user['country']); ?></span>
                </div>
                
                <div class="profile-item">
                    <strong>Publicaciones</strong>
                    <span><?php echo $user['post_count']; ?></span>
                </div>
                
                <div class="profile-item">
                    <strong>Comentarios</strong>
                    <span><?php echo $user['comment_count']; ?></span>
                </div>
                
                <div class="profile-item">
                    <strong>Rol</strong>
                    <span style="text-transform: capitalize;"><?php echo $user['role']; ?></span>
                </div>
            </div>
            
            <?php if ($user['bio']): ?>
            <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-primary); border-radius: 8px;">
                <h3 style="margin-bottom: 0.75rem; font-size: 1.1rem;">Biografía</h3>
                <p style="color: var(--text-secondary); line-height: 1.7;"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($is_own_profile): ?>
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 1.5rem;">Editar Perfil</h3>
                <form method="POST" action="" style="display: grid; gap: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Nombre de usuario</label>
                            <input type="text" name="username" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tema preferido</label>
                            <select name="preferred_theme" class="form-input" required>
                                <option value="">Seleccionar tema</option>
                                <option value="Ciencia y Tecnologia" <?php echo $user['preferred_theme'] == 'Ciencia y Tecnologia' ? 'selected' : ''; ?>>Ciencia y Tecnología</option>
                                <option value="Programacion" <?php echo $user['preferred_theme'] == 'Programacion' ? 'selected' : ''; ?>>Programación</option>
                                <option value="Videojuegos" <?php echo $user['preferred_theme'] == 'Videojuegos' ? 'selected' : ''; ?>>Videojuegos</option>
                                <option value="Musica" <?php echo $user['preferred_theme'] == 'Musica' ? 'selected' : ''; ?>>Música</option>
                                <option value="Cine y Television" <?php echo $user['preferred_theme'] == 'Cine y Television' ? 'selected' : ''; ?>>Cine y Televisión</option>
                                <option value="Deporte" <?php echo $user['preferred_theme'] == 'Deporte' ? 'selected' : ''; ?>>Deporte</option>
                                <option value="Otros" <?php echo $user['preferred_theme'] == 'Otros' ? 'selected' : ''; ?>>Otros</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>País</label>
                            <input type="text" name="country" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['country']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Biografía</label>
                            <textarea name="bio" class="form-input" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: 8px;">
                        <h4 style="margin-bottom: 1rem; font-size: 1rem;">Cambiar Contraseña</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Nueva contraseña</label>
                                <input type="password" name="new_password" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label>Confirmar contraseña</label>
                                <input type="password" name="confirm_password" class="form-input">
                            </div>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.5rem;">
                            Deja estos campos vacíos si no quieres cambiar la contraseña
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Publicaciones del usuario -->
        <div style="margin-top: 3rem;">
            <h2 class="section-title">Publicaciones de <?php echo htmlspecialchars($user['username']); ?></h2>
            
            <?php if (empty($user_posts)): ?>
                <div class="no-posts">
                    <p>Este usuario no ha creado publicaciones aún.</p>
                </div>
            <?php else: ?>
                <div class="posts-container">
                    <?php foreach($user_posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <h2 class="post-title">
                                <a href="<?php echo get_url('/pages/post-detail.php?id=' . $post['id']); ?>" 
                                   style="color: var(--text-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <span class="post-theme" data-theme="<?php echo htmlspecialchars($post['theme']); ?>">
                                <?php echo htmlspecialchars($post['theme']); ?>
                            </span>
                        </div>
                        
                        <div class="post-content">
                            <?php 
                            $content = $post['content'];
                            if (strlen($content) > 300) {
                                echo htmlspecialchars(substr($content, 0, 300)) . '...';
                            } else {
                                echo htmlspecialchars($content);
                            }
                            ?>
                        </div>
                        
                        <div class="post-meta">
                            <div class="author-info">
                                <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                                <span class="separator">•</span>
                                <span><?php echo $post['upvotes']; ?> 👍</span>
                                <span class="separator">•</span>
                                <span><?php echo $post['downvotes']; ?> 👎</span>
                                <span class="separator">•</span>
                                <a href="<?php echo get_url('/pages/post-detail.php?id=' . $post['id']); ?>" 
                                   class="comments-link" style="font-size: 0.875rem;">
                                    Comentarios (<?php echo $post['comment_count'] ?? 0; ?>)
                                </a>
                            </div>
                            
                            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] == $post['author_id'] || $_SESSION['user']['role'] == 'admin')): ?>
                            <div class="post-actions">
                                <a href="<?php echo get_url('/pages/edit-post.php?id=' . $post['id']); ?>" class="edit-link">Editar</a>
                                <span class="separator">•</span>
                                <a href="<?php echo get_url('/pages/delete-post.php?id=' . $post['id']); ?>" class="delete-link">Eliminar</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDeleteUser(userId) {
            if (confirm('¿Estás seguro de eliminar este usuario? Se eliminarán todas sus publicaciones y comentarios. Esta acción no se puede deshacer.')) {
                window.location.href = '<?php echo get_url("/api/delete-user.php?user_id="); ?>' + userId;
            }
        }
    </script>
</body>
</html>