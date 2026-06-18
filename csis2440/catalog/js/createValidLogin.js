document.addEventListener("DOMContentLoaded", () => {
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirmPassword");
    const createBtn = document.querySelector('input[name="submit"]');

    const passMsg = document.querySelector(".requirement-hint-pass");
    const conPassMsg = document.querySelector(".requirement-hint-conf");

    function validatePassword() {
        const password = passwordInput.value;
        let valid = true;

        if (!/^.{8,}$/.test(password)) valid = false;
        if (!/\d/.test(password)) valid = false;

        if (valid) {
            passMsg.textContent = "Password is valid.";
            passMsg.style.color = "limegreen";
            confirmInput.disabled = false;
        } else {
            passMsg.textContent = "Must contain 8+ characters and ≤ 1 number";
            passMsg.style.color = "red";
            confirmInput.disabled = true;
            confirmInput.value = "";
            conPassMsg.textContent = "Must match password";
            conPassMsg.style.color = "";
        }
        return valid;
    }

    function validateConfirm() {
        const match = passwordInput.value === confirmInput.value && confirmInput.value !== "";
        if (match) {
            conPassMsg.textContent = "Passwords match.";
            conPassMsg.style.color = "limegreen";
        } else {
            conPassMsg.textContent = "Passwords do not match.";
            conPassMsg.style.color = "red";
        }
        return match;
    }

    function toggleCreateBtn() {
        createBtn.disabled = !(validatePassword() && validateConfirm());
    }

    passwordInput.addEventListener("input", () => {
        validatePassword();
        toggleCreateBtn();
    });

    confirmInput.addEventListener("input", () => {
        validateConfirm();
        toggleCreateBtn();
    });

    // Initial state
    createBtn.disabled = true;
    confirmInput.disabled = true;
});
