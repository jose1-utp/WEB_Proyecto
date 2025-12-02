<?php
// backend/models/Comment.php - VERSIÓN CORREGIDA
class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $post_id;
    public $user_id;       // Si tu tabla usa 'author_id' en lugar de 'user_id', cambia esto
    public $parent_id;
    public $content;
    public $upvotes;
    public $downvotes;
    public $created_at;
    public $username;
    public $replies = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREAR COMENTARIO
    public function create() {
        // Verificar qué columnas tiene tu tabla
        $query = "INSERT INTO " . $this->table_name . " 
                SET post_id=:post_id, user_id=:user_id, parent_id=:parent_id, content=:content";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->post_id = htmlspecialchars(strip_tags($this->post_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->parent_id = $this->parent_id ? htmlspecialchars(strip_tags($this->parent_id)) : null;
        $this->content = htmlspecialchars(strip_tags($this->content));

        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":parent_id", $this->parent_id);
        $stmt->bindParam(":content", $this->content);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // OBTENER COMENTARIOS POR POST (con estructura jerárquica)
    public function getByPost($post_id) {
        try {
            // Primero, verifiquemos la estructura de la tabla
            $check_query = "SHOW COLUMNS FROM " . $this->table_name;
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->execute();
            $columns = $check_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Determinar el nombre de la columna de usuario
            $user_column = 'user_id';
            if (!in_array('user_id', $columns)) {
                // Buscar columnas alternativas
                if (in_array('author_id', $columns)) {
                    $user_column = 'author_id';
                } elseif (in_array('user', $columns)) {
                    $user_column = 'user';
                } else {
                    // Si no hay columna de usuario, usar un valor por defecto
                    $user_column = null;
                }
            }
            
            if ($user_column) {
                $query = "SELECT c.*, u.username 
                         FROM " . $this->table_name . " c 
                         LEFT JOIN users u ON c." . $user_column . " = u.id 
                         WHERE c.post_id = ? 
                         ORDER BY c.created_at ASC";
            } else {
                // Si no hay columna de usuario, mostrar comentarios sin información de usuario
                $query = "SELECT c.*, 'Anónimo' as username 
                         FROM " . $this->table_name . " c 
                         WHERE c.post_id = ? 
                         ORDER BY c.created_at ASC";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $post_id);
            $stmt->execute();

            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar en estructura jerárquica
            return $this->buildTree($comments);
        } catch(PDOException $exception) {
            // En caso de error, devolver array vacío
            error_log("Error al obtener comentarios: " . $exception->getMessage());
            return [];
        }
    }

    // CONSTRUIR ÁRBOL DE COMENTARIOS
    private function buildTree($comments, $parent_id = null) {
        $branch = [];
        
        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parent_id) {
                $children = $this->buildTree($comments, $comment['id']);
                if ($children) {
                    $comment['replies'] = $children;
                } else {
                    $comment['replies'] = [];
                }
                $branch[] = $comment;
            }
        }
        
        return $branch;
    }

    // OBTENER COMENTARIO POR ID
    public function readOne() {
        try {
            $query = "SELECT c.*, u.username 
                     FROM " . $this->table_name . " c 
                     LEFT JOIN users u ON c.user_id = u.id 
                     WHERE c.id = ? LIMIT 0,1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->id = $row['id'];
                $this->post_id = $row['post_id'];
                $this->user_id = $row['user_id'];
                $this->parent_id = $row['parent_id'];
                $this->content = $row['content'];
                $this->username = $row['username'];
                $this->upvotes = $row['upvotes'];
                $this->downvotes = $row['downvotes'];
                $this->created_at = $row['created_at'];
                
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error al leer comentario: " . $e->getMessage());
            return false;
        }
    }

    // ACTUALIZAR COMENTARIO
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET content=:content 
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // ELIMINAR COMENTARIO (y sus respuestas)
    public function delete() {
        try {
            // Primero eliminar respuestas
            $query_delete_children = "DELETE FROM " . $this->table_name . " WHERE parent_id = ?";
            $stmt_children = $this->conn->prepare($query_delete_children);
            $stmt_children->bindParam(1, $this->id);
            $stmt_children->execute();

            // Luego eliminar el comentario principal
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error al eliminar comentario: " . $e->getMessage());
            return false;
        }
    }

    // VOTAR COMENTARIO
    public function vote($user_id, $vote_type) {
        try {
            // Primero verificar si la tabla comment_votes existe
            $table_exists = $this->conn->query("SHOW TABLES LIKE 'comment_votes'")->rowCount() > 0;
            
            if (!$table_exists) {
                // Si no existe la tabla, solo actualizar contadores directamente
                $current_query = "SELECT upvotes, downvotes FROM " . $this->table_name . " WHERE id = ?";
                $current_stmt = $this->conn->prepare($current_query);
                $current_stmt->bindParam(1, $this->id);
                $current_stmt->execute();
                $current = $current_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($vote_type === 'up') {
                    $new_upvotes = $current['upvotes'] + 1;
                    $new_downvotes = $current['downvotes'];
                } else {
                    $new_upvotes = $current['upvotes'];
                    $new_downvotes = $current['downvotes'] + 1;
                }
                
                $update_query = "UPDATE " . $this->table_name . " SET upvotes = ?, downvotes = ? WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $new_upvotes);
                $update_stmt->bindParam(2, $new_downvotes);
                $update_stmt->bindParam(3, $this->id);
                
                return $update_stmt->execute();
            }
            
            // Si la tabla existe, usar el sistema de votos por usuario
            $check_query = "SELECT id FROM comment_votes WHERE comment_id = ? AND user_id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(1, $this->id);
            $check_stmt->bindParam(2, $user_id);
            $check_stmt->execute();

            if($check_stmt->rowCount() > 0) {
                $update_query = "UPDATE comment_votes SET vote_type = ? WHERE comment_id = ? AND user_id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $vote_type);
                $update_stmt->bindParam(2, $this->id);
                $update_stmt->bindParam(3, $user_id);
                $update_stmt->execute();
            } else {
                $insert_query = "INSERT INTO comment_votes (comment_id, user_id, vote_type) VALUES (?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(1, $this->id);
                $insert_stmt->bindParam(2, $user_id);
                $insert_stmt->bindParam(3, $vote_type);
                $insert_stmt->execute();
            }

            // Actualizar contadores
            return $this->updateCommentVoteCounters();
        } catch(PDOException $e) {
            error_log("Error al votar comentario: " . $e->getMessage());
            return false;
        }
    }

    // ACTUALIZAR CONTADORES DE VOTOS DEL COMENTARIO
    private function updateCommentVoteCounters() {
        try {
            $query = "SELECT 
                     COUNT(CASE WHEN vote_type = 'up' THEN 1 END) as upvotes,
                     COUNT(CASE WHEN vote_type = 'down' THEN 1 END) as downvotes
                     FROM comment_votes WHERE comment_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $update_query = "UPDATE comments SET upvotes = ?, downvotes = ? WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(1, $result['upvotes']);
            $update_stmt->bindParam(2, $result['downvotes']);
            $update_stmt->bindParam(3, $this->id);
            
            return $update_stmt->execute();
        } catch(PDOException $e) {
            error_log("Error al actualizar contadores: " . $e->getMessage());
            return false;
        }
    }

    // OBTENER NÚMERO DE RESPUESTAS
    public function getReplyCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE parent_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>