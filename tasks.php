<?php
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

$projectManager = new ProjectManager($db);
$user_id = $_SESSION['user_id'];

// Get projects for dropdown
$projects = $projectManager->getProjects($user_id);

// Handle filters
$filters = [];
$project_id = $_GET['project_id'] ?? '';
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$search_term = $_GET['search'] ?? '';

if ($project_id) {
    $filters['project_id'] = $project_id;
}
if ($status_filter) {
    $filters['status'] = $status_filter;
}
if ($priority_filter) {
    $filters['priority'] = $priority_filter;
}

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_task':
            $project_id = $_POST['project_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $status = $_POST['status'];
            $priority = $_POST['priority'];
            $due_date = $_POST['due_date'];
            
            if ($projectManager->createTask($project_id, $title, $description, $status, $priority, $due_date)) {
                $success = "Task created successfully!";
            } else {
                $error = "Error creating task!";
            }
            break;
            
        case 'update_task':
            $task_id = $_POST['task_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $status = $_POST['status'];
            $priority = $_POST['priority'];
            $due_date = $_POST['due_date'];
            
            if ($projectManager->updateTask($task_id, $title, $description, $status, $priority, $due_date)) {
                $success = "Task updated successfully!";
            } else {
                $error = "Error updating task!";
            }
            break;
            
        case 'delete_task':
            $task_id = $_POST['task_id'];
            if ($projectManager->deleteTask($task_id)) {
                $success = "Task deleted successfully!";
            } else {
                $error = "Error deleting task!";
            }
            break;
            
        case 'update_status':
            $task_id = $_POST['task_id'];
            $status = $_POST['status'];
            // This is typically handled via AJAX, but we'll support both
            if ($projectManager->updateTask($task_id, '', '', $status, '', '')) {
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating status']);
                exit;
            }
            break;
    }
}

// Get tasks based on filters
if ($search_term) {
    $tasks = $projectManager->searchTasks($user_id, $search_term);
} elseif ($project_id) {
    $tasks = $projectManager->getTasks($project_id, $filters);
} else {
    // Get all tasks for user
    $tasks = [];
    foreach ($projects as $project) {
        $project_tasks = $projectManager->getTasks($project['id'], $filters);
        foreach ($project_tasks as $task) {
            $task['project_name'] = $project['name'];
            $tasks[] = $task;
        }
    }
    
    // Sort tasks by due date and priority
    usort($tasks, function($a, $b) {
        if ($a['due_date'] == $b['due_date']) {
            $priority_order = ['high' => 1, 'medium' => 2, 'low' => 3];
            $a_priority = isset($a['priority']) && isset($priority_order[$a['priority']]) ? $priority_order[$a['priority']] : 4;
            $b_priority = isset($b['priority']) && isset($priority_order[$b['priority']]) ? $priority_order[$b['priority']] : 4;
            return $a_priority - $b_priority;
        }
        return strtotime($a['due_date']) - strtotime($b['due_date']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Project Management Tool</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
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
                    <a href="projects.php" class="btn btn-primary">Projects</a>
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
                <h2 class="card-title">Task Management</h2>
                <button class="btn btn-primary modal-trigger" data-modal="createTaskModal">
                    + New Task
                </button>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="tasks.php" id="task-filters">
                    <div class="filter-group">
                        <div>
                            <label class="form-label">Project</label>
                            <select class="form-control" name="project_id" onchange="this.form.submit()">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>" 
                                        <?php echo ($project_id == $project['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="todo" <?php echo ($status_filter == 'todo') ? 'selected' : ''; ?>>To Do</option>
                                <option value="in-progress" <?php echo ($status_filter == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="done" <?php echo ($status_filter == 'done') ? 'selected' : ''; ?>>Done</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="form-label">Priority</label>
                            <select class="form-control" name="priority" onchange="this.form.submit()">
                                <option value="">All Priority</option>
                                <option value="high" <?php echo ($priority_filter == 'high') ? 'selected' : ''; ?>>High</option>
                                <option value="medium" <?php echo ($priority_filter == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="low" <?php echo ($priority_filter == 'low') ? 'selected' : ''; ?>>Low</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="form-label">&nbsp;</label>
                            <a href="tasks.php" class="btn" style="display: block;">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Box -->
            <div class="filter-section">
                <form method="GET" action="tasks.php">
                    <div class="search-box">
                        <input type="text" class="form-control search-input" name="search" 
                               placeholder="Search tasks..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <span class="search-icon">🔍</span>
                    </div>
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                </form>
            </div>

            <?php if (empty($tasks)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <h3>No tasks found</h3>
                    <p><?php echo $search_term ? 'No tasks match your search criteria.' : 'Create your first task to get started!'; ?></p>
                    <button class="btn btn-primary modal-trigger" data-modal="createTaskModal">
                        Create First Task
                    </button>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($tasks as $task): 
                        $is_overdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status'] !== 'done';
                    ?>
                        <div class="task-item <?php echo $task['status']; ?> priority-<?php echo $task['priority']; ?> 
                            <?php echo $is_overdue ? 'overdue' : ''; ?>">
                            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                                <h3 style="margin: 0; flex: 1;"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary modal-trigger" 
                                            data-modal="editTaskModal"
                                            data-task-id="<?php echo $task['id']; ?>"
                                            data-task-title="<?php echo htmlspecialchars($task['title']); ?>"
                                            data-task-description="<?php echo htmlspecialchars($task['description']); ?>"
                                            data-task-status="<?php echo $task['status']; ?>"
                                            data-task-priority="<?php echo $task['priority']; ?>"
                                            data-task-due-date="<?php echo $task['due_date']; ?>">
                                        Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete_task">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            
                            <?php if (!empty($task['description'])): ?>
                                <p><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <select class="status-select form-control" data-task-id="<?php echo $task['id']; ?>" 
                                            data-original-value="<?php echo $task['status']; ?>"
                                            style="width: auto; padding: 4px 8px;">
                                        <option value="todo" <?php echo ($task['status'] == 'todo') ? 'selected' : ''; ?>>To Do</option>
                                        <option value="in-progress" <?php echo ($task['status'] == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="done" <?php echo ($task['status'] == 'done') ? 'selected' : ''; ?>>Done</option>
                                    </select>
                                    
                                    <span class="status-badge status-<?php echo $task['priority']; ?>">
                                        <?php echo ucfirst($task['priority']); ?> priority
                                    </span>
                                </div>
                                
                                <div style="text-align: right;">
                                    <?php if (isset($task['project_name'])): ?>
                                        <small><strong>Project:</strong> <?php echo htmlspecialchars($task['project_name']); ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($task['due_date']): ?>
                                        <small class="<?php echo $is_overdue ? 'overdue-text' : ''; ?>">
                                            <strong>Due:</strong> <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                            <?php if ($is_overdue): ?> ⚠️<?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                                <small>Created: <?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?></small>
                                <?php if ($task['updated_at'] != $task['created_at']): ?>
                                    <br><small>Updated: <?php echo date('M j, Y g:i A', strtotime($task['updated_at'])); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Task Modal -->
       <!-- Create Task Modal -->
    <div id="createTaskModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <h3 style="margin: 0; color: #2c3e50;">Create New Task</h3>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #7f8c8d;">×</button>
            </div>
            
            <form method="POST" action="tasks.php" style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                <input type="hidden" name="action" value="create_task">
                
                <div class="form-group">
                    <label class="form-label" for="task_project">
                        <i class='bx bx-folder' style="margin-right: 8px;"></i>
                        Project
                    </label>
                    <select class="form-control" id="task_project" name="project_id" required style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                        <option value="">Select a project...</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="task_title">
                        <i class='bx bx-rename' style="margin-right: 8px;"></i>
                        Task Title
                    </label>
                    <input type="text" class="form-control" id="task_title" name="title" required 
                           placeholder="Enter task title..." style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="task_description">
                        <i class='bx bx-text' style="margin-right: 8px;"></i>
                        Description
                    </label>
                    <textarea class="form-control" id="task_description" name="description" rows="4" 
                              placeholder="Enter task description..." style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="task_status">
                            <i class='bx bx-task' style="margin-right: 8px;"></i>
                            Status
                        </label>
                        <select class="form-control" id="task_status" name="status" style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                            <option value="todo">📝 To Do</option>
                            <option value="in-progress">🔄 In Progress</option>
                            <option value="done">✅ Done</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="task_priority">
                            <i class='bx bx-flag' style="margin-right: 8px;"></i>
                            Priority
                        </label>
                        <select class="form-control" id="task_priority" name="priority" style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                            <option value="low">🟢 Low</option>
                            <option value="medium" selected>🟡 Medium</option>
                            <option value="high">🔴 High</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="task_due_date">
                        <i class='bx bx-calendar' style="margin-right: 8px;"></i>
                        Due Date
                    </label>
                    <input type="date" class="form-control" id="task_due_date" name="due_date" 
                           style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                    <small style="color: #666; margin-top: 5px; display: block;">Leave empty if no due date</small>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #f0f0f0;">
                    <button type="button" class="btn modal-close" style="padding: 10px 20px; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 25px; background: #3498db; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class='bx bx-plus' style="margin-right: 5px;"></i>
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
       <!-- Edit Task Modal -->
    <div id="editTaskModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0;">
                <h3 style="margin: 0; color: #2c3e50;">Edit Task</h3>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #7f8c8d;">×</button>
            </div>
            
            <form method="POST" action="tasks.php" style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                <input type="hidden" name="action" value="update_task">
                <input type="hidden" name="task_id" id="edit_task_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit_task_title">
                        <i class='bx bx-rename' style="margin-right: 8px;"></i>
                        Task Title
                    </label>
                    <input type="text" class="form-control" id="edit_task_title" name="title" required 
                           placeholder="Enter task title..." style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_task_description">
                        <i class='bx bx-text' style="margin-right: 8px;"></i>
                        Description
                    </label>
                    <textarea class="form-control" id="edit_task_description" name="description" rows="4" 
                              placeholder="Enter task description..." style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_task_status">
                            <i class='bx bx-task' style="margin-right: 8px;"></i>
                            Status
                        </label>
                        <select class="form-control" id="edit_task_status" name="status" style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                            <option value="todo">📝 To Do</option>
                            <option value="in-progress">🔄 In Progress</option>
                            <option value="done">✅ Done</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="edit_task_priority">
                            <i class='bx bx-flag' style="margin-right: 8px;"></i>
                            Priority
                        </label>
                        <select class="form-control" id="edit_task_priority" name="priority" style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                            <option value="low">🟢 Low</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="high">🔴 High</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_task_due_date">
                        <i class='bx bx-calendar' style="margin-right: 8px;"></i>
                        Due Date
                    </label>
                    <input type="date" class="form-control" id="edit_task_due_date" name="due_date" 
                           style="padding: 12px; border-radius: 8px; border: 2px solid #e1e8ed;">
                    <small style="color: #666; margin-top: 5px; display: block;">Leave empty if no due date</small>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; padding-top: 1rem; border-top: 2px solid #f0f0f0;">
                    <button type="button" class="btn modal-close" style="padding: 10px 20px; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 25px; background: #3498db; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class='bx bx-save' style="margin-right: 5px;"></i>
                        Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script src="js/script.js"></script>
    <script>
    // Handle edit modal data population
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-trigger') && e.target.dataset.modal === 'editTaskModal') {
            document.getElementById('edit_task_id').value = e.target.dataset.taskId;
            document.getElementById('edit_task_title').value = e.target.dataset.taskTitle;
            document.getElementById('edit_task_description').value = e.target.dataset.taskDescription;
            document.getElementById('edit_task_status').value = e.target.dataset.taskStatus;
            document.getElementById('edit_task_priority').value = e.target.dataset.taskPriority;
            document.getElementById('edit_task_due_date').value = e.target.dataset.taskDueDate;
        }
    });
    
    // Add overdue styling
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.task-item').forEach(function(task) {
            const dueDate = task.querySelector('small:contains("Due:")');
            if (dueDate && dueDate.textContent.includes('⚠️')) {
                task.style.borderLeftColor = '#e74c3c';
                dueDate.style.color = '#e74c3c';
                dueDate.style.fontWeight = 'bold';
            }
        });
    });
    </script>
    
    <style>
    .overdue-text {
        color: #e74c3c !important;
        font-weight: bold;
    }
    .task-item.overdue {
        border-left-color: #e74c3c !important;
        background: linear-gradient(90deg, rgba(231,76,60,0.1) 0%, rgba(255,255,255,1) 4%);
    }
    </style>
</body>
</html>