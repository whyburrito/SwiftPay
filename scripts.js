// Toggles visibility of password input fields
function toggleVisibility(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

// Opens a modal
function openModal(id) {
    document.getElementById(id).style.display = "block";
}
// Closes a modal
function closeModal(id) {
    document.getElementById(id).style.display = "none";
}
// Closes all modals when clicking outside of them
window.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        // Close the modal if the click event target is the modal
        if (event.target === modal) {
            closeModal(modal.id);
        }
    });
});

// Checks password strength and displays it
function checkStrength(inputId, strengthDisplayId) {
    const pw = document.getElementById(inputId).value;
    const strengthText = document.getElementById(strengthDisplayId);

    let strength = 0;
    // Adds points for each different criteria
    if (pw.length >= 6) strength++;
    if (/[A-Z]/.test(pw)) strength++;
    if (/\d/.test(pw)) strength++;
    if (/[\W]/.test(pw)) strength++;

    // Displays the strength indicator based on the points
    if (strength === 0) {
        strengthText.innerText = '';
        strengthText.className = '';
    } else if (strength <= 2) {
        strengthText.innerText = 'Weak';
        strengthText.className = 'weak';
    } else if (strength === 3) {
        strengthText.innerText = 'Medium';
        strengthText.className = 'medium';
    } else {
        strengthText.innerText = 'Strong';
        strengthText.className = 'strong';
    }
}

// Validates password match and displays an error message if not
function validatePasswordMatch(pwId, confirmId) {
    const pw = document.getElementById(pwId).value;
    const cpw = document.getElementById(confirmId).value;

    if (pw.length < 6) {
        alert("Password must be at least 6 characters.");
        return false; // Return false to prevent the form from submitting
    }

    if (pw !== cpw) {
        alert("Passwords do not match.");
        return false; // Return false to prevent the form from submitting
    }

    return true; // Return true to allow the form to submit
}

// Previews the uploaded avatar
function previewAvatar(event) {
    // Create a new FileReader object, a built-in browser API for reading files
    const reader = new FileReader();

    // Runs when the file reader has successfully reads the file
    reader.onload = function () {
        const img = document.getElementById('avatarPreview');
        img.src = reader.result; // Set the image source to the file reader's result
    };
    // Start reading the selected file
    reader.readAsDataURL(event.target.files[0]);
}

// Copies the account number to the clipboard
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;

    // Write the text to the clipboard using the navigator.clipboard API
    navigator.clipboard.writeText(text).then(() => {
        // Display a success message if the clipboard write succeeds
        alert("Account number copied to clipboard!");
    }).catch(err => {
        // Display an error message if the clipboard write fails
        console.error("Failed to copy text: ", err);
    });
}
