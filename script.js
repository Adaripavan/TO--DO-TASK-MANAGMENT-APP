document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('addTaskForm');
    const taskInput = document.getElementById('taskInput');
    const taskList = document.getElementById('taskList');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const taskName = taskInput.value;
        if (taskName) {
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=add&task_name=${taskName}`
            }).then(() => {
                location.reload();
            });
        }
    });

    taskList.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-task')) {
            const taskItem = e.target.closest('li');
            const taskId = taskItem.dataset.id;
            const newTaskName = prompt("Edit Task:", taskItem.querySelector('.task-name').textContent);
            if (newTaskName) {
                fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=edit&id=${taskId}&task_name=${newTaskName}`
                }).then(() => {
                    location.reload();
                });
            }
        }

        if (e.target.classList.contains('delete-task')) {
            const taskItem = e.target.closest('li');
            const taskId = taskItem.dataset.id;
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=delete&id=${taskId}`
            }).then(() => {
                location.reload();
            });
        }

        if (e.target.classList.contains('task-status')) {
            const taskItem = e.target.closest('li');
            const taskId = taskItem.dataset.id;
            const newStatus = e.target.value;
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update_status&id=${taskId}&status=${newStatus}`
            }).then(() => {
                location.reload();
            });
        }
    });
});
