// Функція видалення поста
function deletePost(postId) {
    if (confirm('Ви впевнені, що хочете видалити цей пост?')) {
        fetch('api/delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Пост успішно видалено!');
                // Оновлюємо сторінку
                window.location.reload();
            } else {
                alert('Помилка при видаленні поста: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при видаленні поста');
        });
    }
}

// Модалка для редагування поста
window.editPostModal = function(post) {
    // Якщо вже існує модалка, видаляємо
    let editModal = document.getElementById('edit-post-modal');
    if (editModal) editModal.remove();

    // Створюємо модальне вікно
    editModal = document.createElement('div');
    editModal.className = 'modal';
    editModal.id = 'edit-post-modal';
    editModal.innerHTML = `
        <div class="modal-content">
            <button class="modal-close" id="close-edit-post-modal"></button>
            <h2>Редагувати пост</h2>
            <form id="edit-post-form">
                <input type="text" class="modal-add-post-input" name="title" value="${post.title.replace(/&/g, '&amp;').replace(/"/g, '&quot;')}" maxlength="255" required>
                <textarea name="content" maxlength="5000" required>${post.content ? post.content.replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''}</textarea>
                <div id="edit-images-block">
                    ${[0,1,2].map(i => `
                        <div class="image-preview-container">
                            <img id="edit-preview-image${i+1}" src="assets/upload/${post.images && post.images[i] ? post.images[i] : ''}" style="display:${post.images && post.images[i] ? 'block' : 'none'}">
                            <input type="file" name="image${i+1}" accept="image/*" class="modal-add-image-btn" id="edit-image-input${i+1}"><br>
                            <button type="button" class="buttons-style-one" id="edit-remove-image${i+1}" style="display:${post.images && post.images[i] ? 'inline-block' : 'none'}; margin-left:10px;">Видалити</button>
                        </div>
                    `).join('')}
                </div>
                <button class="buttons-style-one" type="submit">Зберегти зміни</button>
                <button class="buttons-style-one" type="button" data-post-id="${post.id}">Видалити пост</button>
            </form>
        </div>
    `;
    document.body.appendChild(editModal);
    editModal.style.display = 'block';

    // Закриття модалки
    document.getElementById('close-edit-post-modal').onclick = () => editModal.remove();
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) editModal.remove();
    });

    // --- Превʼю та видалення для 3-х картинок ---
    [1,2,3].forEach(function(i) {
        const input = document.getElementById('edit-image-input'+i);
        const preview = document.getElementById('edit-preview-image'+i);
        const removeBtn = document.getElementById('edit-remove-image'+i);
        // Превʼю
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
            } else {
                preview.src = '';
                preview.style.display = 'none';
                removeBtn.style.display = 'none';
            }
        };
        // Видалення
        removeBtn.onclick = function() {
            preview.src = '';
            preview.style.display = 'none';
            removeBtn.style.display = 'none';
            input.value = ''; // Очищаємо поле вводу
            
            // Додаємо приховане поле для видалення
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'remove_image' + i;
            hiddenInput.value = '1';
            input.parentNode.insertBefore(hiddenInput, input.nextSibling);
        
        };
    });

    // Обробка форми редагування
    // Додаємо обробник для кнопки видалення
    const deleteButton = document.querySelector('.modal-delete-post-btn');
    if (deleteButton) {
        deleteButton.onclick = function() {
            const postId = this.dataset.postId;
            deletePost(postId);
        };
    }

    document.getElementById('edit-post-form').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('post_id', post.id);
        formData.append('title', this.title.value);
        formData.append('content', this.content.value);
        // Картинки: якщо є файл — додаємо, якщо є src (старе) — додаємо у remaining_images
        let remainingImages = [];
        [1,2,3].forEach(function(i) {
            const input = document.getElementById('edit-image-input'+i);
            const preview = document.getElementById('edit-preview-image'+i);
            if (input.files && input.files[0]) {
                formData.append('image'+i, input.files[0]);
            } else if (preview.src && preview.style.display !== 'none') {
                // Якщо є старе зображення (url)
                remainingImages.push(preview.src);
            }
            // Додаємо приховане поле remove_imageX, якщо воно є у DOM
            const removeField = input.parentNode.querySelector('input[type="hidden"][name="remove_image'+i+'"]');
            if (removeField) {
                formData.append('remove_image'+i, '1');
            }
        });
        formData.append('remaining_images', JSON.stringify(remainingImages));
        fetch('api/update_post.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Пост оновлено!');
                editModal.remove();
                window.location.reload();
            } else {
                alert(data.error || 'Помилка при оновленні поста!');
            }
        })
        .catch(() => alert('Помилка при оновленні поста!'));
    };
};
