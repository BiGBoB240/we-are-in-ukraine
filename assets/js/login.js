document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loginMessage.textContent = '';
            const formData = new FormData(loginForm);
            fetch('api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    loginMessage.style.color = 'red';
                    loginMessage.textContent = data.error;
                }
            })
            .catch(() => {
                loginMessage.style.color = 'red';
                loginMessage.textContent = 'Помилка при вході. Спробуйте пізніше.';
            });
        });
    }
});
