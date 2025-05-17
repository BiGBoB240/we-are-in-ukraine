document.addEventListener('DOMContentLoaded', function() {
    const feedbackForm = document.getElementById('feedbackForm');
    const showFeedbacksBtn = document.getElementById('showFeedbacks');
    const feedbacksList = document.getElementById('feedbacksList');
    const phoneInput = document.getElementById('phone');
    
    // Add input validation for phone number
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    }

    // Handle feedback form submission
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/submit_feedback.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => { 
                if (data.success) {
                    customAlert('Дякуємо за ваше звернення! Ми зв\'яжемося з вами найближчим часом.');
                    feedbackForm.reset();
                } else {
                    customAlert(data.error || 'Помилка при відправці звернення. Спробуйте пізніше.');
                }
            })
            .catch(error => {
                customAlert('Помилка при відправці звернення. Спробуйте пізніше.');
            });
        });
    }

    // Admin functionality
    if (showFeedbacksBtn) {
        showFeedbacksBtn.addEventListener('click', function() {
            if (feedbacksList.style.display === 'none') {
                loadFeedbacks();
                feedbacksList.style.display = 'block';
                this.textContent = 'Сховати звернення';
            } else {
                feedbacksList.style.display = 'none';
                this.textContent = 'Показати звернення';
            }
        });
    }

    // Load feedbacks for admin
    function loadFeedbacks() {
        fetch('api/get_feedbacks.php')
            .then(response => response.json())
            .then(data => {
                feedbacksList.innerHTML = data.feedbacks.map(feedback => `
                    <div class="feedback-item" data-id="${feedback.id}">
                        <h3>${feedback.username}</h3>
                        <p><strong>Email:</strong> ${feedback.email}</p>
                        <p><strong>Телефон:</strong> ${feedback.phone_number}</p>
                        <p><strong>Звернення:</strong> ${feedback.feedback_text}</p>
                        <p><strong>Дата:</strong> ${feedback.created_at}</p>
                        <div class="feedback-actions">
                            <button class="buttons-style-one" onclick="resolveFeedback(${feedback.id})">Вирішено</button>
                        </div>
                    </div>
                `).join('') || '<p>Немає нових звернень</p>';
            });
    }
});

// Resolve feedback (for admin)
function resolveFeedback(feedbackId) {
    customConfirm('Ви впевнені, що хочете позначити це звернення як вирішене?').then(function(confirmed){
        if (confirmed) {
            const btn = document.querySelector(`.feedback-item[data-id="${feedbackId}"] .buttons-style-one`);
            btn.disabled = true;
            btn.textContent = 'Обробка...';
    fetch('api/resolve_feedback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: feedbackId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const feedbackItem = document.querySelector(`.feedback-item[data-id="${feedbackId}"]`);
            if (feedbackItem) {
                feedbackItem.remove();
            }
            customAlert('Звернення позначено як вирішене. Користувачу відправлено повідомлення.');
        } else {
            customAlert(data.error || 'Помилка при обробці звернення.');
        }
    })
    .catch(error => {
        customAlert('Помилка при обробці звернення.');
    });
}
});
}
