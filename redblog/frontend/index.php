<?php
// frontend/index.php - DISEÑO SOBRIO COMPLETO
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

// Inicializar variables
$posts = [];
$error = '';
$search_query = $_GET['search'] ?? '';
$theme_filter = $_GET['theme'] ?? '';

try {
    if(file_exists('../backend/config/database.php') && file_exists('../backend/models/Post.php')) {
        include_once '../backend/config/database.php';
        include_once '../backend/models/Post.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $post = new Post($db);
        
        if(!empty($search_query) || !empty($theme_filter)) {
            $posts_result = $post->search($search_query, $theme_filter);
        } else {
            $posts_result = $post->read();
        }
        
        if($posts_result instanceof PDOStatement) {
            $posts = $posts_result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $posts = $posts_result;
        }
    } else {
        throw new Exception("Archivos del backend no encontrados");
    }
} catch(Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedBlog - Plataforma Comunitaria</title>
    <link rel="stylesheet" href="<?php echo get_asset('/css/style.css'); ?>">
    <style>
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }
        
        .vote-btn.loading {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php 
    $navbar_path = __DIR__ . '/includes/navbar.php';
    if (file_exists($navbar_path)) include $navbar_path;
    ?>

    <main class="container">
        <h1 class="page-title">Publicaciones Recientes</h1>
        
        <!-- Barra de búsqueda -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" class="form-input" 
                       placeholder="Buscar publicaciones..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="theme" class="form-input" style="min-width: 180px;">
                    <option value="">Todos los temas</option>
                    <option value="Ciencia y Tecnologia" <?php echo $theme_filter == 'Ciencia y Tecnologia' ? 'selected' : ''; ?>>Ciencia y Tecnología</option>
                    <option value="Programacion" <?php echo $theme_filter == 'Programacion' ? 'selected' : ''; ?>>Programación</option>
                    <option value="Videojuegos" <?php echo $theme_filter == 'Videojuegos' ? 'selected' : ''; ?>>Videojuegos</option>
                    <option value="Musica" <?php echo $theme_filter == 'Musica' ? 'selected' : ''; ?>>Música</option>
                    <option value="Cine y Television" <?php echo $theme_filter == 'Cine y Television' ? 'selected' : ''; ?>>Cine y Televisión</option>
                    <option value="Deporte" <?php echo $theme_filter == 'Deporte' ? 'selected' : ''; ?>>Deporte</option>
                    <option value="Otros" <?php echo $theme_filter == 'Otros' ? 'selected' : ''; ?>>Otros</option>
                </select>
                <button type="submit" class="btn btn-primary">Buscar</button>
                <?php if(!empty($search_query) || !empty($theme_filter)): ?>
                    <a href="<?php echo get_url('/index.php'); ?>" class="btn btn-secondary">Limpiar filtros</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div id="posts-container" class="posts-container">
            <?php if(empty($posts)): ?>
                <div class="no-posts">
                    <p>No hay publicaciones que coincidan con tu búsqueda.</p>
                    <?php if(isset($_SESSION['user'])): ?>
                        <a href="<?php echo get_url('/pages/create-post.php'); ?>" class="btn btn-primary">
                            Crear Primera Publicación
                        </a>
                    <?php else: ?>
                        <a href="<?php echo get_url('/pages/register.php'); ?>" class="btn btn-primary">
                            Regístrate para Publicar
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach($posts as $post): ?>
                <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                    <!-- Encabezado -->
                    <div class="post-header">
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <span class="post-theme" data-theme="<?php echo htmlspecialchars($post['theme']); ?>">
                            <?php echo htmlspecialchars($post['theme']); ?>
                        </span>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    
                    <!-- Información del autor y acciones -->
                    <div class="post-meta">
                        <div class="author-info">
                            <span>Por <a href="<?php echo get_url('/pages/profile.php?user_id=' . $post['author_id']); ?>" 
                                       class="author-link"><?php echo htmlspecialchars($post['author_name']); ?></a></span>
                            <span class="separator">•</span>
                            <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                            
                            <?php if(isset($_SESSION['user']) && ($_SESSION['user']['id'] == $post['author_id'] || $_SESSION['user']['role'] == 'admin')): ?>
                                <span class="separator">•</span>
                                <a href="<?php echo get_url('/pages/edit-post.php?id=' . $post['id']); ?>" class="edit-link">Editar</a>
                                <span class="separator">•</span>
                                <a href="<?php echo get_url('/pages/delete-post.php?id=' . $post['id']); ?>" class="delete-link">Eliminar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="post-stats">
                        <div class="vote-buttons">
                            <button class="vote-btn like-btn" onclick="votePost('up', <?php echo $post['id']; ?>)" 
                                    id="like-btn-<?php echo $post['id']; ?>">
                                👍 <span id="like-count-<?php echo $post['id']; ?>"><?php echo $post['upvotes']; ?></span>
                            </button>
                            <button class="vote-btn dislike-btn" onclick="votePost('down', <?php echo $post['id']; ?>)" 
                                    id="dislike-btn-<?php echo $post['id']; ?>">
                                👎 <span id="dislike-count-<?php echo $post['id']; ?>"><?php echo $post['downvotes']; ?></span>
                            </button>
                        </div>
                        
                        <a href="<?php echo get_url('/pages/post-detail.php?id=' . $post['id']); ?>" class="comments-link">
                        💬 <span>Comentarios</span>
                            <span>(<?php echo isset($post['comment_count']) ? $post['comment_count'] : 0; ?>)</span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div id="loading" class="loading">
            Cargando más publicaciones...
        </div>
    </main>

    <script>
        // Función para votar posts con manejo de errores mejorado
        async function votePost(voteType, postId) {
            const isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
            
            if(!isLoggedIn) {
                alert('Debes iniciar sesión para votar');
                window.location.href = '<?php echo get_url("/pages/login.php"); ?>';
                return;
            }
            
            const likeBtn = document.getElementById('like-btn-' + postId);
            const dislikeBtn = document.getElementById('dislike-btn-' + postId);
            
            // Deshabilitar botones temporalmente
            likeBtn.disabled = true;
            dislikeBtn.disabled = true;
            likeBtn.classList.add('loading');
            dislikeBtn.classList.add('loading');
            
            try {
                const response = await fetch('<?php echo get_url("/api/vote-post.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId,
                        vote_type: voteType
                    })
                });
                
                const result = await response.json();
                
                if(result.success) {
                    // Actualizar contadores
                    document.getElementById('like-count-' + postId).textContent = result.upvotes;
                    document.getElementById('dislike-count-' + postId).textContent = result.downvotes;
                    
                    // Mostrar notificación
                    showNotification('Voto registrado', 'success');
                } else {
                    showNotification(result.message || 'Error al registrar el voto', 'error');
                }
            } catch(error) {
                console.error('Error al votar:', error);
                showNotification('Error de conexión al servidor', 'error');
            } finally {
                // Rehabilitar botones
                likeBtn.disabled = false;
                dislikeBtn.disabled = false;
                likeBtn.classList.remove('loading');
                dislikeBtn.classList.remove('loading');
            }
        }
        
        // Sistema de notificaciones
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                max-width: 300px;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        }
        
        // Agregar estilos CSS para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>