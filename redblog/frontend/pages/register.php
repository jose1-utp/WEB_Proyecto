<?php
// frontend/pages/register.php - ACTUALIZADO
session_start();

if(isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $preferred_theme = $_POST['preferred_theme'] ?? 'Otros';
    $country = $_POST['country'] ?? '';
    
    // Validaciones
    if(empty($username) || empty($email) || empty($password)) {
        $error = 'Todos los campos obligatorios son requeridos';
    } elseif($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif(strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $user = new User($db);
            $user->username = $username;
            $user->email = $email;
            $user->password = $password;
            $user->role = 'user';
            $user->preferred_theme = $preferred_theme;
            $user->country = $country;
            
            $result = $user->create();
            
            if($result['success']) {
                // Login automático
                $login_result = $user->login($email, $password);
                
                if($login_result['success']) {
                    $_SESSION['user'] = $login_result['user'];
                    $success = '¡Registro exitoso! Bienvenido a RedBlog';
                    header('Refresh: 2; URL=../index.php');
                } else {
                    $error = 'Registro exitoso pero error al iniciar sesión. Por favor inicia sesión manualmente.';
                }
            } else {
                $error = $result['message'];
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
    <title>Registrarse - RedBlog</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="form-container">
        <div class="form">
            <h2 class="form-title">Crear Cuenta</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $success; ?>
                    <p>Redirigiendo al inicio...</p>
                </div>
            <?php else: ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nombre de usuario *</label>
                    <input type="text" id="username" name="username" class="form-input" 
                           placeholder="Ej: juan123" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="ejemplo@correo.com" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Mínimo 6 caracteres" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar contraseña *</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-input" placeholder="Repite tu contraseña" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="preferred_theme">Tema preferido</label>
                        <select id="preferred_theme" name="preferred_theme" class="form-input">
                            <option value="Otros">Seleccionar tema</option>
                            <option value="Ciencia y Tecnologia" <?php echo ($_POST['preferred_theme'] ?? '') == 'Ciencia y Tecnologia' ? 'selected' : ''; ?>>Ciencia y Tecnología</option>
                            <option value="Programacion" <?php echo ($_POST['preferred_theme'] ?? '') == 'Programacion' ? 'selected' : ''; ?>>Programación</option>
                            <option value="Videojuegos" <?php echo ($_POST['preferred_theme'] ?? '') == 'Videojuegos' ? 'selected' : ''; ?>>Videojuegos</option>
                            <option value="Musica" <?php echo ($_POST['preferred_theme'] ?? '') == 'Musica' ? 'selected' : ''; ?>>Música</option>
                            <option value="Cine y Television" <?php echo ($_POST['preferred_theme'] ?? '') == 'Cine y Television' ? 'selected' : ''; ?>>Cine y Televisión</option>
                            <option value="Deporte" <?php echo ($_POST['preferred_theme'] ?? '') == 'Deporte' ? 'selected' : ''; ?>>Deporte</option>
                            <option value="Otros" <?php echo ($_POST['preferred_theme'] ?? '') == 'Otros' ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">País</label>
                        <input type="text" id="country" name="country" class="form-input" 
                               placeholder="Ej: España"
                               value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Crear Cuenta
                </button>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <span style="color: var(--text-muted);">¿Ya tienes cuenta? </span>
                    <a href="login.php" class="text-link">Inicia sesión aquí</a>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>