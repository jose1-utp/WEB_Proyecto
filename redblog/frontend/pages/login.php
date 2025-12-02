<?php
// frontend/pages/login.php - VERSIÓN CON BOTÓN MEJORADO
session_start();

if(isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(empty($email) || empty($password)) {
        $error = 'Email y contraseña son obligatorios';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $user = new User($db);
            $result = $user->login($email, $password);
            
            if($result['success']) {
                $_SESSION['user'] = $result['user'];
                header('Location: ../index.php');
                exit;
            } else {
                $error = $result['message'];
            }
        } catch(Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - RedBlog</title>
    <link rel="stylesheet" href="<?php echo get_asset('/css/style.css'); ?>">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 2rem 1rem;
        }
        
        .login-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }
        
        .login-title {
            font-size: 1.875rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }
        
        .login-button {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .login-help {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .credential-box {
            background: var(--bg-primary);
            padding: 1.25rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .credential-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }
        
        .credential-item {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .credential-item:before {
            content: "•";
            color: var(--accent-primary);
        }
    </style>
</head>
<body>
    <?php 
    $navbar_path = __DIR__ . '/../includes/navbar.php';
    if (file_exists($navbar_path)) include $navbar_path;
    ?>

    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Iniciar Sesión</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="tu@email.com" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Tu contraseña" required>
                    <div style="text-align: right; margin-top: 0.25rem;">
                        <a href="#" style="font-size: 0.875rem; color: var(--accent-primary);">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    Iniciar Sesión
                </button>

                <div class="login-help">
                    <span style="color: var(--text-muted);">¿No tienes cuenta? </span>
                    <a href="<?php echo get_url('/pages/register.php'); ?>" class="text-link">Regístrate aquí</a>
                </div>
                
                <!-- Credenciales de prueba -->
                <div class="credential-box">
                    <div class="credential-title">Credenciales de prueba:</div>
                    <div class="credential-item">
                        <strong>Admin:</strong> admin@redblog.com / password
                    </div>
                    <div class="credential-item">
                        <strong>Usuario:</strong> usuario1@redblog.com / password
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>