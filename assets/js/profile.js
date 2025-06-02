document.addEventListener('DOMContentLoaded', function() {
    // === Notifications Bell Logic ===
    const bellBtn = document.getElementById('notification-bell');
    const notifModal = document.getElementById('notifications-modal');
    const notifClose = document.getElementById('notifications-modal-close');
    const notifDot = document.getElementById('notification-dot');
    const notifList = document.getElementById('notifications-list');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    const deleteAllBtn = document.getElementById('delete-all-btn');

    // Показываем/скрываем модалку уведомлений
    if (bellBtn && notifModal) {
        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifModal.style.display = 'block';
            loadNotifications();
        });
    }
    if (notifClose) {
        notifClose.addEventListener('click', function() {
            notifModal.style.display = 'none';
        });
    }
    notifModal && notifModal.addEventListener('mousedown', function(event) {
        if (event.target === notifModal) {
            notifModal.style.display = 'none';
            updateNotifDot();
        }
    });

    // Загрузка уведомлений и отображение красной точки
    function loadNotifications() {
        fetch('api/notifications.php?action=get')
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                const notifList = document.getElementById('notifications-list');
                if (!notifList) {
                    console.error('Element #notifications-list not found!');
                    return;
                }
                notifList.innerHTML = '';
                let hasUnread = false;
                const markAllReadBtn = document.getElementById('mark-all-read-btn');
                const deleteAllBtn = document.getElementById('delete-all-btn');
                if (data.notifications.length === 0) {
                    notifList.innerHTML = '<div class="modal-empty">Немає нових повідомлень</div>';
                    if (markAllReadBtn) markAllReadBtn.style.display = 'none';
                    if (deleteAllBtn) deleteAllBtn.style.display = 'none';
                } else {
                    if (markAllReadBtn) markAllReadBtn.style.display = '';
                    if (deleteAllBtn) deleteAllBtn.style.display = '';
                    const list = document.createElement('div');
                    list.className = 'modal-list';
                    data.notifications.forEach(n => {
                        const item = document.createElement('div');
                        item.className = 'modal-list-item' + (n.is_read == 0 ? ' unread' : ' read');
                        item.innerHTML =
    `<div class='notif-author'>${n.sender_username || 'Користувач'}</div>` +
    `<div class='notif-text'>${n.comment_text || ''}</div>` +
    `<div class='notif-status'>${n.is_read == 0 ? 'Не прочитано' : 'Прочитано'}</div>` +
    `<button class='modal-btn buttons-style-one mark-read-btn' data-notif-id='${n.id}' style='${n.is_read == 0 ? '' : 'display:none;'}'>Позначити як прочитане</button>` +
    `<button class='modal-btn buttons-style-one buttons-style-two delete-notif-btn' data-notif-id='${n.id}' style='${n.is_read == 1 ? '' : 'display:none;'}'>Видалити</button>` +
    `<button class='modal-btn buttons-style-one reply-notif-btn' data-comment-id='${n.comment_id}' data-post-id='${n.post_id}' data-username='${n.sender_username}'>Відповісти користувачу</button>`;
                        list.appendChild(item);
                        if (n.is_read == 0) hasUnread = true;
                    });
                    notifList.appendChild(list);
                }
                updateNotifDot();
            });
    }

    // Функція для оновлення кружечка
    function updateNotifDot() {
        // Якщо є хоча б одне .modal-list-item.unread — показуємо, інакше ховаємо
        const hasUnread = notifList.querySelector('.modal-list-item.unread');
        if (notifDot) notifDot.style.display = hasUnread ? 'inline-block' : 'none';
    }

    // Инициализация точки при загрузке страницы
    if (bellBtn && notifDot) {
        loadNotifications();
    }
    // Кнопка "Видалити все"

    

    // Кнопка "Прочитати все"
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            customConfirm('Відзначити всі повідомлення як прочитані?').then(confirmed => {
                if (confirmed) {
                    fetch('api/notifications.php?action=mark_all_read', {method: 'POST'})
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadNotifications();
                                customAlert('Всі повідомлення відзначено як прочитані!');
                            } else {
                                customAlert('Помилка при оновленні повідомлень');
                            }
                        });
                }
            });
        });
    }

    // Кнопка "Видалити все"
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function() {
            customConfirm('Видалити всі повідомлення?').then(confirmed => {
                if (confirmed) {
                    fetch('api/notifications.php?action=delete_all', {method: 'POST'})
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadNotifications();
                                customAlert('Всі повідомлення видалено!');
                            } else {
                                customAlert('Помилка при видаленні повідомлень');
                            }
                        });
                }
            });
        });
    }
    // Делегирование для кнопок "Позначити як прочитане"
    notifList.addEventListener('click', function(e) {
        // Ответ пользователю
        if (e.target && e.target.matches('.reply-notif-btn')) {
            const btn = e.target;
            const commentId = btn.getAttribute('data-comment-id');
            const postId = btn.getAttribute('data-post-id');
            // Открываем index.php с нужным якорем и reply_to
            if (postId && commentId) {
                window.location.href = `index.php?post_id=${postId}&reply_to=${commentId}`;
            } else {
                customAlert('Не вдалося перейти до відповіді');
            }
            return;
        }
        if (e.target && e.target.matches('.mark-read-btn')) {
    const btn = e.target;
    const notifId = btn.getAttribute('data-notif-id');
    const notifItem = btn.closest('.modal-list-item');
    fetch('api/notifications.php?action=mark_one_read', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: notifId})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (notifItem) {
                notifItem.classList.remove('unread');
                notifItem.classList.add('read');
                const status = notifItem.querySelector('.notif-status');
                if (status) status.textContent = 'Прочитано';
                btn.style.display = 'none';
                const deleteBtn = notifItem.querySelector('.delete-notif-btn');
                if (deleteBtn) deleteBtn.style.display = '';
            }
            updateNotifDot();
        } else {
            customAlert('Не вдалося позначити як прочитане');
        }
    });
}
        // Удаление одного уведомления
        if (e.target && e.target.matches('.delete-notif-btn')) {
            const btn = e.target;
            const notifId = btn.getAttribute('data-notif-id');
            const notifItem = btn.closest('.modal-list-item');
            customConfirm('Видалити це повідомлення?').then(confirmed => {
                if (confirmed) {
                    fetch('api/notifications.php?action=delete_one', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id: notifId})
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (notifItem) notifItem.remove();
                            updateNotifDot();
                        } else {
                            customAlert('Не вдалося видалити повідомлення');
                        }
                    });
                }
            });
        }
    });
    // === END Notifications ===

    // Перенос обработчика жалобы на профиль из profile.php
    var reportBtn = document.getElementById('report-profile-btn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            customConfirm('Поскаржитись на цей профіль?').then(function(confirmed) {
                if (confirmed) {
                    fetch('api/report.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            content_id: window.profileUserId || null,
                            content_type: 'user'
                        })
                    })
                    .then(res => res.json())
                    .then(data => customAlert(data.success || data.error))
                    .catch(() => customAlert('Помилка при надсиланні скарги'));
                }
            });
        });
    }
    // Modal functionality
    const nameModal = document.getElementById('nameChangeModal');
    const passwordModal = document.getElementById('passwordChangeModal');
    const closeBtns = document.getElementsByClassName('modal-close');

    // Close modals when clicking (x)
    Array.from(closeBtns).forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (nameModal) nameModal.style.display = 'none';
            if (passwordModal) passwordModal.style.display = 'none';
        });
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == nameModal) {
            nameModal.style.display = 'none';
        }
        if (event.target == passwordModal) {
            passwordModal.style.display = 'none';
        }
    }

    // Load comments with default filter
    loadComments('date-new');

    // Add click handlers for filter buttons
    const filterIds = ['filter-date-new', 'filter-date-old', 'filter-rating-high', 'filter-rating-low'];
    const filterBtns = filterIds.map(id => document.getElementById(id));
    
    filterBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                loadComments(filter);
                
                // Update active button
                filterBtns.forEach(b => {
                    if (b) b.classList.remove('active');
                });
                this.classList.add('active');
            });
        }
    });

    // Handle name change form
    const nameChangeForm = document.getElementById('nameChangeForm');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');

    function validateNameInput(input) {
        if (input && input.value.includes(' ')) {
            input.setCustomValidity("Ім'я та прізвище не повинні містити пробілів.");
            input.reportValidity();
            return false;
        }
        if (input) input.setCustomValidity('');
        return true;
    }

    if (firstNameInput) {
        firstNameInput.addEventListener('input', () => validateNameInput(firstNameInput));
    }
    if (lastNameInput) {
        lastNameInput.addEventListener('input', () => validateNameInput(lastNameInput));
    }

    if (nameChangeForm) {
        nameChangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate names before submission
            if ((firstNameInput && !validateNameInput(firstNameInput)) || 
                (lastNameInput && lastNameInput.value && !validateNameInput(lastNameInput))) {
                return;
            }
            
            const formData = new FormData(nameChangeForm);
            if (window.profileUserIdForAdmin !== undefined) {
                formData.append('user_id', window.profileUserIdForAdmin);
            }
            fetch('api/update_profile.php', {
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
            });
        });
    }

    // Handle password change form
    const passwordChangeForm = document.getElementById('passwordChangeForm');
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(passwordChangeForm);
            fetch('api/change_password.php', {
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
            });
        });
    }
});

function cancelEdit(commentId) {
    const commentText = document.getElementById(`comment-text-${commentId}`);
    const editForm = document.getElementById(`edit-form-${commentId}`);
    
    if (commentText && editForm) {
        commentText.style.display = 'block';
        editForm.style.display = 'none';
    }
}

// Functions to open modals
function openNameChangeModal() {
    document.getElementById('nameChangeModal').style.display = 'block';
}

function openPasswordChangeModal() {
    document.getElementById('passwordChangeModal').style.display = 'block';
}

// Function to delete a user profile (for admin)
function deleteUserProfile(userId, username, email) {
    customConfirmWithCheckbox(
        'Ви впевнені, що хочете видалити цей профіль? Ця дія незворотна!',
        'Заблокувати email користувача?'
    ).then(function(result){
        if (result.confirmed) {
            fetch('api/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    block_user: result.checked,
                    username: username,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertOnIndex('Профіль успішно видалений');
                } else {
                    customAlert(data.error || 'Помилка при видаленні профілю');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                customAlert('Помилка при видаленні профілю');
            });
        }
    });
}




// Load user comments
function loadComments(filter = 'date-new') {
    const commentsContainer = document.getElementById('comments-container');
    // Get user ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        commentsContainer.innerHTML = '<p class="error">Помилка: ID користувача не вказано</p>';
        return;
    }

    fetch(`api/get_user_comments.php?filter=${filter}&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            commentsContainer.innerHTML = '';
            
            if (data.error) {
                commentsContainer.innerHTML = `<p class="error">${data.error}</p>`;
                return;
            }

            if (!data.comments || data.comments.length === 0) {
                commentsContainer.innerHTML = '<p class="existed-comments">Користувач ще не залишав коментарі</p>';
                return;
            }

            data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.dataset.commentId = comment.id;
                
                const commentContent = `
                    <p class="comment-text" id="comment-text-${comment.id}">${comment.comment_text}</p>
                    <p class="comment-meta">
                        <span>До посту: ${comment.post_title}</span>
                        <span>Дата: ${comment.created_at}</span>
                        ${comment.is_edited ? '<span>(відредаговано)</span>' : ''}
                        <button class="like-button ${comment.has_liked ? 'liked' : ''}" onclick="toggleCommentLike(${comment.id}, this)">
                            <span class="like-icon">❤️</span>
                            <span class="likes-count">${comment.likes_count}</span>
                        </button>
                        ${(window.isLoggedIn && window.currentUserId !== comment.user_id) ? `<button class="report-btn" title="Поскаржитись на коментар" onclick="reportComment(${comment.id})">Поскаржитись</button>` : ''}
                    </p>
                `;

                const editForm = `
                    <div class="edit-form" id="edit-form-${comment.id}" style="display: none;">
                        <textarea class="edit-textarea">${comment.comment_text}</textarea>
                        <div class="edit-buttons">
                            <button class="buttons-style-one" onclick="saveCommentprofile(${comment.id})">Зберегти</button>
                            <button class="buttons-style-one" onclick="cancelEdit(${comment.id})">Скасувати</button>
                        </div>
                    </div>
                `;

                const actionButtons = comment.can_edit || comment.can_delete ? `
                    <div class="comment-actions">
                        ${comment.can_edit ? `<button class="buttons-style-one" onclick="editComment(${comment.id})">Редагувати</button>` : ''}
                        ${comment.can_delete ? `<button class="buttons-style-one buttons-style-two" onclick="deleteComment(${comment.id})">Видалити</button>` : ''}
                    </div>
                ` : '';

                commentDiv.innerHTML = commentContent + editForm + actionButtons;
                commentsContainer.appendChild(commentDiv);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            commentsContainer.innerHTML = '<p class="error">Помилка при завантаженні коментарів</p>';
        });
}




// Save edited comment
function saveCommentprofile(commentId) {
    const editForm = document.getElementById(`edit-form-${commentId}`);
    const textarea = editForm.querySelector('.edit-textarea');
    const newText = textarea.value.trim();

    if (!newText) {
        customAlert('Коментар не може бути порожнім');
        return;
    }

    if (newText.length > 300) {
        customAlert('Коментар не може бути довшим за 300 символів');
        return;
    }

    fetch('api/update_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            comment_id: commentId,
            text: newText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            customAlert(data.error || 'Помилка при оновленні коментаря');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        customAlert('Помилка при оновленні коментаря');
    });
}

// Toggle comment like
window.toggleCommentLike = function(commentId, button) {
    fetch('api/toggle_comment_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            comment_id: commentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likesCount = button.querySelector('.likes-count');
            likesCount.textContent = data.likes_count;
            
            if (data.action === 'liked') {
                button.classList.add('liked');
            } else {
                button.classList.remove('liked');
            }
        } else {
            customAlert(data.error || 'Помилка при оновленні лайку');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        customAlert('Помилка при оновленні лайку');
    });
};


