// admin.js — логика для кнопок администратора и модального окна добавления поста

document.addEventListener('DOMContentLoaded', function() {
    // Проверяем глобальную переменную window.isAdmin, которую выставит index.php
    if (!window.isAdmin) return;

    // Добавляем админ кнопки в filter-buttons
    const container = document.querySelector('.posts-header');
    if (!container) return;
    const filterButtons = container.querySelector('.filter-buttons');
    if (!filterButtons) return;

    // Добавляем админ кнопки в filter-buttons
    const adminAddPostBtn = document.createElement('button');
    adminAddPostBtn.id = 'admin-add-post';
    adminAddPostBtn.className = 'buttons-style-one';
    adminAddPostBtn.textContent = 'ДОДАТИ ПОСТ';

    const adminReportsBtn = document.createElement('button');
    adminReportsBtn.id = 'admin-reports-btn';
    adminReportsBtn.className = 'buttons-style-one';
    adminReportsBtn.textContent = 'СКАРГИ';

    // Добавляем админ кнопки в начало filter-buttons
    filterButtons.insertBefore(adminAddPostBtn, filterButtons.firstChild);
    filterButtons.insertBefore(adminReportsBtn, filterButtons.firstChild);

    // Модальное окно для добавления поста
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'none';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Додати пост</h2>
            <form id="add-post-form" enctype="multipart/form-data">
            <div id="edit-images-block">
                <input type="text" name="title" placeholder="Назва" required class="modal-add-post-input"><br>
                <textarea name="content" placeholder="Введіть текст поста..." required></textarea><br>
                <div class="image-preview-container">
                    <img id="preview-image1">
                    <label class="buttons-style-one">
                        <input type="file" name="image1" accept="image/*" class="modal-add-image-btn" style="display:none;">
                        <span id="custom-btn-1">Обрати фото</span>
                    </label>
                    <div class="file-name" id="file-name-1">Фото не було обране</div>
                    <button class="buttons-style-one buttons-style-two" type="button" id="remove-image1" style="display:none; margin-left:10px;">Видалити</button>
                </div>
                <div class="image-preview-container">
                    <img id="preview-image2">
                    <label class="buttons-style-one">
                        <input type="file" name="image2" accept="image/*" class="modal-add-image-btn" style="display:none;">
                        <span id="custom-btn-2">Обрати фото</span>
                    </label>
                    <div class="file-name" id="file-name-2">Фото не було обране</div>
                    <button class="buttons-style-one buttons-style-two" type="button" id="remove-image2" style="display:none; margin-left:10px;">Видалити</button>
                </div>
                <div class="image-preview-container">
                    <img id="preview-image3">
                    <label class="buttons-style-one">
                        <input type="file" name="image3" accept="image/*" class="modal-add-image-btn" style="display:none;">
                        <span id="custom-btn-3">Обрати фото</span>
                    </label>
                    <div class="file-name" id="file-name-3">Фото не було обране</div>
                    <button class="buttons-style-one buttons-style-two" type="button" id="remove-image3" style="display:none; margin-left:10px;">Видалити</button>
                </div>
                <button class="buttons-style-one" type="submit">Опублікувати</button>
            </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);

    // Модалка для скарг
    const reportsModal = document.createElement('div');
    reportsModal.className = 'modal';
    reportsModal.style.display = 'none';
    reportsModal.innerHTML = `
        <div class="modal-content  modal-reports" style="max-width:700px;">
            <span class="modal-close" id="close-reports-modal">&times;</span>
            <h2>Скарги</h2>
            <div id="reports-list">Завантаження...</div>
        </div>
    `;
    document.body.appendChild(reportsModal);

    // Открытие модального окна
    document.getElementById('admin-add-post').onclick = () => {
        modal.style.display = 'block';
        // Додаємо прев’ю для кожного input[type=file]
        const files = modal.querySelectorAll('.modal-add-image-btn');
        files.forEach((input, idx) => {
    const i = idx + 1;
    const preview = modal.querySelector(`#preview-image${i}`);
    const removeBtn = modal.querySelector(`#remove-image${i}`);
    const fileNameField = modal.querySelector(`#file-name-${i}`);
    const customBtn = modal.querySelector(`#custom-btn-${i}`);

    // Clicking the custom button triggers file input
    if (customBtn) {
        customBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            input.click();
            return false;
        };
    }

    input.onchange = function() {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                removeBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
            if (fileNameField) fileNameField.textContent = file.name;
        } else {
            preview.src = '';
            preview.style.display = 'none';
            removeBtn.style.display = 'none';
            if (fileNameField) fileNameField.textContent = 'Фото не було обране';
        }
    };
    removeBtn.onclick = function() {
        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        removeBtn.style.display = 'none';
        if (fileNameField) fileNameField.textContent = 'Фото не було обране';
    };
});
    };


    // Функція для завантаження скарг
    function loadReports() {
        const reportsList = document.getElementById('reports-list');
        reportsList.innerHTML = 'Завантаження...';

        fetch('/we-are-in-ukraine/api/get_reports.php')
            .then(response => response.json())
            .then(data => {
                if (!data.reports || data.reports.length === 0) {
                    reportsList.innerHTML = 'Скарг немає.';
                    return;
                }
                reportsList.innerHTML = '';
                data.reports.forEach(report => {
                    let content = '';
                    if (report.type === 'post' && report.post) {
                        content = `
                            <div class="report-item">
                                <b>Пост:</b> ${report.post.title} <br>
                                <b>Дата створення:</b> ${report.post.created_at}<br>
                                <button class="buttons-style-one" onclick="openPost(${report.post.id})">Відкрити пост</button>
                            </div>
                        `;
                    }
                    if (report.type === 'user' && report.user) {
                        content = `
                            <div class="report-item">
                                <b>Користувач:</b> ${report.user.username} (ID: ${report.user.id})<br>
                                <button class="buttons-style-one" onclick="openUserProfile(${report.user.id})">Відкрити профіль</button>
                            </div>
                        `;
                    }
                    if (report.type === 'comment' && report.comment) {
                        const c = report.comment;
                        content = `
                            <div class="comment" data-comment-id="${c.id}">
                                <div class="comment-content">
                                    <strong>${c.username}</strong>
                                    <p class="comment-text" id="comment-text-${c.id}">${c.comment_text}</p>
                                    <small>${c.created_at}${c.is_edited ? ' (відредаговано)' : ''}</small>
                                    <button class="like-button ${c.has_liked ? 'liked' : ''}" onclick="toggleCommentLike(${c.id}, this)">
                                        <span class="like-icon">❤️</span>
                                        <span class="likes-count">${c.likes_count}</span>
                                    </button>
                                </div>
                                <div class="edit-form" id="edit-form-${c.id}" style="display: none;">
                                    <textarea class="edit-textarea">${c.comment_text}</textarea>
                                    <div class="edit-buttons">
                                        <button class="buttons-style-one" onclick="saveComment(${c.id}, true)">Зберегти</button>
                                        <button class="buttons-style-one" onclick="cancelEdit(${c.id})">Скасувати</button>
                                    </div>
                                </div>
                                ${(c.can_edit || c.can_delete) ? `
                                    <div class="comment-actions">
                                        ${c.can_edit ? `<button class="buttons-style-one" onclick="editComment(${c.id})">Редагувати</button>` : ''}
                                        ${c.can_delete ? `<button class="buttons-style-one buttons-style-two" onclick="adminDeleteComment(${c.id})">Видалити</button>` : ''}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }
                    // Додаємо кнопку закриття скарги з data-атрибутами
                    content += `<button class="close-report-btn buttons-style-one" data-content-id="${report[report.type]?.id || ''}" data-content-type="${report.type}">Закрити скаргу</button>`;
                    const div = document.createElement('div');
                    div.innerHTML = content;
                    reportsList.appendChild(div);
                });
            })
            .catch(err => {
                reportsList.innerHTML = 'Помилка при завантаженні скарг.';
                console.error(err);
            });
    }

    // --- Функції для відкриття поста та профілю з модалки скарг ---
    window.openPost = function(id) {
        reportsModal.style.display = 'none';
        if (typeof openPostModal === 'function') {
            openPostModal(id);
        } else if (window.openPostModal) {
            window.openPostModal(id);
        } else {
            customAlert('Функція openPostModal не знайдена');
        }
    };
    window.openUserProfile = function(id) {
        reportsModal.style.display = 'none';
        window.location.href = `profile.php?id=${id}`;
    };

    // Открытие модального окна скарг
    document.getElementById('admin-reports-btn').onclick = () => {
        reportsModal.style.display = 'block';
        loadReports();
    };

    // Закрытие модального окна
    modal.querySelector('.modal-close').onclick = () => {
        modal.style.display = 'none';
    };
    // Закрытие модального окна скарг
    reportsModal.querySelector('.modal-close').onclick = () => {
        reportsModal.style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target === modal) modal.style.display = 'none';
        if (event.target === reportsModal) reportsModal.style.display = 'none';
    };
    // Обработка формы
    document.getElementById('add-post-form').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('api/create_post.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            customAlert(result.error || 'Помилка!');
        }
    };

    // Делегування події на кнопку "Закрити скаргу"
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('close-report-btn')) {

            customConfirm('Ви впевнені, що хочете закрити цю скаргу?').then(function(confirmed){
                if (confirmed) {

            const btn = e.target;
            btn.disabled = true;
            btn.textContent = 'Обробка...';

            const contentId = btn.getAttribute('data-content-id');
            const contentType = btn.getAttribute('data-content-type');
            if (!contentId || !contentType) {
                customAlert('Не вдалося визначити елемент скарги.');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Обробка...';
            fetch('/we-are-in-ukraine/api/close_report.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `content_id=${encodeURIComponent(contentId)}&content_type=${encodeURIComponent(contentType)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    customAlert('Скаргу успішно закрито. Користувачі отримають повідомлення.');
                    loadReports(); // Оновити список скарг
                } else {
                    customAlert('Помилка при закритті скарги: ' + (data.error || ''));
                }
            })
            .catch(() => {
                customAlert('Помилка при з’єднанні з сервером.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Закрити скаргу';
            });
            }
        });
    }
});

    adminDeleteComment = function(commentId) {
        customConfirm('Ви впевнені, що хочете видалити цей коментар?').then(function(confirmed){
            if (confirmed) {
            fetch('api/delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ comment_id: commentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Оновлюємо список скарг
                    loadReports();
                } else {
                    customAlert(data.error || 'Помилка при видаленні коментаря');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                customAlert('Помилка при видаленні коментаря');
            });
        }
    });
}
});

