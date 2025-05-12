document.addEventListener('DOMContentLoaded', function() {
    const forgotForm = document.getElementById('forgotForm');
    const forgotMessage = document.getElementById('forgotMessage');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            forgotMessage.textContent = '';
            const formData = new FormData(forgotForm);
            fetch('api/forgot_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    forgotMessage.style.color = 'green';
                    forgotMessage.textContent = data.success;
                    forgotForm.reset();
                } else {
                    forgotMessage.style.color = 'red';
                    forgotMessage.textContent = data.error;
                }
            })
            .catch(() => {
                forgotMessage.style.color = 'red';
                forgotMessage.textContent = 'Помилка при надсиланні листа. Спробуйте пізніше.';
            });
        });
    }
});