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
                    showAlertAndRedirect(data.success, 'login.php');
                } else {
                    customAlert(data.error);
                }
            })
            .catch(() => {
                customAlert('Помилка при зміні пароля. Спробуйте пізніше.');
            });
        });
    }
});
