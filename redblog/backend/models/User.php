<?php
// backend/models/User.php - VERSIÓN COMPLETA
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $is_active;
    public $preferred_theme;
    public $country;
    public $bio;
    public $avatar_url;
    public $created_at;
    public $post_count;
    public $comment_count;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREAR USUARIO
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    SET username = :username, 
                        email = :email, 
                        password = :password, 
                        role = :role,
                        preferred_theme = :preferred_theme,
                        country = :country";

            $stmt = $this->conn->prepare($query);

            // Validar datos
            $this->validateUserData();

            // Verificar si el usuario/email ya existe
            if ($this->emailExists() || $this->usernameExists()) {
                return ["success" => false, "message" => "El email o nombre de usuario ya existe"];
            }

            // Hash de la contraseña
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $password_hash);
            $stmt->bindParam(":role", $this->role);
            $stmt->bindParam(":preferred_theme", $this->preferred_theme);
            $stmt->bindParam(":country", $this->country);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return ["success" => true, "user_id" => $this->id];
            }
            
            return ["success" => false, "message" => "Error al crear usuario"];
            
        } catch(PDOException $exception) {
            return ["success" => false, "message" => "Error de base de datos: " . $exception->getMessage()];
        }
    }

    // VALIDAR DATOS DEL USUARIO
    private function validateUserData() {
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->preferred_theme = htmlspecialchars(strip_tags($this->preferred_theme));
        $this->country = htmlspecialchars(strip_tags($this->country));
        
        if (empty($this->preferred_theme)) {
            $this->preferred_theme = 'Otros';
        }
        
        if (empty($this->country)) {
            $this->country = 'No especificado';
        }
    }

    // LOGIN
    public function login($email, $password) {
        try {
            $query = "SELECT id, username, email, password, role, is_active, 
                             preferred_theme, country, bio, avatar_url, created_at
                     FROM " . $this->table_name . " 
                     WHERE email = ? LIMIT 0,1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $email);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $row['password'])) {
                    if($row['is_active']) {
                        return [
                            "success" => true,
                            "user" => [
                                "id" => $row['id'],
                                "username" => $row['username'],
                                "email" => $row['email'],
                                "role" => $row['role'],
                                "preferred_theme" => $row['preferred_theme'],
                                "country" => $row['country'],
                                "bio" => $row['bio'],
                                "avatar_url" => $row['avatar_url']
                            ]
                        ];
                    } else {
                        return ["success" => false, "message" => "Cuenta desactivada"];
                    }
                }
            }
            return ["success" => false, "message" => "Credenciales incorrectas"];
        } catch(PDOException $exception) {
            return ["success" => false, "message" => "Error de base de datos"];
        }
    }

    // ACTUALIZAR PERFIL
    public function updateProfile($user_id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " SET ";
            $updates = [];
            $params = [];
            
            // Construir consulta dinámica
            if (isset($data['username'])) {
                $updates[] = "username = ?";
                $params[] = htmlspecialchars(strip_tags($data['username']));
            }
            
            if (isset($data['preferred_theme'])) {
                $updates[] = "preferred_theme = ?";
                $params[] = htmlspecialchars(strip_tags($data['preferred_theme']));
            }
            
            if (isset($data['country'])) {
                $updates[] = "country = ?";
                $params[] = htmlspecialchars(strip_tags($data['country']));
            }
            
            if (isset($data['bio'])) {
                $updates[] = "bio = ?";
                $params[] = htmlspecialchars(strip_tags($data['bio']));
            }
            
            if (isset($data['password']) && !empty($data['password'])) {
                $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $updates[] = "password = ?";
                $params[] = $password_hash;
            }
            
            if (empty($updates)) {
                return ["success" => false, "message" => "No hay datos para actualizar"];
            }
            
            $query .= implode(", ", $updates) . " WHERE id = ?";
            $params[] = $user_id;
            
            $stmt = $this->conn->prepare($query);
            
            if($stmt->execute($params)) {
                return ["success" => true, "message" => "Perfil actualizado correctamente"];
            }
            
            return ["success" => false, "message" => "Error al actualizar perfil"];
            
        } catch(PDOException $exception) {
            return ["success" => false, "message" => "Error de base de datos: " . $exception->getMessage()];
        }
    }

    // OBTENER USUARIO POR ID
    public function getById($user_id) {
        $query = "SELECT u.*, 
                         (SELECT COUNT(*) FROM posts WHERE author_id = u.id) as post_count,
                         (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
                 FROM " . $this->table_name . " u 
                 WHERE u.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // OBTENER TODOS LOS USUARIOS (para admin)
    public function getAllUsers() {
        $query = "SELECT u.*, 
                         (SELECT COUNT(*) FROM posts WHERE author_id = u.id) as post_count,
                         (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
                 FROM " . $this->table_name . " u 
                 ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // ELIMINAR USUARIO (admin only)
    public function deleteUser($user_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            
            if($stmt->execute()) {
                return ["success" => true, "message" => "Usuario eliminado correctamente"];
            }
            
            return ["success" => false, "message" => "Error al eliminar usuario"];
            
        } catch(PDOException $exception) {
            return ["success" => false, "message" => "Error de base de datos: " . $exception->getMessage()];
        }
    }

    // VERIFICAR SI EL EMAIL EXISTE
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // VERIFICAR SI EL USERNAME EXISTE
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // OBTENER POSTS DEL USUARIO
    public function getUserPosts($user_id) {
        $query = "SELECT p.*, u.username as author_name 
                 FROM posts p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.author_id = ? AND p.is_public = 1 
                 ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }
}
?>