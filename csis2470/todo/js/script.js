// Global variables
let tasks = [];
let selectedPriority = 'med';
let draggedElement = null;

// Load tasks from storage on page load
window.addEventListener('DOMContentLoaded', () => {
    loadTasks();
    renderTasks();
    setupEventListeners();
});

// Setup all event listeners
function setupEventListeners() {
    // Add task button
    document.getElementById('addBtn').addEventListener('click', addTask);

    // Enter key to add task
    document.getElementById('taskInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            addTask();
        }
    });

    // Priority button selection
    document.querySelectorAll('.priority-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.priority-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedPriority = btn.dataset.priority;
        });
    });
}

// Add a new task
function addTask() {
    const input = document.getElementById('taskInput');
    const taskText = input.value.trim();

    // Validation
    if (taskText === '') {
        showMessage('Please enter a task!', 'error');
        return;
    }

    if (taskText.length > 200) {
        showMessage('Task is too long! Maximum 200 characters.', 'error');
        return;
    }

    // Create task object
    const task = {
        id: Date.now(),
        text: taskText,
        priority: selectedPriority,
        completed: false
    };

    tasks.push(task);
    saveTasks();
    renderTasks();

    input.value = '';
    showMessage('Item added', 'success');
}

// Toggle task completion status
function toggleTask(id) {
    const task = tasks.find(t => t.id === id);
    if (task) {
        task.completed = !task.completed;
        saveTasks();
        renderTasks();
        showMessage('Item marked as done', 'success');
    }
}

// Delete a task
function deleteTask(id) {
    tasks = tasks.filter(t => t.id !== id);
    saveTasks();
    renderTasks();
    showMessage('Item deleted', 'success');
}

// Render all tasks to the DOM
function renderTasks() {
    const taskList = document.getElementById('taskList');

    if (tasks.length === 0) {
        taskList.innerHTML = '<div class="empty-state">No tasks yet. Add one above!</div>';
        return;
    }

    // Sort tasks: uncompleted first, then completed
    const sortedTasks = [...tasks].sort((a, b) => {
        if (a.completed === b.completed) return 0;
        return a.completed ? 1 : -1;
    });

    const taskHTML = sortedTasks.map(task => `
        <div class="task-item ${task.priority} ${task.completed ? 'completed' : ''}" 
             data-id="${task.id}"
             draggable="${!task.completed}">
            <span class="drag-handle" ${task.completed ? 'style="opacity:0.3; cursor:not-allowed"' : ''}>⋮⋮</span>
            <input type="checkbox" 
                   class="task-checkbox" 
                   ${task.completed ? 'checked' : ''}
                   onchange="toggleTask(${task.id})">
            <div class="task-text">${escapeHtml(task.text)}</div>
            <span class="task-priority ${task.priority}">${task.priority}</span>
            <button class="delete-btn" onclick="deleteTask(${task.id})">Delete</button>
        </div>
    `).join('');

    taskList.innerHTML = taskHTML;

    // Setup drag and drop for uncompleted tasks
    setupDragAndDrop();
}

// Setup drag and drop functionality
function setupDragAndDrop() {
    const taskItems = document.querySelectorAll('.task-item:not(.completed)');

    taskItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragenter', handleDragEnter);
        item.addEventListener('dragleave', handleDragLeave);
    });
}

// Drag and drop event handlers
function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.task-item').forEach(item => {
        item.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    if (!this.classList.contains('completed')) {
        this.classList.add('drag-over');
    }
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }

    if (draggedElement !== this && !this.classList.contains('completed')) {
        const draggedId = parseInt(draggedElement.dataset.id);
        const droppedId = parseInt(this.dataset.id);

        const draggedIndex = tasks.findIndex(t => t.id === draggedId && !t.completed);
        const droppedIndex = tasks.findIndex(t => t.id === droppedId && !t.completed);

        if (draggedIndex !== -1 && droppedIndex !== -1) {
            // Get only uncompleted tasks
            const uncompletedTasks = tasks.filter(t => !t.completed);
            const completedTasks = tasks.filter(t => t.completed);

            // Reorder uncompleted tasks
            const [removed] = uncompletedTasks.splice(draggedIndex, 1);
            uncompletedTasks.splice(droppedIndex, 0, removed);

            // Combine back
            tasks = [...uncompletedTasks, ...completedTasks];

            saveTasks();
            renderTasks();
        }
    }

    return false;
}

// Save tasks to local storage
function saveTasks() {
    try {
        const tasksData = JSON.stringify(tasks);
        localStorage.setItem('todoTasks', tasksData);
    } catch (e) {
        console.error('Error saving tasks:', e);
        showMessage('Error saving tasks', 'error');
    }
}

// Load tasks from local storage
function loadTasks() {
    try {
        const tasksData = localStorage.getItem('todoTasks');
        if (tasksData) {
            tasks = JSON.parse(tasksData);
        }
    } catch (e) {
        console.error('Error loading tasks:', e);
        tasks = [];
    }
}

// Display a temporary message
function showMessage(text, type) {
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }

    const message = document.createElement('div');
    message.className = `message ${type}`;
    message.textContent = text;
    document.body.appendChild(message);

    setTimeout(() => message.classList.add('show'), 10);

    setTimeout(() => {
        message.classList.add('hiding');
        setTimeout(() => message.remove(), 300);
    }, 2000);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}