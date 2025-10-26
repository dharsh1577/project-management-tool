<?php
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

$projectManager = new ProjectManager($db);
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_project':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            if ($projectManager->createProject($user_id, $name, $description)) {
                $success = "Project created successfully!";
            } else {
                $error = "Error creating project!";
            }
            break;
            
        case 'update_project':
            $project_id = $_POST['project_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            if ($projectManager->updateProject($project_id, $user_id, $name, $description)) {
                $success = "Project updated successfully!";
            } else {
                $error = "Error updating project!";
            }
            break;
            
        case 'delete_project':
            $project_id = $_POST['project_id'];
            if ($projectManager->deleteProject($project_id, $user_id)) {
                $success = "Project deleted successfully!";
            } else {
                $error = "Error deleting project!";
            }
            break;
    }
}

// Get user's projects
$projects = $projectManager->getProjects($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - Project Management Tool</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <a href="dashboard.php" class="logo">Project Manager</a>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                    <a href="tasks.php" class="btn btn-primary">Tasks</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">My Projects</h2>
                <button class="btn btn-primary modal-trigger" data-modal="createProjectModal">
                    + New Project
                </button>
            </div>

            <?php if (empty($projects)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <h3>No projects yet</h3>
                    <p>Create your first project to get started!</p>
                    <button class="btn btn-primary modal-trigger" data-modal="createProjectModal">
                        Create First Project
                    </button>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($projects as $project): 
                        $tasks = $projectManager->getTasks($project['id']);
                        $completed_tasks = array_filter($tasks, function($task) {
                            return $task['status'] === 'done';
                        });
                    ?>
                        <div class="project-item">
                            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                                <h3 style="margin: 0; flex: 1;"><?php echo htmlspecialchars($project['name']); ?></h3>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary modal-trigger" 
                                            data-modal="editProjectModal"
                                            data-project-id="<?php echo $project['id']; ?>"
                                            data-project-name="<?php echo htmlspecialchars($project['name']); ?>"
                                            data-project-description="<?php echo htmlspecialchars($project['description']); ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this project and all its tasks?')">
                                        <input type="hidden" name="action" value="delete_project">
                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            
                            <p><?php echo htmlspecialchars($project['description']); ?></p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                <div>
                                    <small><strong>Tasks:</strong> <?php echo count($tasks); ?> total, <?php echo count($completed_tasks); ?> completed</small>
                                </div>
                                <div>
                                    <a href="tasks.php?project_id=<?php echo $project['id']; ?>" class="btn btn-primary">
                                        View Tasks (<?php echo count($tasks); ?>)
                                    </a>
                                </div>
                            </div>
                            
                            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                                <small>Created: <?php echo date('M j, Y g:i A', strtotime($project['created_at'])); ?></small><br>
                                <small>Updated: <?php echo date('M j, Y g:i A', strtotime($project['updated_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Create New Project</h3>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <form method="POST" action="projects.php">
                <input type="hidden" name="action" value="create_project">
                
                <div class="form-group">
                    <label class="form-label" for="project_name">Project Name</label>
                    <input type="text" class="form-control" id="project_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="project_description">Description</label>
                    <textarea class="form-control" id="project_description" name="description" rows="4"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn modal-close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div id="editProjectModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Edit Project</h3>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <form method="POST" action="projects.php">
                <input type="hidden" name="action" value="update_project">
                <input type="hidden" name="project_id" id="edit_project_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit_project_name">Project Name</label>
                    <input type="text" class="form-control" id="edit_project_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_project_description">Description</label>
                    <textarea class="form-control" id="edit_project_description" name="description" rows="4"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn modal-close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
    // Handle edit modal data population
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-trigger') && e.target.dataset.modal === 'editProjectModal') {
            document.getElementById('edit_project_id').value = e.target.dataset.projectId;
            document.getElementById('edit_project_name').value = e.target.dataset.projectName;
            document.getElementById('edit_project_description').value = e.target.dataset.projectDescription;
        }
    });
    </script>
</body>
</html>