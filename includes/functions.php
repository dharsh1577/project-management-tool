<?php
require_once "config.php";

class ProjectManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Project methods
    public function createProject($user_id, $name, $description) {
        $query = "INSERT INTO projects SET user_id=:user_id, name=:name, description=:description";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        return $stmt->execute();
    }

    public function getProjects($user_id) {
        $query = "SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProject($project_id, $user_id, $name, $description) {
        $query = "UPDATE projects SET name=:name, description=:description, updated_at=NOW() 
                  WHERE id=:id AND user_id=:user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $project_id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    public function deleteProject($project_id, $user_id) {
        $query = "DELETE FROM projects WHERE id=:id AND user_id=:user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $project_id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    // Task methods
    public function createTask($project_id, $title, $description, $status, $priority, $due_date) {
        $query = "INSERT INTO tasks SET project_id=:project_id, title=:title, description=:description, 
                  status=:status, priority=:priority, due_date=:due_date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":priority", $priority);
        $stmt->bindParam(":due_date", $due_date);
        return $stmt->execute();
    }

    public function getTasks($project_id, $filters = []) {
        $query = "SELECT * FROM tasks WHERE project_id = :project_id";
        
        // Add filters
        if(isset($filters['status']) && !empty($filters['status'])) {
            $query .= " AND status = :status";
        }
        if(isset($filters['priority']) && !empty($filters['priority'])) {
            $query .= " AND priority = :priority";
        }
        
        $query .= " ORDER BY 
            CASE priority 
                WHEN 'high' THEN 1 
                WHEN 'medium' THEN 2 
                WHEN 'low' THEN 3 
            END, due_date ASC";
            
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        
        if(isset($filters['status']) && !empty($filters['status'])) {
            $stmt->bindParam(":status", $filters['status']);
        }
        if(isset($filters['priority']) && !empty($filters['priority'])) {
            $stmt->bindParam(":priority", $filters['priority']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTask($task_id, $title, $description, $status, $priority, $due_date) {
        $query = "UPDATE tasks SET title=:title, description=:description, status=:status, 
                  priority=:priority, due_date=:due_date, updated_at=NOW() WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":priority", $priority);
        $stmt->bindParam(":due_date", $due_date);
        $stmt->bindParam(":id", $task_id);
        return $stmt->execute();
    }

    public function deleteTask($task_id) {
        $query = "DELETE FROM tasks WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $task_id);
        return $stmt->execute();
    }

    public function searchTasks($user_id, $search_term) {
        $query = "SELECT t.*, p.name as project_name 
                  FROM tasks t 
                  JOIN projects p ON t.project_id = p.id 
                  WHERE p.user_id = :user_id 
                  AND (t.title LIKE :search OR t.description LIKE :search)
                  ORDER BY t.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $search_term = "%$search_term%";
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>