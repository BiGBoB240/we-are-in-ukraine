document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');

    function validateNameInput(input) {
        if (input.value.includes(' ')) {
            input.setCustomValidity("Ім'я та прізвище не повинні містити пробілів.");
            input.reportValidity();
            return false;
        }
        input.setCustomValidity('');
        return true;
    }

    if (firstNameInput) {
        firstNameInput.addEventListener('input', () => validateNameInput(firstNameInput));
    }
    if (lastNameInput) {
        lastNameInput.addEventListener('input', () => validateNameInput(lastNameInput));
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate names before submission
            if ((firstNameInput && !validateNameInput(firstNameInput)) || 
                (lastNameInput && lastNameInput.value && !validateNameInput(lastNameInput))) {
                return;
            }

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
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'ЗАРЕЄСТРУВАТИСЯ';
                    }
                }
            })
            .catch(() => {
                customAlert('Помилка при реєстрації. Спробуйте пізніше.');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'ЗАРЕЄСТРУВАТИСЯ';
                }
            });
        });
    }
});
