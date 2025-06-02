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
});
