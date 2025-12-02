<?php
// frontend/pages/post-detail.php - VERSIÓN REDISEÑADA
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

// Obtener ID del post
$post_id = $_GET['id'] ?? null;
if(!$post_id || !is_numeric($post_id)) {
    header('Location: ' . get_url('/index.php'));
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/Post.php';
include_once '../../backend/models/Comment.php';
include_once '../../backend/models/User.php';

$database = new Database();
$db = $database->getConnection();
$post_model = new Post($db);
$comment_model = new Comment($db);
$user_model = new User($db);

// Obtener post
$post_model->id = $post_id;
if(!$post_model->readOne()) {
    header('Location: ' . get_url('/index.php'));
    exit;
}

// Obtener comentarios anidados
$comments = $comment_model->getByPost($post_id);

// Procesar nuevo comentario
$comment_error = '';
$comment_success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    if(!isset($_SESSION['user'])) {
        $comment_error = 'Debes iniciar sesión para comentar';
    } else {
        $content = trim($_POST['comment_content']);
        $parent_id = $_POST['parent_id'] ?? null;
        
        if(empty($content)) {
            $comment_error = 'El comentario no puede estar vacío';
        } else {
            $comment_model->post_id = $post_id;
            $comment_model->user_id = $_SESSION['user']['id'];
            $comment_model->parent_id = $parent_id ?: null;
            $comment_model->content = $content;
            
            if($comment_model->create()) {
                $comment_success = 'Comentario publicado exitosamente';
                // Redirigir para evitar reenvío del formulario
                header('Location: ' . get_url("/pages/post-detail.php?id=$post_id"));
                exit;
            } else {
                $comment_error = 'Error al publicar el comentario';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post_model->title); ?> - RedBlog</title>
    <link rel="stylesheet" href="<?php echo get_asset('/css/style.css'); ?>">
    <style>
        /* Estilos específicos para comentarios anidados */
        .comments-section {
            margin-top: 3rem;
        }
        
        .comment {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .comment.reply {
            margin-left: 2.5rem;
            margin-top: 0.75rem;
            border-left: 3px solid var(--accent-primary);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .comment-author a {
            color: var(--accent-primary);
            text-decoration: none;
        }
        
        .comment-author a:hover {
            text-decoration: underline;
        }
        
        .comment-time {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .comment-content {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .comment-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .comment-votes {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .reply-btn, .edit-comment-btn, .delete-comment-btn {
            background: none;
            border: none;
            color: var(--accent-primary);
            cursor: pointer;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .reply-btn:hover, .edit-comment-btn:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        
        .delete-comment-btn {
            color: var(--error-text);
        }
        
        .delete-comment-btn:hover {
            background: rgba(220, 38, 38, 0.1);
        }
        
        .reply-form {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            display: none;
        }
        
        .reply-form textarea {
            width: 100%;
            margin-bottom: 0.5rem;
            min-height: 80px;
        }
        
        .no-comments {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }
        
        /* Niveles de comentarios */
        .comment-level-0 { margin-left: 0; }
        .comment-level-1 { margin-left: 2rem; }
        .comment-level-2 { margin-left: 4rem; }
        .comment-level-3 { margin-left: 6rem; }
        .comment-level-4 { margin-left: 8rem; }
        .comment-level-5 { margin-left: 10rem; }
        
        .comment-level-5 .comment {
            background: var(--bg-primary);
        }
        
        /* Botón para mostrar/ocultar respuestas */
        .show-replies-btn {
            background: none;
            border: none;
            color: var(--accent-primary);
            cursor: pointer;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .show-replies-btn:hover {
            background: rgba(59, 130, 246, 0.1);
        }
        
        .replies {
            margin-top: 0.75rem;
        }
        
        .replies.collapsed {
            display: none;
        }
    </style>
</head>
<body>
    <?php 
    $navbar_path = __DIR__ . '/../includes/navbar.php';
    if (file_exists($navbar_path)) include $navbar_path;
    ?>

    <div class="container-narrow">
        <!-- Post principal -->
        <div class="post-card">
            <div class="post-header">
                <h1 class="post-title"><?php echo htmlspecialchars($post_model->title); ?></h1>
                <span class="post-theme" data-theme="<?php echo htmlspecialchars($post_model->theme); ?>">
                    <?php echo htmlspecialchars($post_model->theme); ?>
                </span>
            </div>
            
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post_model->content)); ?>
            </div>
            
            <div class="post-meta">
                <div class="author-info">
                    <span>Por <a href="<?php echo get_url('/pages/profile.php?user_id=' . $post_model->author_id); ?>" 
                               class="author-link"><?php echo htmlspecialchars($post_model->author_name); ?></a></span>
                    <span class="separator">•</span>
                    <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post_model->created_at)); ?></span>
                    
                    <?php if(isset($_SESSION['user']) && ($_SESSION['user']['id'] == $post_model->author_id || $_SESSION['user']['role'] == 'admin')): ?>
                        <span class="separator">•</span>
                        <a href="<?php echo get_url('/pages/edit-post.php?id=' . $post_model->id); ?>" class="edit-link">Editar</a>
                        <span class="separator">•</span>
                        <a href="<?php echo get_url('/pages/delete-post.php?id=' . $post_model->id); ?>" class="delete-link">Eliminar</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sistema de votos y comentarios -->
            <div class="post-stats">
                <div class="vote-buttons">
                    <button class="vote-btn like-btn" onclick="votePost('up', <?php echo $post_model->id; ?>)" 
                            id="like-btn-<?php echo $post_model->id; ?>">
                        👍 <span id="like-count-<?php echo $post_model->id; ?>"><?php echo $post_model->upvotes; ?></span>
                    </button>
                    <button class="vote-btn dislike-btn" onclick="votePost('down', <?php echo $post_model->id; ?>)" 
                            id="dislike-btn-<?php echo $post_model->id; ?>">
                        👎 <span id="dislike-count-<?php echo $post_model->id; ?>"><?php echo $post_model->downvotes; ?></span>
                    </button>
                </div>
                
                <span class="stat-item comments-count">
                    💬 <span id="comment-count"><?php echo count($comments); ?></span> Comentarios
                </span>
            </div>
        </div>
        
        <!-- Formulario para nuevo comentario -->
        <div class="form" style="margin-top: 2rem; max-width: 100%;">
            <h3 style="margin-bottom: 1rem;">Añadir comentario</h3>
            
            <?php if($comment_error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $comment_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($comment_success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $comment_success; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['user'])): ?>
                <form method="POST" action="" id="commentForm">
                    <input type="hidden" name="parent_id" value="" id="parent_id">
                    
                    <div class="form-group">
                        <textarea id="comment_content" name="comment_content" class="form-input" 
                                  placeholder="Escribe tu comentario aquí..." rows="4" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Publicar comentario
                    </button>
                </form>
            <?php else: ?>
                <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <p>Debes <a href="<?php echo get_url('/pages/login.php'); ?>" class="text-link">iniciar sesión</a> para comentar.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sección de comentarios -->
        <div class="comments-section">
            <h2 style="margin-bottom: 1.5rem;">Comentarios</h2>
            
            <?php if(empty($comments)): ?>
                <div class="no-comments">
                    <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
                </div>
            <?php else: ?>
                <?php 
                // Función recursiva para mostrar comentarios anidados
                function displayComments($comments, $level = 0, $post_id, $base_url) {
                    foreach($comments as $comment) {
                        $levelClass = $level > 5 ? 'comment-level-5' : 'comment-level-' . $level;
                        $hasReplies = !empty($comment['replies']);
                        ?>
                        <div class="comment <?php echo $levelClass; ?>" id="comment-<?php echo $comment['id']; ?>" 
                             data-comment-id="<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <div>
                                    <span class="comment-author">
                                        <a href="<?php echo $base_url . '/pages/profile.php?user_id=' . $comment['user_id']; ?>">
                                            <?php echo htmlspecialchars($comment['username'] ?? 'Anónimo'); ?>
                                        </a>
                                    </span>
                                    <span class="comment-time">• <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                
                                <div class="comment-votes">
                                    <button class="vote-btn like-btn" style="font-size: 0.875rem; padding: 0.25rem;" 
                                            onclick="voteComment(this, <?php echo $comment['id']; ?>, 'up')">👍</button>
                                    <span id="comment-vote-<?php echo $comment['id']; ?>" style="font-weight: bold; font-size: 0.875rem;">
                                        <?php echo (($comment['upvotes'] ?? 0) - ($comment['downvotes'] ?? 0)); ?>
                                    </span>
                                    <button class="vote-btn dislike-btn" style="font-size: 0.875rem; padding: 0.25rem;" 
                                            onclick="voteComment(this, <?php echo $comment['id']; ?>, 'down')">👎</button>
                                </div>
                            </div>
                            
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                            
                            <div class="comment-actions">
                                <?php if(isset($_SESSION['user'])): ?>
                                    <button class="reply-btn" onclick="showReplyForm(<?php echo $comment['id']; ?>)">
                                        Responder
                                    </button>
                                    
                                    <?php if(isset($comment['user_id']) && ($_SESSION['user']['id'] == $comment['user_id'] || ($_SESSION['user']['role'] == 'admin' && $comment['user_id'] != $_SESSION['user']['id']))): ?>
                                        <button class="edit-comment-btn" onclick="editComment(<?php echo $comment['id']; ?>)">
                                            Editar
                                        </button>
                                        <button class="delete-comment-btn" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                            Eliminar
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Formulario de respuesta (oculto por defecto) -->
                            <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form">
                                <form method="POST" action="" class="reply-comment-form">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                    
                                    <div class="form-group">
                                        <textarea name="comment_content" class="form-input" 
                                                  placeholder="Escribe tu respuesta..." rows="3" required></textarea>
                                    </div>
                                    
                                    <div style="display: flex; gap: 1rem;">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Responder</button>
                                        <button type="button" class="btn btn-secondary" 
                                                onclick="hideReplyForm(<?php echo $comment['id']; ?>)">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Mostrar respuestas recursivamente -->
                            <?php if($hasReplies): ?>
                                <button class="show-replies-btn" onclick="toggleReplies(<?php echo $comment['id']; ?>)">
                                    <span id="replies-toggle-<?php echo $comment['id']; ?>">▼</span> 
                                    <span id="replies-count-<?php echo $comment['id']; ?>">
                                        <?php echo count($comment['replies']); ?> respuestas
                                    </span>
                                </button>
                                
                                <div id="replies-<?php echo $comment['id']; ?>" class="replies">
                                    <?php displayComments($comment['replies'], $level + 1, $post_id, $base_url); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
                
                displayComments($comments, 0, $post_id, get_url(''));
                ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Función para votar posts
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
            const originalLikeText = likeBtn.innerHTML;
            const originalDislikeText = dislikeBtn.innerHTML;
            
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
            }
        }
        
        // Función para votar comentarios
        async function voteComment(button, commentId, voteType) {
            const isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
            
            if(!isLoggedIn) {
                alert('Debes iniciar sesión para votar');
                window.location.href = '<?php echo get_url("/pages/login.php"); ?>';
                return;
            }
            
            button.disabled = true;
            const originalText = button.innerHTML;
            
            try {
                const response = await fetch('<?php echo get_url("/api/vote-comment.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment_id: commentId,
                        vote_type: voteType
                    })
                });
                
                const result = await response.json();
                
                if(result.success) {
                    // Actualizar contador
                    document.getElementById('comment-vote-' + commentId).textContent = 
                        (parseInt(result.upvotes) - parseInt(result.downvotes));
                    
                    showNotification('Voto registrado', 'success');
                } else {
                    showNotification(result.message || 'Error al registrar el voto', 'error');
                }
            } catch(error) {
                console.error('Error al votar comentario:', error);
                showNotification('Error de conexión al servidor', 'error');
            } finally {
                button.disabled = false;
            }
        }
        
        // Función para mostrar/ocultar formulario de respuesta
        function showReplyForm(commentId) {
            // Ocultar otros formularios abiertos
            document.querySelectorAll('.reply-form').forEach(form => {
                form.style.display = 'none';
            });
            
            const form = document.getElementById('reply-form-' + commentId);
            form.style.display = 'block';
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Establecer el parent_id en el formulario principal
            document.getElementById('parent_id').value = commentId;
        }
        
        function hideReplyForm(commentId) {
            document.getElementById('reply-form-' + commentId).style.display = 'none';
        }
        
        // Función para mostrar/ocultar respuestas
        function toggleReplies(commentId) {
            const repliesDiv = document.getElementById('replies-' + commentId);
            const toggleSpan = document.getElementById('replies-toggle-' + commentId);
            
            if(repliesDiv.classList.contains('collapsed')) {
                repliesDiv.classList.remove('collapsed');
                toggleSpan.textContent = '▼';
            } else {
                repliesDiv.classList.add('collapsed');
                toggleSpan.textContent = '▶';
            }
        }
        
        // Función para eliminar comentario
        async function deleteComment(commentId) {
            if(!confirm('¿Estás seguro de eliminar este comentario? Esta acción no se puede deshacer.')) {
                return;
            }
            
            try {
                const response = await fetch('<?php echo get_url("/api/delete-comment.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment_id: commentId
                    })
                });
                
                const result = await response.json();
                
                if(result.success) {
                    document.getElementById('comment-' + commentId).remove();
                    showNotification('Comentario eliminado', 'success');
                    
                    // Actualizar contador de comentarios
                    const commentCount = document.getElementById('comment-count');
                    commentCount.textContent = parseInt(commentCount.textContent) - 1;
                } else {
                    showNotification(result.message, 'error');
                }
            } catch(error) {
                console.error('Error al eliminar:', error);
                showNotification('Error al eliminar comentario', 'error');
            }
        }
        
        // Función para editar comentario
        async function editComment(commentId) {
            const commentDiv = document.getElementById('comment-' + commentId);
            const contentDiv = commentDiv.querySelector('.comment-content');
            const currentContent = contentDiv.textContent;
            
            const newContent = prompt('Editar comentario:', currentContent);
            if(newContent !== null && newContent.trim() !== '' && newContent !== currentContent) {
                try {
                    const response = await fetch('<?php echo get_url("/api/edit-comment.php"); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            comment_id: commentId,
                            content: newContent
                        })
                    });
                    
                    const result = await response.json();
                    
                    if(result.success) {
                        contentDiv.textContent = newContent;
                        showNotification('Comentario actualizado', 'success');
                    } else {
                        showNotification(result.message, 'error');
                    }
                } catch(error) {
                    console.error('Error al editar:', error);
                    showNotification('Error al editar comentario', 'error');
                }
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
                padding: 12px 24px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            
            if (type === 'success') {
                notification.style.backgroundColor = '#10b981';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#ef4444';
            } else {
                notification.style.backgroundColor = '#3b82f6';
            }
            
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
        
        // Enfocar textarea de comentario si hay error
        <?php if($comment_error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('comment_content').focus();
        });
        <?php endif; ?>
    </script>
</body>
</html>