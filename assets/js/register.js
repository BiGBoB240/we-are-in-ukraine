document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const registerMessage = document.getElementById('registerMessage');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            registerMessage.textContent = '';
            const formData = new FormData(registerForm);
            fetch('api/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    registerMessage.style.color = 'green';
                    registerMessage.textContent = data.success;
                    registerForm.reset();
                } else {
                    registerMessage.style.color = 'red';
                    registerMessage.textContent = data.error;
                }
            })
            .catch(() => {
                registerMessage.style.color = 'red';
                registerMessage.textContent = 'Помилка при реєстрації. Спробуйте пізніше.';
            });
        });
    }
});
