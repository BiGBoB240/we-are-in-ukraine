document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('search-results');
    const searchBar = document.getElementById('search');
    const postsContainer = document.getElementById('posts-container');
    let currentPage = 1;
    let currentFilter = 'date-new';

    let loadedPostIds = new Set(); // Для защиты от дубликатов

    // Create modal container
    const modalContainer = document.createElement('div');
    modalContainer.className = 'modal';
    modalContainer.innerHTML = `
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <div class="modal-body"></div>
        </div>
    `;
    document.body.appendChild(modalContainer);

    // Close modal on click outside or close button
    modalContainer.addEventListener('click', (e) => {
        if (e.target === modalContainer || e.target.classList.contains('modal-close')) {
            modalContainer.style.display = 'none';
        }
    });

    // Global variable to check if user is logged in
    let isLoggedIn = false;
    
    // Check login status
    fetch('api/check_login.php')
        .then(response => response.json())
        .then(data => {
            isLoggedIn = data.isLoggedIn;
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });
        
    // Search functionality
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length > 0) {
            searchTimeout = setTimeout(() => {
                fetch(`api/search_bar.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        searchResults.style.display = 'block';
                        searchBar.classList.add('no-bottom-radius');

                        data.forEach(item => {
                            if (item.type === 'post') {
                                const div = document.createElement('div');
                                div.className = 'search-result-item';
                                div.textContent = item.title;
                                div.addEventListener('click', () => {
                                    openPostModal(item.id);
                                });
                                searchResults.appendChild(div);
                            } else if (item.type === 'user') {
                                const div = document.createElement('div');
                                div.className = 'search-result-item user';
                                div.textContent = `👤 ${item.username}`;
                                div.addEventListener('click', () => {
                                    window.location.href = `profile.php?id=${item.id}`;
                                });
                                searchResults.appendChild(div);
                            }
                        });
                    });
            }, 300);
        } else {
            searchResults.style.display = 'none';
            searchBar.classList.remove('no-bottom-radius');
        }
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
            searchBar.classList.remove('no-bottom-radius');
        }
    });

    // Load posts
    function loadPosts() {
        return fetch(`api/posts.php?page=${currentPage}&filter=${currentFilter}`)
            .then(response => response.json())
            .then(data => {
                if (!data.posts || data.posts.length === 0) {
                    if (postsContainer.children.length === 0) {
                        postsContainer.innerHTML = '<div style="text-align:center;color:#888;margin:2rem;">Постів немає</div>';
                    }
                    return;
                }
                data.posts.forEach(post => {
                    if (loadedPostIds.has(post.id)) return; // Не добавлять дубликаты
                    loadedPostIds.add(post.id);
                    const postElement = createPostElement(post);
                    postsContainer.appendChild(postElement);
                });
            });
    }

    // Create post element
    function createPostElement(post) {
        const filteredImages = post.images ? post.images.filter(img => img) : [];
        const postDiv = document.createElement('div');
        postDiv.className = 'post';
        postDiv.dataset.postId = post.id;
        postDiv.innerHTML = `
            <h2>${post.title}</h2>
            ${filteredImages.length > 0 ? createImageSlider(filteredImages, false) : ''}
            ${post.content ? `<div class="post-content">${post.content}</div>` : ''}
            <div class="post-footer">
                <div class="rating">
                    <span>❤️ </span><span id="post-likes-${post.id}">${post.post_likes}</span>
                </div>
                <div class="date">${post.created_at}</div>
            </div>
        `;
        
        // Add click handlers for post and images
        postDiv.addEventListener('click', (e) => {
            if (!e.target.closest('.slider-prev') && !e.target.closest('.slider-next')) {
                openPostModal(post.id);
            }
        });

        // Initialize image slider if exists
        const slider = postDiv.querySelector('.image-slider');
        if (slider && filteredImages.length > 0) {
            initializeImageSlider(slider, filteredImages);
        }

        return postDiv;
    }

    // Create image slider
    function createImageSlider(images, isInModal = false) {
        if (images.length === 0) return '';
        return `
            <div class="image-slider">
                <img src="assets/upload/${images[0]}" alt="Post image" class="slider-main-img" data-is-modal="${isInModal}">
                ${images.length > 1 ? `
                    <button class="slider-prev" tabindex="-1">&lt;</button>
                    <button class="slider-next" tabindex="-1">&gt;</button>
                ` : ''}
            </div>
        `;
    }

    // Initialize image slider
    function initializeImageSlider(slider, images) {
        if (!slider) return;
        const mainImg = slider.querySelector('.slider-main-img');
        const prevBtn = slider.querySelector('.slider-prev');
        const nextBtn = slider.querySelector('.slider-next');
        let currentIndex = 0;

        function showImage(index) {
            mainImg.style.opacity = 0;
            setTimeout(() => {
                mainImg.src = `assets/upload/${images[index]}`;
                mainImg.style.opacity = 1;
            }, 200);
        }

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                showImage(currentIndex);
            });
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                currentIndex = (currentIndex + 1) % images.length;
                showImage(currentIndex);
            });
        }

        // Add click handler only for modal images
        if (mainImg.dataset.isModal === 'true') {
            mainImg.addEventListener('click', () => {
                window.open(mainImg.src, '_blank');
            });
        }

        // Инициализация
        showImage(currentIndex);
    }

    // Open post modal
    window.openPostModal = function(postId) {
        fetch(`api/post.php?id=${postId}`)
            .then(response => response.json())
            .then(post => {
                const filteredImages = post.images ? post.images.filter(img => img) : [];
                const modalBody = modalContainer.querySelector('.modal-body');
                modalBody.innerHTML = `
                    <h2>${post.title}</h2>
                    ${filteredImages.length > 0 ? createImageSlider(filteredImages, true) : ''}
                    ${post.content ? `<div class="post-content-box">${post.content}</div>` : ''}
                    ${isLoggedIn ? `
                        <div class="post-content-box">
                        ${window.isAdmin ? `<button class="buttons-style-one" id="modal-edit-post-btn" style="float:left; margin-right:10px;">Редагувати пост</button>` : ''}
                        <button class="like-button${post.has_liked ? ' liked' : ''}" onclick="togglePostLike(${post.id}, this)">
                            <span class="like-icon">❤️</span> <span class="likes-count">${post.post_likes}</span>
                        </button>
                        <button class="report-btn" id="modal-report-post-btn" onclick="reportPost(${post.id})" style="float:right;">Побачили помилку?</button>
                        </div>` : ''}
                    ${post.comments ? `
                        ${isLoggedIn || post.comments.length > 0 ? `
                        <div class="comment-section comment-section-box">
                            <h3>Коментарі</h3>` : ''}
                            ${isLoggedIn ? `
                                <form class="comment-form">
    <textarea placeholder="Додати коментар..." maxlength="300"></textarea>
    <div style="display: flex; gap: 8px; align-items: center;">
        <button class="buttons-style-one" type="submit">Відправити</button>
        <button class="buttons-style-one" type="button" id="cancel-reply-btn" style="display:none;">Скасувати відповідь</button>
    </div>
</form>` : ''}
                            ${post.comments.map(comment => `
                                <div class="comment" data-comment-id="${comment.id}">
                                    <div class="comment-content">
                                        <strong>${comment.username}</strong>
                                        <p class="comment-text" id="comment-text-${comment.id}">${comment.comment_text}</p>
                                        <small>${comment.created_at}${comment.is_edited ? ' (відредаговано)' : ''}</small>
                                        <button class="like-button ${comment.has_liked ? 'liked' : ''}" onclick="toggleCommentLike(${comment.id}, this)">
                                            <span class="like-icon">❤️</span>
                                            <span class="likes-count">${comment.likes_count}</span>
                                        </button>
                                        ${(isLoggedIn && window.currentUserId !== comment.user_id) ? `<button class="report-btn" title="Поскаржитись на коментар" onclick="reportComment(${comment.id})">Поскаржитись</button>` : ''}
                                        ${(isLoggedIn && window.currentUserId !== comment.user_id) ? `<button class="reply-btn" data-reply-username="${comment.username}" data-reply-id="${comment.id}">Відповісти користувачу</button>` : ''}
                                    </div>
                                    
                                    <div class="edit-form" id="edit-form-${comment.id}" style="display: none;">
                                        <textarea class="edit-textarea">${comment.comment_text}</textarea>
                                        <div class="edit-buttons">
                                            <button class="buttons-style-one" onclick="saveComment(${comment.id})">Зберегти</button>
                                            <button class="buttons-style-one" onclick="cancelEdit(${comment.id})">Скасувати</button>
                                        </div>
                                    </div>
                                    ${comment.can_edit || comment.can_delete ? `
                                        <div class="comment-actions">
                                            ${comment.can_edit ? `<button class="buttons-style-one" onclick="editComment(${comment.id})">Редагувати</button>` : ''}
                                            ${comment.can_delete ? `<button class="buttons-style-one buttons-style-two" onclick="deleteComment(${comment.id})">Видалити</button>` : ''}
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                `;
                
                // Initialize slider in modal if exists
                const modalSlider = modalBody.querySelector('.image-slider');
                if (modalSlider) {
                    initializeImageSlider(modalSlider, filteredImages);
                }

                modalContainer.style.display = 'block';

                // Добавить анимацию появления модалки
                setTimeout(() => {
                    modalContainer.querySelector('.modal-content').classList.add('post-animate-in');
                }, 10);
                modalContainer.querySelector('.modal-content').addEventListener('animationend', () => {
                    modalContainer.querySelector('.modal-content').classList.remove('post-animate-in');
                }, { once: true });

                // Кнопка скарги на пост в модалці
                const reportPostBtn = document.getElementById('modal-report-post-btn');
                if (reportPostBtn) {
                    reportPostBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        customConfirm('Повідомити про помилку у цьому пості?').then(function(confirmed){
                            if (confirmed) {
                            fetch('api/report.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ content_id: post.id, content_type: 'post' })
                            })
                            .then(res => res.json())
                            .then(data => customAlert(data.success || data.error))
                            .catch(() => customAlert('Помилка при надсиланні повідомлення'));
                        }
                    });
                });
                }

                // Кнопка "Редагувати пост" для адміна
                const editPostBtn = document.getElementById('modal-edit-post-btn');
                if (editPostBtn) {
                    editPostBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (typeof window.editPostModal === 'function') {
                            window.editPostModal(post);
                        } else {
                            customAlert('Не вдалося редагувати пост.');
                        }
                    });
                }
                // Глобальна функція для скарги на коментар (використовується у кнопці)
                window.reportComment = function(commentId) {
                    customConfirm('Поскаржитись на цей коментар?').then(function(confirmed){
                        if (confirmed) {
                        fetch('api/report.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ content_id: commentId, content_type: 'comment' })
                        })
                        .then(res => res.json())
                        .then(data => customAlert(data.success || data.error))
                        .catch(() => customAlert('Помилка при надсиланні скарги'));
                      }
                 });
                }
                
                // Handle reply-to-user logic
                let replyTo = null;
                let replyToUsername = '';
                const commentForm = modalBody.querySelector('.comment-form');
                if (commentForm) {
                    const textarea = commentForm.querySelector('textarea');
                    // Reply button logic
                    const replyBtns = modalBody.querySelectorAll('.reply-btn');
                    const cancelReplyBtn = modalBody.querySelector('#cancel-reply-btn');
                    replyBtns.forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            replyTo = this.getAttribute('data-reply-id');
                            replyToUsername = this.getAttribute('data-reply-username');
                            textarea.placeholder = `Відповідь користувачу ${replyToUsername}:`;
                            textarea.focus();
                            commentForm.classList.add('reply-mode');
                            if (cancelReplyBtn) cancelReplyBtn.style.display = '';
                        });
                    });
                    // Cancel reply logic
                    if (cancelReplyBtn) {
                        cancelReplyBtn.addEventListener('click', function() {
                            replyTo = null;
                            replyToUsername = '';
                            textarea.placeholder = 'Додати коментар...';
                            commentForm.classList.remove('reply-mode');
                            cancelReplyBtn.style.display = 'none';
                        });
                    }

                    // Submission logic
                    commentForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const text = textarea.value.trim();
                        if (!text) return;
                        let commentText = text;
                        let payload = { post_id: postId, comment_text: commentText };
                        if (replyTo && replyToUsername) {
                            commentText = `Відповідь користувачу ${replyToUsername}: ${text}`;
                            payload = { post_id: postId, comment_text: commentText, reply_to: replyTo };
                        }
                        fetch('api/create_comment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                openPostModal(postId);
                            } else {
                                customAlert("Ви повинні бути авторизовані" || 'Помилка під час додавання коментаря');
                            }
                        })
                        .catch(err => console.error('Comment error:', err));
                        // Reset reply mode
                        replyTo = null;
                        replyToUsername = '';
                        textarea.placeholder = 'Додати коментар...';
                        commentForm.classList.remove('reply-mode');
                        if (cancelReplyBtn) cancelReplyBtn.style.display = 'none';
                    });
                    // Reset reply mode on modal close
                    modalContainer.querySelector('.modal-close').addEventListener('click', function() {
                        replyTo = null;
                        replyToUsername = '';
                        textarea.placeholder = 'Додати коментар...';
                        commentForm.classList.remove('reply-mode');
                        if (cancelReplyBtn) cancelReplyBtn.style.display = 'none';
                    });
                }
            });
    }

        // Toggle post like
        window.togglePostLike = function(postId, button) {
            fetch('api/toggle_post_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем лайки в модальном окне
                    const likesCount = button.querySelector('.likes-count');
                    if (likesCount) {
                        likesCount.textContent = data.likes_count;
                    }
                    // Обновляем лайки на карточке поста в ленте
                    const cardLikes = document.getElementById(`post-likes-${postId}`);
                    if (cardLikes) {
                        cardLikes.textContent = data.likes_count;
                    }
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

    // Like functionality
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

    // Comment functions
    window.editComment = function(commentId) {
        const commentText = document.getElementById(`comment-text-${commentId}`);
        const editForm = document.getElementById(`edit-form-${commentId}`);
        
        if (commentText && editForm) {
            commentText.style.display = 'none';
            editForm.style.display = 'block';
        }
    };

    window.cancelEdit = function(commentId) {
        const commentText = document.getElementById(`comment-text-${commentId}`);
        const editForm = document.getElementById(`edit-form-${commentId}`);
        
        if (commentText && editForm) {
            commentText.style.display = 'block';
            editForm.style.display = 'none';
        }
    };

    window.saveComment = function(commentId, fromReports = false) {
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
                if (data.post_id && !fromReports) {
                    openPostModal(data.post_id);
                } else {
                    const commentText = document.getElementById(`comment-text-${commentId}`);
                    commentText.textContent = newText;
                    commentText.style.display = 'block';
                    editForm.style.display = 'none';
                }
            } else {
                customAlert(data.error || 'Помилка при оновленні коментаря');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            customAlert('Помилка при оновленні коментаря');
        });
    };

    window.deleteComment = function(commentId) {
        customConfirm('Ви впевнені, що хочете видалити цей коментар?').then(function(confirmed){
            if (confirmed) {

            fetch('api/delete_comment.php', {
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
                    // Если удаляем в модалке поста — удаляем только из modalBody
                    if (window.location.pathname.endsWith('profile.php')) {
                        // Профиль: удалять глобально
                        const commentDiv = document.querySelector(`[data-comment-id="${commentId}"]`);
                        if (commentDiv) commentDiv.remove();
                    } else {
                        // Пост: только из modalBody
                        const modalBody = modalContainer.querySelector('.modal-body');
                        if (modalBody) {
                            const commentDiv = modalBody.querySelector(`[data-comment-id="${commentId}"]`);
                            if (commentDiv) commentDiv.remove();
                        }
                    }
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
    };

    // Filter buttons
    document.querySelectorAll('#filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentFilter = this.dataset.filter;
            currentPage = 1;
            postsContainer.innerHTML = '';
            loadedPostIds = new Set();
            autoLoadPostsUntilScrollable();
        });
    });

    // Infinite scroll: подгружаем посты при достижении низа страницы
    let isLoadingPosts = false;
    window.addEventListener('scroll', function() {
        if (isLoadingPosts) return;
        // Проверяем, близко ли низ страницы
        if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
            isLoadingPosts = true;
            currentPage++;
            loadPosts().finally(() => {
                isLoadingPosts = false;
            });
        }
    });

    // Автоматическая подгрузка постов на больших экранах
    let autoLoadInProgress = false;

    // Initial load
    autoLoadPostsUntilScrollable();

    // Автодогрузка при изменении размера окна (например, пользователь уменьшил масштаб)
    window.addEventListener('resize', function() {
        // Если скролла нет и автодогрузка не идёт, запустить автодогрузку
        const enoughScroll = document.body.offsetHeight > window.innerHeight + 20;
        if (!enoughScroll && !autoLoadInProgress) {
            autoLoadPostsUntilScrollable();
        }
    });

    function autoLoadPostsUntilScrollable() {
        if (autoLoadInProgress) return; // не запускать параллельно
        autoLoadInProgress = true;
        let lastPostsCount = -1;
        let lastPage = currentPage;
        function tryLoad() {
            loadPosts().then(() => {
                // Если постов не прибавилось или скролл появился — прекращаем
                const enoughScroll = document.body.offsetHeight > window.innerHeight + 20;
                const postsCount = postsContainer.children.length;
                if (enoughScroll || postsCount === lastPostsCount) {
                    autoLoadInProgress = false;
                    return;
                }
                lastPostsCount = postsCount;
                // Если постов не было добавлено, значит посты закончились
                if (postsCount === 0) {
                    autoLoadInProgress = false;
                    return;
                }
                // Иначе пробуем ещё раз
                currentPage++;
                setTimeout(tryLoad, 50);
            });
        }
        tryLoad();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Create back to top button
    const backToTopButton = document.createElement('div');
    backToTopButton.className = 'back-to-top';
    backToTopButton.innerHTML = '&#8679;'; // Up arrow symbol
    backToTopButton.id = 'back-to-top';
    document.body.appendChild(backToTopButton);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('visible');
        } else {
            backToTopButton.classList.remove('visible');
        }
    });
    
    // Scroll to top when button is clicked
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
