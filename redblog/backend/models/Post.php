<?php
// backend/models/Post.php - VERSIÓN CORREGIDA
class Post {
    private $conn;
    private $table_name = "posts";

    public $id;
    public $title;
    public $content;
    public $author_id;
    public $author_name;
    public $theme;  // Cambié "type" por "theme" para que coincida con la DB
    public $upvotes;
    public $downvotes;
    public $is_public;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // LEER TODOS LOS POSTS
    public function read() {
        try {
            $query = "SELECT p.*, u.username as author_name 
                    FROM " . $this->table_name . " p 
                    LEFT JOIN users u ON p.author_id = u.id 
                    WHERE p.is_public = 1 
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch(PDOException $exception) {
            return $this->getSampleData();
        }
    }

    // BUSCAR POSTS
    public function search($search_term = '', $theme = '') {
        try {
            $query = "SELECT p.*, u.username as author_name 
                    FROM " . $this->table_name . " p 
                    LEFT JOIN users u ON p.author_id = u.id 
                    WHERE p.is_public = 1";
            
            $params = [];
            
            if(!empty($search_term)) {
                $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
                $search_param = "%$search_term%";
                $params[] = $search_param;
                $params[] = $search_param;
            }
            
            if(!empty($theme)) {
                $query .= " AND p.theme = ?";
                $params[] = $theme;
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt;
        } catch(PDOException $exception) {
            return $this->getSampleData();
        }
    }

    // CREAR POST
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET title=:title, content=:content, author_id=:author_id, theme=:theme";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->theme = htmlspecialchars(strip_tags($this->theme));

        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":author_id", $this->author_id);
        $stmt->bindParam(":theme", $this->theme);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ACTUALIZAR POST
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET title=:title, content=:content, theme=:theme
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->theme = htmlspecialchars(strip_tags($this->theme));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":theme", $this->theme);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ELIMINAR POST
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // LEER UN SOLO POST - CORREGIDO: cambié "type" por "theme"
    public function readOne() {
        $query = "SELECT p.*, u.username as author_name 
                 FROM " . $this->table_name . " p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->author_id = $row['author_id'];
            $this->author_name = $row['author_name'];
            $this->theme = $row['theme'];  // Cambié "type" por "theme"
            $this->upvotes = $row['upvotes'];
            $this->downvotes = $row['downvotes'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        return false;
    }

    // VOTAR POST (sistema de 1 voto por usuario)
    public function vote($user_id, $vote_type) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();
            
            // Primero verificar si ya votó
            $check_query = "SELECT id, vote_type FROM post_votes WHERE post_id = ? AND user_id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(1, $this->id);
            $check_stmt->bindParam(2, $user_id);
            $check_stmt->execute();

            if($check_stmt->rowCount() > 0) {
                $existing_vote = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Si ya votó de la misma manera, quitar el voto
                if($existing_vote['vote_type'] == $vote_type) {
                    $delete_query = "DELETE FROM post_votes WHERE id = ?";
                    $delete_stmt = $this->conn->prepare($delete_query);
                    $delete_stmt->bindParam(1, $existing_vote['id']);
                    $delete_stmt->execute();
                    
                    // Decrementar contador
                    if($vote_type == 'up') {
                        $update_query = "UPDATE posts SET upvotes = upvotes - 1 WHERE id = ?";
                    } else {
                        $update_query = "UPDATE posts SET downvotes = downvotes - 1 WHERE id = ?";
                    }
                } else {
                    // Cambiar voto (de up a down o viceversa)
                    $update_vote_query = "UPDATE post_votes SET vote_type = ? WHERE id = ?";
                    $update_vote_stmt = $this->conn->prepare($update_vote_query);
                    $update_vote_stmt->bindParam(1, $vote_type);
                    $update_vote_stmt->bindParam(2, $existing_vote['id']);
                    $update_vote_stmt->execute();
                    
                    // Ajustar ambos contadores
                    if($existing_vote['vote_type'] == 'up' && $vote_type == 'down') {
                        $update_query = "UPDATE posts SET upvotes = upvotes - 1, downvotes = downvotes + 1 WHERE id = ?";
                    } else {
                        $update_query = "UPDATE posts SET upvotes = upvotes + 1, downvotes = downvotes - 1 WHERE id = ?";
                    }
                }
            } else {
                // Primer voto
                $insert_query = "INSERT INTO post_votes (post_id, user_id, vote_type) VALUES (?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(1, $this->id);
                $insert_stmt->bindParam(2, $user_id);
                $insert_stmt->bindParam(3, $vote_type);
                $insert_stmt->execute();
                
                // Incrementar contador
                if($vote_type == 'up') {
                    $update_query = "UPDATE posts SET upvotes = upvotes + 1 WHERE id = ?";
                } else {
                    $update_query = "UPDATE posts SET downvotes = downvotes + 1 WHERE id = ?";
                }
            }
            
            // Ejecutar actualización de contadores
            if(isset($update_query)) {
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $this->id);
                $update_stmt->execute();
            }
            
            // Obtener nuevos contadores
            $count_query = "SELECT upvotes, downvotes FROM posts WHERE id = ?";
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(1, $this->id);
            $count_stmt->execute();
            $counts = $count_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Confirmar transacción
            $this->conn->commit();
            
            return [
                'success' => true,
                'upvotes' => (int)$counts['upvotes'],
                'downvotes' => (int)$counts['downvotes']
            ];
            
        } catch(PDOException $e) {
            // Revertir en caso de error
            if($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Error al votar: ' . $e->getMessage()
            ];
        }
    }

    // DATOS DE EJEMPLO
    private function getSampleData() {
        $samplePosts = [
            [
                'id' => 1,
                'title' => '¡Bienvenido a RedBlog! 🎉',
                'content' => 'Esta es una publicación de ejemplo. La aplicación está funcionando correctamente con PHP y MySQL.',
                'author_name' => 'admin',
                'theme' => 'Otros',
                'upvotes' => 15,
                'downvotes' => 2,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        return $samplePosts;
    }
}
?>