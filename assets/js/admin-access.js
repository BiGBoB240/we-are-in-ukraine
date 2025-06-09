document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('admin-login-form');
    const verifyForm = document.getElementById('admin-verify-form');
    const loginSection = document.getElementById('admin-login-section');
    const verifySection = document.getElementById('admin-verify-section');
    const resultMsg = document.getElementById('admin-access-result');
    let adminId = null;

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const login = loginForm.login.value.trim();
            const password = loginForm.password.value;
            fetch('api/admin_access.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({action: 'login', login, password})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
    adminId = data.admin_id;
    loginSection.style.display = 'none';
    verifySection.style.display = 'block';
    customAlert('Лист був надісланий на вашу пошту!');
} else {
    customAlert(data.error || 'Помилка');
}
            })
            .catch(() => { customAlert('Помилка з’єднання з сервером.'); });
        });
    }

    if (verifyForm) {
    verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const code = verifyForm.verification_code.value.trim();
        fetch('api/admin_access.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action: 'verify', admin_id: adminId, code})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                verifySection.style.display = 'none';
                document.getElementById('admin-panel-section').style.display = 'block';
                // Показати посилання для зміни паролю
                var pwdLink = document.getElementById('openPasswordChangeModal');
                if (pwdLink) pwdLink.style.display = '';
                // Підключити логіку модального вікна
                setupPasswordChangeModal();
                loadAdminPanel();
                customAlert(data.success);
            } else {
                customAlert(data.error || 'Помилка');
            }
        })
        .catch(() => {
            customAlert('Помилка з’єднання з сервером.');
        });
    });
}

// --- Password change modal logic ---
function setupPasswordChangeModal() {
    var modal = document.getElementById('passwordChangeModal');
    var openBtn = document.getElementById('openPasswordChangeModal');
    if (!modal || !openBtn) return;
    var closeBtn = modal.querySelector('.modal-close');
    openBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'block';
    });
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    window.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';
    });

    // --- AJAX зміна паролю ---
    var form = document.getElementById('passwordChangeForm');
    if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(form);
        fd.append('action', 'change_password');
        fetch('api/admin_access.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlertAfterReload(data.success);
            } else {
                customAlert(data.error || 'Помилка');
            }
        })
        .catch(() => {
            customAlert('Помилка з’єднання з сервером.');
        });
    });
}
}


// --- Admin Panel Logic ---
function loadAdminPanel() {
    let users = [], admins = [];
    const usersTable = document.getElementById('users-table');
    const adminsTable = document.getElementById('admins-table');
    const searchInput = document.getElementById('user-search-input');
    const addForm = document.getElementById('add-admin-form');
    const removeForm = document.getElementById('remove-admin-form');

    function renderTables(filter = '') {
        // users
        let filteredusers = users.filter(u => u.username.toLowerCase().includes(filter.toLowerCase()));
        usersTable.innerHTML = '<tr><th>ID</th><th>Username</th><th>Email</th></tr>' +
            filteredusers.map(u => `<tr><td>${u.id}</td><td>${u.username}</td><td>${u.email}</td></tr>`).join('');
        // Admins
        let filteredAdmins = admins.filter(u => u.username.toLowerCase().includes(filter.toLowerCase()));
        adminsTable.innerHTML = '<tr><th>ID</th><th>Username</th><th>Email</th></tr>' +
            filteredAdmins.map(u => `<tr><td>${u.id}</td><td>${u.username}</td><td>${u.email}</td></tr>`).join('');
    }

    function loadusers() {
        fetch('api/admin_access.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'get_users'})
        })
        .then(r=>r.json())
        .then(data=>{ users = data.users || []; renderTables(searchInput.value); });
    }
    function loadAdmins() {
        fetch('api/admin_access.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'get_admins'})
        })
        .then(r=>r.json())
        .then(data=>{ admins = data.admins || []; renderTables(searchInput.value); });
    }

    // Фільтр
    searchInput.addEventListener('input', function() {
        renderTables(this.value);
    });

    // Додати адміна
    addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = addForm.user_id.value;
        fetch('api/admin_access.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'add_admin', user_id:id})
        })
        .then(r=>r.json())
        .then(data=>{
            customAlert(data.success || data.error || 'Помилка');
            loadAdmins();
        });
    });
    // Видалити адміна
    removeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = removeForm.user_id.value;
        fetch('api/admin_access.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'remove_admin', user_id:id})
        })
        .then(r=>r.json())
        .then(data=>{
            customAlert(data.success || data.error || 'Помилка');
            loadAdmins();
        });
    });

    // Початкове завантаження
    loadusers();
    loadAdmins();
}

});
