document.addEventListener("DOMContentLoaded", () => {
    const userInput = document.getElementById("username")
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirmPassword");
    const createBtn = document.querySelector('input[name="submit"]');

    const userMsg = document.querySelector(".requirement-hint-user")
    const passMsg = document.querySelector(".requirement-hint-pass");
    const conPassMsg = document.querySelector(".requirement-hint-conf");


    function validateUserName() {
        const username = userInput.value;
        let valid = true;


        if (!/^[a-zA-Z]+$/.test(username)) valid = false;
        

        if (username.length < 6) valid = false;

        if (valid) {
            userMsg.textContent = "Username is valid.";
            userMsg.style.color = "limegreen";
        } else {
            userMsg.textContent = "Must be 6+ characters from the alphabet (no numbers or symbols)";
            userMsg.style.color = "red";
        }
        return valid;
    }

    function validatePassword() {
        const password = passwordInput.value;
        let valid = true;


        if (!/^.{7,}$/.test(password)) valid = false;
        
        if (!/\d/.test(password)) valid = false;
        
        if (!/[A-Z]/.test(password)) valid = false;

        if (valid) {
            passMsg.textContent = "Password is valid.";
            passMsg.style.color = "limegreen";
            confirmInput.disabled = false;
        } else {
            passMsg.textContent = "Must contain 7+ characters, 1+ numbers, and 1+ capital letter";
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
        createBtn.disabled = !(validateUserName() && validatePassword() && validateConfirm());
    }

    userInput.addEventListener("input", () => {
        validateUserName();
        toggleCreateBtn();
    });

    passwordInput.addEventListener("input", () => {
        validatePassword();
        toggleCreateBtn();
    });

    confirmInput.addEventListener("input", () => {
        validateConfirm();
        toggleCreateBtn();
    });

    createBtn.disabled = true;
    confirmInput.disabled = true;
});
