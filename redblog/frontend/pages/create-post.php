<?php
// frontend/pages/create-post.php - VERSIÓN FUNCIONAL
session_start();
include_once '../config.php';

if(!isset($_SESSION['user'])) {
    header('Location: ' . url('/pages/login.php'));
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/Post.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $theme = $_POST['theme'] ?? '';

    if(empty($title) || empty($content) || empty($theme)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $post = new Post($db);

            $post->title = $title;
            $post->content = $content;
            $post->theme = $theme;
            $post->author_id = $_SESSION['user']['id'];

            if($post->create()) {
                $success = '¡Post creado exitosamente!';
                // Redirigir después de 2 segundos
                header('Refresh: 2; URL=' . url('/index.php'));
            } else {
                $error = 'Error al crear el post';
            }
        } catch(Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Post - RedBlog</title>
    <link rel="stylesheet" href="<?php echo asset('/css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container">
            <div class="form">
                <h2 class="form-title">Crear Nueva Publicación</h2>
                
                <?php if($error): ?>
                    <div class="error-message">
                        ❌ <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="success-message">
                        ✅ <?php echo $success; ?>
                        <p>Redirigiendo al inicio...</p>
                    </div>
                <?php else: ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="Escribe un título llamativo" required 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="theme">Tema:</label>
                        <select id="theme" name="theme" class="form-input" required>
                            <option value="">Selecciona un tema</option>
                            <option value="Ciencia y Tecnologia" <?php echo ($_POST['theme'] ?? '') == 'Ciencia y Tecnologia' ? 'selected' : ''; ?>>Ciencia y Tecnología</option>
                            <option value="Ciencia y Tecnologia" <?php echo ($_POST['theme'] ?? '') == 'Libros' ? 'selected' : ''; ?>>Libros</option>
                            <option value="Programacion" <?php echo ($_POST['theme'] ?? '') == 'Programacion' ? 'selected' : ''; ?>>Programación</option>
                            <option value="Videojuegos" <?php echo ($_POST['theme'] ?? '') == 'Videojuegos' ? 'selected' : ''; ?>>Videojuegos</option>
                            <option value="Musica" <?php echo ($_POST['theme'] ?? '') == 'Musica' ? 'selected' : ''; ?>>Música</option>
                            <option value="Cine y Television" <?php echo ($_POST['theme'] ?? '') == 'Cine y Television' ? 'selected' : ''; ?>>Cine y Television</option>
                            <option value="Deportes" <?php echo ($_POST['theme'] ?? '') == 'Deportes' ? 'selected' : ''; ?>>Deportes</option>
                            <option value="Otros" <?php echo ($_POST['theme'] ?? '') == 'Otros' ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Contenido:</label>
                        <textarea id="content" name="content" class="form-input" 
                                  placeholder="Escribe el contenido de tu publicación..." 
                                  rows="8" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="form-button">
                        Publicar
                    </button>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="<?php echo url('/index.php'); ?>" class="text-link">← Volver al inicio</a>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="<?php echo asset('/js/script.js'); ?>"></script>
</body>
</html>