document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');
    const resetMessage = document.getElementById('resetMessage');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            resetMessage.textContent = '';
            const formData = new FormData(resetForm);
            fetch('api/reset_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resetMessage.style.color = 'green';
                    resetMessage.textContent = data.success;
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    resetMessage.style.color = 'red';
                    resetMessage.textContent = data.error;
                }
            })
            .catch(() => {
                resetMessage.style.color = 'red';
                resetMessage.textContent = 'Помилка при зміні пароля. Спробуйте пізніше.';
            });
        });
    }
});
