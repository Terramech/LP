document.addEventListener('DOMContentLoaded', function() {
    const popupReg = document.getElementById("regPopup");
    const regBtn = document.getElementById("mainPageRegBtn");

    if (regBtn) {
        regBtn.addEventListener("click", (e) => {
            if (popupReg.classList.contains("visibleFalse")) {
                popupReg.classList.remove("visibleFalse");
                popupReg.classList.add("visibleTrue");
            }
        });
    }

    const popup = document.getElementById('regPopup');
    const closeBtn = document.getElementById('registrationCloseBtn');
    const openBtn = document.getElementById('mainPageRegBtn');

    if (openBtn) {
        openBtn.addEventListener('click', function() {
            popup.classList.remove('visibleFalse');
            popup.classList.add('visibleTrue');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            popup.classList.remove('visibleTrue');
            popup.classList.add('visibleFalse');
        });
    }

    if (popup) {
        popup.addEventListener('click', function(event) {
            if (event.target === popup) {
                popup.classList.remove('visibleTrue');
                popup.classList.add('visibleFalse');
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && popup && popup.classList.contains('visibleTrue')) {
            popup.classList.remove('visibleTrue');
            popup.classList.add('visibleFalse');
        }
    });

    // Переключение регистрация/вход
    const authModeBtn = document.getElementById("authorizationSwitchBtn");
    const regModeBtn = document.getElementById("registrationSwitchBtn");
    const registrationBlock = document.getElementById("registrationFormBlock");
    const authorizationBlock = document.getElementById("authorizationFormBlock");
    let authRegMode = 1;

    if (authModeBtn) {
        authModeBtn.addEventListener("click", (e) => {
            if (authRegMode == 1) {
                registrationBlock.classList.add("visibleFalse");
                authorizationBlock.classList.remove("visibleFalse");
                authModeBtn.setAttribute('disabled', true);
                regModeBtn.removeAttribute("disabled");
                authRegMode = 0;
            }
        });
    }

    if (regModeBtn) {
        regModeBtn.addEventListener("click", (e) => {
            if (authRegMode == 0) {
                authorizationBlock.classList.add("visibleFalse");
                registrationBlock.classList.remove("visibleFalse");
                regModeBtn.setAttribute('disabled', true);
                authModeBtn.removeAttribute("disabled");
                authRegMode = 1;
            }
        });
    }

    // ====== РЕГИСТРАЦИЯ ======
    const regForm = document.getElementById('registrationFormBlock');
    if (regForm) {
        regForm.setAttribute('onsubmit', 'return false;');
        const regBtn = regForm.querySelector('.authRegButton');
        if (regBtn) {
            regBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                clearAuthErrors();

                const name = regForm.querySelector('input[name="name"]')?.value?.trim() || '';
                const email = regForm.querySelector('input[name="email"]')?.value?.trim() || '';
                const password = regForm.querySelector('input[name="password"]')?.value || '';
                const confirmPassword = regForm.querySelector('input[name="confirm_password"]')?.value || '';

                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('confirm_password', confirmPassword);

                try {
                    // АБСОЛЮТНЫЙ ПУТЬ — .htaccess перенаправит в src/handlers/
                    const response = await fetch('handlers/register.php', {
                        method: 'POST',
                        body: formData
                    });
                    const text = await response.text();
                    console.log('Ответ сервера:', text.substring(0, 200));
                    const data = JSON.parse(text);

                    if (data.success) {
                        showAuthSuccess(data.message || 'Регистрация успешна!');
                        setTimeout(() => switchToAuthMode(), 1500);
                    } else {
                        showAuthErrors(data.errors);
                    }
                } catch (err) {
                    console.error('Ошибка:', err);
                    showAuthErrors({general: 'Ошибка соединения'});
                }
            });
        }
    }

    // ====== АВТОРИЗАЦИЯ ======
    const authForm = document.getElementById('authorizationFormBlock');
    if (authForm) {
        authForm.setAttribute('onsubmit', 'return false;');
        const authBtn = authForm.querySelector('.authRegButton');
        if (authBtn) {
            authBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                clearAuthErrors();

                const email = authForm.querySelector('input[name="email"]')?.value?.trim() || '';
                const password = authForm.querySelector('input[name="password"]')?.value || '';

                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);

                try {
                    // АБСОЛЮТНЫЙ ПУТЬ — .htaccess перенаправит в src/handlers/
                    const response = await fetch('/handlers/login.php', {
                        method: 'POST',
                        body: formData
                    });
                    const text = await response.text();
                    console.log('Ответ сервера:', text.substring(0, 200));
                    const data = JSON.parse(text);

                    if (data.success) {
                        window.location.href = data.redirect || '/index.php';
                    } else {
                        showAuthErrors(data.errors);
                    }
                } catch (err) {
                    console.error('Ошибка:', err);
                    showAuthErrors({general: 'Ошибка соединения'});
                }
            });
        }
    }
});

function showAuthErrors(errors) {
    for (const [field, message] of Object.entries(errors)) {
        let errorEl = document.querySelector(`.formFieldError[data-field="${field}"]`);
        if (!errorEl) {
            const fieldMap = {
                'name': 'registrationNameFormField',
                'email': 'registrationEmailFormField',
                'password': 'registrationPasswordFormField',
                'confirm_password': 'registrationConfPassFormField'
            };
            if (fieldMap[field]) {
                const block = document.getElementById(fieldMap[field]);
                if (block) errorEl = block.querySelector('.formFieldError');
            }
        }
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.color = '#e74c3c';
            errorEl.style.fontSize = '12px';
            errorEl.style.display = 'block';
        }
    }
}

function clearAuthErrors() {
    document.querySelectorAll('.formFieldError').forEach(el => {
        el.textContent = '';
        el.style.color = '';
        el.style.display = '';
    });
}

function showAuthSuccess(message) {
    const regBtn = document.querySelector('#registrationFormBlock .authRegButton');
    if (regBtn) {
        const originalText = regBtn.textContent;
        regBtn.textContent = '✓ ' + message;
        regBtn.style.background = '#2ecc71';
        setTimeout(() => {
            regBtn.textContent = originalText;
            regBtn.style.background = '';
        }, 2000);
    }
}

function switchToAuthMode() {
    const authModeBtn = document.getElementById('authorizationSwitchBtn');
    if (authModeBtn) authModeBtn.click();
}