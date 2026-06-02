const popupReg = document.getElementById("regPopup");
const regBtn = document.getElementById("mainPageRegBtn");
regBtn.addEventListener("click", (e) => {
    if (popupReg.classList.contains("visibleFalse")) {
        popupReg.classList.remove("visibleFalse")
    }
})
// authReg Block
// // switching and closing
const regCloseBtn = document.getElementById("registrationCloseBtn");
const authModeBtn = document.getElementById("authorizationSwitchBtn");
console.log(authModeBtn);
const  regModeBtn = document.getElementById("registrationSwitchBtn");
const  registrationBlock = document.getElementById("registrationFormBlock");
registrationBlock.addEventListener('submit', (e) => {
    e.preventDefault();
})
const  authorizationBlock = document.getElementById("authorizationFormBlock");
let authRegMode = 1; 
authModeBtn.addEventListener("click", (e) => {
    if (authRegMode == 1) {
        registrationBlock.classList.add("visibleFalse");
        authorizationBlock.classList.remove("visibleFalse");
        authModeBtn.setAttribute('disabled', true);
        regModeBtn.removeAttribute("disabled");
        authRegMode = 0;
    }
})
regModeBtn.addEventListener("click", (e) => {
    if (authRegMode == 0) {
        authorizationBlock.classList.add("visibleFalse");
        registrationBlock.classList.remove("visibleFalse");
        regModeBtn.setAttribute('disabled', true);
        authModeBtn.removeAttribute("disabled");
        authRegMode = 1;
    }
})
regCloseBtn.addEventListener("click", (e) => {
    popupReg.classList.add("visibleFalse");
})

// // registration
const regNameBlock = document.getElementById("registrationNameFormField");
const regEmailBlock = document.getElementById("registrationEmailFormField");
const regPasswordBlock = document.getElementById("registrationPasswordFormField")
const regPassConfBlock = document.getElementById("registrationConfPassFormField")
const regNameField = regNameBlock.querySelector(".formField")
const regEmailField = regEmailBlock.querySelector(".formField")

regNameField.addEventListener("input", (e) => {
    let errorLine = regNameBlock.querySelector(".formFieldError");
    if (regNameField.value == "") {
        errorLine.textContent = "Введите имя"
    }
    else if (regNameField.value.length <= 3 && regNameField.value != '') {
        errorLine.textContent = "имя должно быть не меньше 3 букв";
    } else {
        errorLine.textContent = "";
    }
})
regEmailField.addEventListener("input", (e) => {
    let errorLine = regEmailBlock.querySelector(".formFieldError");
    if (!regEmailField.value.includes("@")) {
        errorLine.textContent = "Введите корректный Email";
    } else {
        errorLine.textContent = '';
    }
})

