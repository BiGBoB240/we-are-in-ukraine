document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Обробка...';
            }

            const formData = new FormData(registerForm);
            fetch('api/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertAfterReload(data.success);
                } else {
                    customAlert(data.error);
                }
            })
            .catch(() => {
                customAlert('Помилка при реєстрації. Спробуйте пізніше.');
            });
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'ЗАРЕЄСТРУВАТИСЯ';
            }
        });
    }
});
