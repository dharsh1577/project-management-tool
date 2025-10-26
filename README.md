# Project Management Tool

A complete web-based project management application built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### Core Features
- **User Authentication**: Register and login system
- **Project Management**: Create, read, update, delete projects
- **Task Management**: Create tasks within projects with status, priority, due dates
- **User Isolation**: Users can only access their own projects and tasks

### Bonus Features
- ✅ Task filtering by status and priority
- ✅ Search functionality across tasks
- ✅ Clean, responsive blue-themed UI with animations
- ✅ Task status updates without page reload
- ✅ Overdue task highlighting
- ✅ Project statistics and dashboard
- ✅ Modal-based forms for better UX

## Tech Stack

- **Backend**: PHP (Procedural with PDO)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL
- **Server**: XAMPP (Apache, MySQL)
- **Styling**: Custom CSS with blue theme and animations

## Installation & Setup

### Prerequisites
- XAMPP or similar local server stack
- MySQL database
- Web browser

### Step-by-Step Setup

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `project_management`
   - Import the `sql/schema.sql` file

3. **Project Files**
   - Place all project files in your XAMPP `htdocs` folder
   - Example: `C:\xampp\htdocs\project-management-tool\`

4. **Configuration**
   - Update database credentials in `includes/database.php` if needed
   - Default credentials (for XAMPP):
     - Host: `localhost`
     - Database: `project_management`
     - Username: `root`
     - Password: `''` (empty)

5. **Access the Application**
   - Open your browser and navigate to:
   - `http://localhost/project-management-tool/`

## Default Demo Account

- **Username**: `demo`
- **Password**: `password`

## File Structure
project-management-tool/
├── index.php # Home/Landing page
├── login.php # User login
├── register.php # User registration
├── dashboard.php # User dashboard with stats
├── projects.php # Project management
├── tasks.php # Task management
├── logout.php # Logout handler
├── includes/
│ ├── config.php # Configuration and session
│ ├── auth.php # Authentication functions
│ ├── database.php # Database connection
│ └── functions.php # Project and task functions
├── css/
│ └── style.css # All styling
├── js/
│ └── script.js # JavaScript functionality
└── sql/
└── schema.sql # Database schema


## Database Schema

### Users Table
- id, username, email, password, created_at

### Projects Table
- id, user_id, name, description, created_at, updated_at

### Tasks Table
- id, project_id, title, description, status, priority, due_date, created_at, updated_at

## Features in Detail

### Authentication
- Secure password hashing
- Session management
- Protected routes

### Projects
- Create new projects
- Edit existing projects
- Delete projects (with cascading task deletion)
- Project descriptions and timestamps

### Tasks
- Create tasks within projects
- Set task status (todo, in-progress, done)
- Set priority levels (low, medium, high)
- Due date management
- Task filtering and search
- Quick status updates

### UI/UX
- Responsive design
- Blue color theme
- Smooth animations
- Modal dialogs
- Loading states
- Alert messages
- Hover effects

## API Endpoints

All endpoints are handled via POST requests:

### Authentication
- `register.php` - User registration
- `login.php` - User login

### Projects
- `projects.php?action=create_project` - Create project
- `projects.php?action=update_project` - Update project
- `projects.php?action=delete_project` - Delete project

### Tasks
- `tasks.php?action=create_task` - Create task
- `tasks.php?action=update_task` - Update task
- `tasks.php?action=delete_task` - Delete task
- `tasks.php?action=update_status` - Update task status (AJAX)

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge

## Security Features

- Password hashing
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Session-based authentication
- User data isolation

## Future Enhancements

- File attachments
- Task comments
- User roles and permissions
- Email notifications
- Calendar view
- Gantt charts
- API for mobile apps

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP services are running
   - Verify database credentials in `database.php`

2. **Page Not Found**
   - Ensure files are in correct htdocs directory
   - Check Apache is running

3. **Login Not Working**
   - Verify database has demo user
   - Check password hashing

## License

This project is for educational and interview purposes.