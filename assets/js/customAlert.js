// Кастомный alert
function showCustomAlert(message) {
    // Проверяем, не открыт ли уже alert
    if (document.getElementById('custom-alert-modal')) return;

    // Создаем элементы
    const modal = document.createElement('div');
    modal.id = 'custom-alert-modal';
    modal.className = 'custom-alert-modal';
    modal.innerHTML = `
        <div class="custom-alert-content">
            <div class="custom-alert-message">${message}</div>
            <button class="buttons-style-one custom-alert-ok">OK</button>
        </div>
    `;
    document.body.appendChild(modal);

    // Обработчик закрытия
    modal.querySelector('.custom-alert-ok').onclick = function() {
        modal.remove();
    };

    // Закрытие по клику вне окна
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Для совместимости с alert
window.customAlert = showCustomAlert;

// Кастомный confirm
function showCustomConfirm(message, callback) {
    return new Promise((resolve) => {
        if (document.getElementById('custom-confirm-modal')) return;
        const modal = document.createElement('div');
        modal.id = 'custom-confirm-modal';
        modal.className = 'custom-alert-modal';
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="custom-alert-message">${message}</div>
                <div style="margin-top: 18px; display: flex; justify-content: center; gap: 16px;">
                    <button class="buttons-style-one custom-confirm-ok">OK</button>
                    <button class="buttons-style-one custom-confirm-cancel">Скасувати</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        const close = (result) => {
            modal.remove();
            if (callback) callback(result);
            resolve(result);
        };
        modal.querySelector('.custom-confirm-ok').onclick = () => close(true);
        modal.querySelector('.custom-confirm-cancel').onclick = () => close(false);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) close(false);
        });
    });
}
window.customConfirm = showCustomConfirm;

// Кастомный confirm с чекбоксом
function showCustomConfirmWithCheckbox(message, checkboxLabel) {
    return new Promise((resolve) => {
        if (document.getElementById('custom-confirm-modal')) return;
        const modal = document.createElement('div');
        modal.id = 'custom-confirm-modal';
        modal.className = 'custom-alert-modal';
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="custom-alert-message">${message}</div>
                <label style="display: flex; align-items: center; margin: 14px 0;">
                    <input type="checkbox" id="block-email-checkbox" style="margin-right: 8px;">
                    ${checkboxLabel}
                </label>
                <div style="margin-top: 12px; display: flex; justify-content: center; gap: 16px;">
                    <button class="buttons-style-one custom-confirm-ok">OK</button>
                    <button class="buttons-style-one custom-confirm-cancel">Скасувати</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        const close = (result, checked) => {
            modal.remove();
            resolve({confirmed: result, checked: checked});
        };
        modal.querySelector('.custom-confirm-ok').onclick = () => close(true, document.getElementById('block-email-checkbox').checked);
        modal.querySelector('.custom-confirm-cancel').onclick = () => close(false, false);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) close(false, false);
        });
    });
}
window.customConfirmWithCheckbox = showCustomConfirmWithCheckbox;

// Показывать alert после обновления страницы
function showAlertAfterReload(message) {
    sessionStorage.setItem('postReloadAlert', message);
    window.location.reload();
}
window.ля = showAlertAfterReload;

// Проверка и показ alert после reload
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showPostReloadAlert);
} else {
    showPostReloadAlert();
}
function showPostReloadAlert() {
    const msg = sessionStorage.getItem('postReloadAlert');
    if (msg) {
        customAlert(msg);
        sessionStorage.removeItem('postReloadAlert');
    }
}

function showAlertOnIndex(message) {
    sessionStorage.setItem('postReloadAlert', message);
    window.location.href = 'index.php';
}

// Универсальный alert с редиректом на любую страницу
function showAlertAndRedirect(message, url) {
    sessionStorage.setItem('postReloadAlert', message);
    window.location.href = url;
}
window.showAlertAndRedirect = showAlertAndRedirect;