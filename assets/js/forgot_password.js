document.addEventListener('DOMContentLoaded', function() {
    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Обробка...';
            }

            const formData = new FormData(forgotForm);
            fetch('api/forgot_password.php', {
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
                customAlert('Помилка при надсиланні листа. Спробуйте пізніше.');
            });
        });
    }
});