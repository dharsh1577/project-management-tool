<?php
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

$projectManager = new ProjectManager($db);
$user_id = $_SESSION['user_id'];

// Get user's projects
$projects = $projectManager->getProjects($user_id);

// Get recent tasks across all projects
$recent_tasks = [];
$all_tasks = [];

foreach ($projects as $project) {
    $tasks = $projectManager->getTasks($project['id']);
    $all_tasks = array_merge($all_tasks, $tasks);
    $recent_tasks = array_merge($recent_tasks, array_slice($tasks, 0, 3));
}

// Sort recent tasks by due date
usort($recent_tasks, function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});
$recent_tasks = array_slice($recent_tasks, 0, 5);

// Statistics
$total_projects = count($projects);
$total_tasks = count($all_tasks);
$completed_tasks = 0;
$overdue_tasks = 0;
$today_tasks = 0;

foreach ($all_tasks as $task) {
    if ($task['status'] === 'done') {
        $completed_tasks++;
    }
    if ($task['due_date'] && strtotime($task['due_date']) < time() && $task['status'] !== 'done') {
        $overdue_tasks++;
    }
    if ($task['due_date'] && date('Y-m-d') == $task['due_date']) {
        $today_tasks++;
    }
}

$completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

// Get high priority tasks
$high_priority_tasks = array_filter($all_tasks, function($task) {
    return $task['priority'] === 'high' && $task['status'] !== 'done';
});
$high_priority_tasks = array_slice($high_priority_tasks, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Project Management Tool</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-hero">
        <div class="container">
            <!-- Enhanced Header -->
            <div class="header glass" style="border-radius: 20px; margin-bottom: 2rem;">
    <div class="header-content">
        <a href="dashboard.php" class="logo" style="font-size: 2rem; color: #ffffff; text-decoration: none; display: flex; align-items: center; gap: 10px;">
          <img src="assets/logo.png" alt="Project Manager Logo" 
     style="height: 55px; width: auto; background: transparent; border: 2px solid white; border-radius: 50%; padding: 6px; box-shadow: 0 0 10px rgba(255,255,255,0.3);">
            Project Manager
        </a>
        <div class="user-info">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                    <small style="color: rgba(255,255,255,0.8);">Last login: Today</small>
                </div>
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="projects.php" class="action-btn">
                    <i class='bx bx-folder-plus'></i>
                    New Project
                </a>
                <a href="tasks.php" class="action-btn">
                    <i class='bx bx-task'></i>
                    Create Task
                </a>
                <button class="action-btn modal-trigger" data-modal="quickTaskModal">
                    <i class='bx bx-plus-circle'></i>
                    Quick Task
                </button>
                <a href="#" class="action-btn">
                    <i class='bx bx-stats'></i>
                    Reports
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card floating">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number"><?php echo $total_projects; ?></div>
                    <div class="stat-label">Active Projects</div>
                    <small style="color: #666;">+2 this week</small>
                </div>

                <div class="stat-card floating" style="animation-delay: 0.2s;">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $total_tasks; ?></div>
                    <div class="stat-label">Total Tasks</div>
                    <small style="color: #666;"><?php echo $today_tasks; ?> due today</small>
                </div>

                <div class="stat-card floating" style="animation-delay: 0.4s;">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-number"><?php echo $overdue_tasks; ?></div>
                    <div class="stat-label">Overdue</div>
                    <small style="color: #e74c3c;">Needs attention</small>
                </div>

               <div class="stat-card floating" style="animation-delay: 0.6s;">
    <div class="progress-ring">
        <svg width="100" height="100" viewBox="0 0 100 100">
            <defs>
                <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3498db" />
                    <stop offset="100%" stop-color="#9b59b6" />
                </linearGradient>
            </defs>
            <circle class="ring-bg" cx="50" cy="50" r="40" />
            <circle class="ring-progress" cx="50" cy="50" r="40" 
                    stroke="url(#progressGradient)"
                    stroke-dasharray="251.2"
                    stroke-dashoffset="<?php echo 251.2 - (251.2 * $completion_rate / 100); ?>" />
        </svg>
        <div class="ring-text"><?php echo $completion_rate; ?>%</div>
    </div>
    <div class="stat-label">Completion Rate</div>
    <small style="color: #666;">Overall progress</small>
</div>
            </div>

            <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; margin-top: 2rem;">
                <!-- Recent Projects & Tasks -->
                <div>
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3 class="section-title">📁 Recent Projects</h3>
                            <a href="projects.php" class="btn btn-primary">View All</a>
                        </div>
                        
                        <?php if (empty($projects)): ?>
                            <div style="text-align: center; padding: 2rem;">
                                <i class='bx bx-folder-open' style="font-size: 3rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                                <h4>No projects yet</h4>
                                <p>Create your first project to get started!</p>
                                <a href="projects.php" class="btn btn-primary">Create Project</a>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($projects, 0, 4) as $project): 
                                $project_tasks = $projectManager->getTasks($project['id']);
                                $completed_project_tasks = array_filter($project_tasks, function($task) {
                                    return $task['status'] === 'done';
                                });
                                $project_progress = count($project_tasks) > 0 ? round((count($completed_project_tasks) / count($project_tasks)) * 100) : 0;
                            ?>
                                <div class="project-item" style="margin-bottom: 1.5rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 0.5rem 0; color: #2c3e50;"><?php echo htmlspecialchars($project['name']); ?></h4>
                                            <p style="margin: 0; color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($project['description']); ?></p>
                                        </div>
                                        <span class="status-badge" style="background: #3498db; color: white;">
                                            <?php echo count($project_tasks); ?> tasks
                                        </span>
                                    </div>
                                    
                                    <div style="margin-bottom: 0.5rem;">
                                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #666;">
                                            <span>Progress</span>
                                            <span><?php echo $project_progress; ?>%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $project_progress; ?>%;"></div>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #666;">
                                        <span>Updated <?php echo date('M j', strtotime($project['updated_at'])); ?></span>
                                        <a href="tasks.php?project_id=<?php echo $project['id']; ?>" class="btn" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">View Tasks</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3 class="section-title">🎯 High Priority Tasks</h3>
                            <a href="tasks.php?priority=high" class="btn btn-primary">View All</a>
                        </div>
                        
                        <?php if (empty($high_priority_tasks)): ?>
                            <div style="text-align: center; padding: 1rem; color: #666;">
                                <i class='bx bx-check-circle' style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                <p>No high priority tasks! 🎉</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($high_priority_tasks as $task): 
                                $is_overdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status'] !== 'done';
                            ?>
                                <div class="task-item priority-high" style="margin-bottom: 1rem; border-left-color: #e74c3c;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h5 style="margin: 0 0 0.3rem 0; color: #2c3e50;"><?php echo htmlspecialchars($task['title']); ?></h5>
                                            <p style="margin: 0; color: #666; font-size: 0.8rem;"><?php echo htmlspecialchars($task['description']); ?></p>
                                        </div>
                                        <span class="status-badge status-<?php echo $task['status']; ?>" style="font-size: 0.7rem;">
                                            <?php echo str_replace('-', ' ', $task['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; font-size: 0.8rem;">
                                        <span style="color: <?php echo $is_overdue ? '#e74c3c' : '#666'; ?>; font-weight: <?php echo $is_overdue ? 'bold' : 'normal'; ?>;">
                                            📅 <?php echo $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : 'No due date'; ?>
                                            <?php if ($is_overdue): ?> ⚠️<?php endif; ?>
                                        </span>
                                        <select class="status-select form-control" data-task-id="<?php echo $task['id']; ?>" 
                                                data-original-value="<?php echo $task['status']; ?>"
                                                style="width: auto; padding: 2px 6px; font-size: 0.7rem;">
                                            <option value="todo" <?php echo ($task['status'] == 'todo') ? 'selected' : ''; ?>>To Do</option>
                                            <option value="in-progress" <?php echo ($task['status'] == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="done" <?php echo ($task['status'] == 'done') ? 'selected' : ''; ?>>Done</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar - Upcoming Tasks -->
                <div>
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3 class="section-title">📅 Upcoming Tasks</h3>
                            <a href="tasks.php" class="btn btn-primary">View All</a>
                        </div>
                        
                        <?php if (empty($recent_tasks)): ?>
                            <div style="text-align: center; padding: 1rem; color: #666;">
                                <i class='bx bx-calendar' style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                <p>No upcoming tasks</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_tasks as $task): 
                                $is_overdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status'] !== 'done';
                                $is_today = $task['due_date'] && date('Y-m-d') == $task['due_date'];
                            ?>
                                <div class="task-item <?php echo $task['status']; ?> priority-<?php echo $task['priority']; ?>" 
                                     style="margin-bottom: 1rem; <?php echo $is_today ? 'border-left-color: #f39c12; background: linear-gradient(90deg, rgba(243,156,18,0.1) 0%, rgba(255,255,255,1) 4%);' : ''; ?>">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h5 style="margin: 0 0 0.3rem 0; color: #2c3e50; font-size: 0.9rem;"><?php echo htmlspecialchars($task['title']); ?></h5>
                                            <p style="margin: 0; color: #666; font-size: 0.7rem;"><?php echo htmlspecialchars($task['description']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; font-size: 0.7rem;">
                                        <span class="status-badge status-<?php echo $task['status']; ?>" style="font-size: 0.6rem;">
                                            <?php echo str_replace('-', ' ', $task['status']); ?>
                                        </span>
                                        <span style="color: <?php echo $is_overdue ? '#e74c3c' : ($is_today ? '#f39c12' : '#666'); ?>; font-weight: <?php echo $is_overdue || $is_today ? 'bold' : 'normal'; ?>;">
                                            <?php if ($is_today): ?>
                                                🎯 Today
                                            <?php else: ?>
                                                📅 <?php echo $task['due_date'] ? date('M j', strtotime($task['due_date'])) : 'No date'; ?>
                                            <?php endif; ?>
                                            <?php if ($is_overdue): ?> ⚠️<?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Team Progress -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3 class="section-title">👥 Team Progress</h3>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem;">
                            <div class="progress-ring" style="width: 100px; height: 100px; margin: 0 auto 1rem;">
                                <svg width="100" height="100" viewBox="0 0 100 100">
                                    <circle class="ring-bg" cx="50" cy="50" r="40" />
                                    <circle class="ring-progress" cx="50" cy="50" r="40" 
                                            stroke-dasharray="251"
                                            stroke-dashoffset="75" />
                                </svg>
                                <div class="ring-text" style="font-size: 1.2rem;">70%</div>
                            </div>
                            <div style="font-size: 0.9rem; color: #666;">
                                <strong>Team Efficiency</strong>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem;">Better than last week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Task Modal -->
    <div id="quickTaskModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>➕ Quick Task</h3>
                <button class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <form method="POST" action="tasks.php" class="ajax-form">
                <input type="hidden" name="action" value="create_task">
                
                <div class="form-group">
                    <label class="form-label" for="quick_task_title">Task Title</label>
                    <input type="text" class="form-control" id="quick_task_title" name="title" required placeholder="What needs to be done?">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="quick_task_project">Project</label>
                    <select class="form-control" id="quick_task_project" name="project_id" required>
                        <option value="">Select Project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="quick_task_priority">Priority</label>
                        <select class="form-control" id="quick_task_priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="quick_task_due_date">Due Date</label>
                        <input type="date" class="form-control" id="quick_task_due_date" name="due_date">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" class="btn modal-close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
    // Enhanced dashboard interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress rings on scroll
        const progressRings = document.querySelectorAll('.ring-progress');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const ring = entry.target;
                    const circumference = parseInt(ring.getAttribute('stroke-dasharray'));
                    const percent = parseInt(ring.parentElement.querySelector('.ring-text').textContent);
                    ring.style.strokeDashoffset = circumference - (circumference * percent / 100);
                }
            });
        }, { threshold: 0.5 });

        progressRings.forEach(ring => observer.observe(ring));

        // Add hover effects to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Check for overdue tasks and show notification
        const overdueTasks = document.querySelectorAll('.task-item.priority-high');
        if (overdueTasks.length > 0) {
            setTimeout(() => {
                const notification = document.createElement('div');
                notification.className = 'notification notification-warning';
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    padding: 1rem;
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    border-left: 4px solid #e74c3c;
                    z-index: 10000;
                    animation: slideInRight 0.3s ease;
                `;
                notification.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class='bx bx-error' style="color: #e74c3c;"></i>
                        <span>You have ${overdueTasks.length} overdue high priority tasks!</span>
                        <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; margin-left: auto;">×</button>
                    </div>
                `;
                document.body.appendChild(notification);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 5000);
            }, 1000);
        }
    });

    // Add slideInRight animation if not exists
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>