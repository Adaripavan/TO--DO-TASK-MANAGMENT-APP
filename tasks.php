<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "task_manager");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['task_name']) && !isset($_POST['task_id'])) {
        $task_name = $_POST['task_name'];
        $conn->query("INSERT INTO tasks (task_name, status) VALUES ('$task_name', 'Pending')");
        header("Location: tasks.php"); // Redirect to the tasks page to display the added task
        exit;
    }
}

// Handle task update (edit and status change)
if (isset($_POST['action'])) {
    $task_id = $_POST['task_id'];
    
    if ($_POST['action'] == 'edit') {
        $task_name = $_POST['task_name'];
        $conn->query("UPDATE tasks SET task_name='$task_name' WHERE id=$task_id");
    } elseif ($_POST['action'] == 'delete') {
        $conn->query("DELETE FROM tasks WHERE id=$task_id");
    } elseif ($_POST['action'] == 'change_status') {
        $new_status = $_POST['new_status'];
        $conn->query("UPDATE tasks SET status='$new_status' WHERE id=$task_id");
    }
}

// Fetch tasks for display
$tasks = $conn->query("SELECT * FROM tasks");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Task List</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f4;
            margin: 0;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        .task-list {
            margin-top: 20px;
            list-style: none;
            padding: 0;
            width: 100%;
            max-width: 600px;
        }
        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            margin: 5px 0;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .task-item.completed {
            background-color: #d4edda; /* Light green background for completed tasks */
        }
        .status-buttons {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .status-buttons .status-button {
            border: none;
            border-radius: 5px; /* Change to smaller radius for a different style */
            width: 100px; /* Increased width for full names */
            height: 35px; /* Increased height for better visibility */
            display: inline-block;
            text-align: center;
            color: white;
            cursor: pointer;
            font-size: 16px; /* Increased font size */
            line-height: 35px; /* Center text vertically */
            padding: 5px; /* Added padding */
            transition: background-color 0.3s; /* Smooth transition for hover effects */
        }
        .status-buttons .status-pending {
            background-color: black; /* Ensure the color is distinct */
        }
        .status-buttons .status-in-progress {
            background-color: blue;
        }
        .status-buttons .status-completed {
            background-color: green;
        }
        .edit-task {
            background-color: #6c757d; /* Grey color */
            color: white;
            padding: 8px 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
        }
        .delete-task {
            background-color: #dc3545; /* Red color */
            color: white;
            padding: 8px 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
        }
        .go-back {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        .go-back:hover {
            background-color: #0056b3;
        }

        /* Dialog box styling */
        .status-dropdown {
            display: none;
            position: absolute;
            background-color: #e6ffe6; /* Very light green background */
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            z-index: 1000;
        }
        .status-dropdown button {
            display: block;
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
        }
        .status-dropdown button:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>TODO TASK LIST</h1>
    <ul class="task-list" id="taskList">
        <?php while ($row = $tasks->fetch_assoc()) { ?>
            <li class="task-item <?php echo $row['status'] == 'Completed' ? 'completed' : ''; ?>" id="task-<?php echo $row['id']; ?>">
                <div>
                    <span><?php echo $row['task_name']; ?></span>
                </div>
                <div class="status-buttons">
                    <!-- Status button with full names -->
                    <button class="status-button <?php echo $row['status'] == 'Pending' ? 'status-pending' : ($row['status'] == 'In-Progress' ? 'status-in-progress' : 'status-completed'); ?>" onclick="toggleStatusDropdown(event, <?php echo $row['id']; ?>)">
                        <?php echo $row['status'] == 'Pending' ? 'Pending' : ($row['status'] == 'In-Progress' ? 'In Progress' : 'Completed'); ?>
                    </button>

                    <button class="edit-task" onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo $row['task_name']; ?>')">Edit</button>
                    <button class="delete-task" onclick="deleteTask(<?php echo $row['id']; ?>)">Delete</button>

                    <!-- Dropdown for changing status -->
                    <div id="status-dropdown-<?php echo $row['id']; ?>" class="status-dropdown">
                        <button onclick="changeTaskStatus(<?php echo $row['id']; ?>, 'Pending')">Pending</button>
                        <button onclick="changeTaskStatus(<?php echo $row['id']; ?>, 'In-Progress')">In Progress</button>
                        <button onclick="changeTaskStatus(<?php echo $row['id']; ?>, 'Completed')">Completed</button>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>

    <a href="index.php" class="go-back">Go Back</a>

    <script>
        function toggleStatusDropdown(event, taskId) {
            const dropdown = document.getElementById(`status-dropdown-${taskId}`);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            event.stopPropagation(); // Prevent click event from propagating to the document
        }

        function changeTaskStatus(taskId, newStatus) {
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'task_id': taskId,
                    'new_status': newStatus,
                    'action': 'change_status'
                })
            }).then(response => {
                return response.text();
            }).then(data => {
                // Update the task display here based on new status
                const taskItem = document.getElementById(`task-${taskId}`);
                const statusButton = taskItem.querySelector('.status-button');
                statusButton.textContent = newStatus === 'In-Progress' ? 'In Progress' : newStatus;

                // Update status button color
                statusButton.classList.remove('status-pending', 'status-in-progress', 'status-completed');
                if (newStatus === 'Pending') {
                    statusButton.classList.add('status-pending');
                } else if (newStatus === 'In-Progress') {
                    statusButton.classList.add('status-in-progress');
                } else if (newStatus === 'Completed') {
                    statusButton.classList.add('status-completed');
                }

                // Toggle status menu off
                const menu = document.getElementById(`status-dropdown-${taskId}`);
                menu.style.display = 'none';

                // Change background color for completed tasks
                taskItem.classList.remove('completed');
                if (newStatus === 'Completed') {
                    taskItem.classList.add('completed');
                }
            });
        }

        function deleteTask(taskId) {
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'task_id': taskId,
                    'action': 'delete'
                })
            }).then(response => {
                // Remove the task from the list
                const taskItem = document.getElementById(`task-${taskId}`);
                taskItem.remove();
            });
        }

        function showEditForm(taskId, currentTaskName) {
            const newTaskName = prompt("Edit Task Name:", currentTaskName);
            if (newTaskName) {
                fetch('tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'task_id': taskId,
                        'task_name': newTaskName,
                        'action': 'edit'
                    })
                }).then(response => {
                    // Update the task name in the list
                    const taskItem = document.getElementById(`task-${taskId}`);
                    taskItem.querySelector('span').textContent = newTaskName;
                });
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            const dropdowns = document.getElementsByClassName('status-dropdown');
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].style.display = 'none';
            }
        });
    </script>
</body>
</html>
