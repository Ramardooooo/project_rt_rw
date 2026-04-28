/*===== FOCUS =====*/
const inputs = document.querySelectorAll(".form__input")

/*=== Add focus ===*/
function addfocus(){
    let parent = this.parentNode.parentNode;
    parent.classList.add("focus");
}

/*=== Remove focus ===*/
function remfocus(){
    let parent = this.parentNode.parentNode;
    if(this.value == ""){
        parent.classList.remove("focus");
    }
}

/*=== To call function===*/
inputs.forEach(input=>{
    input.addEventListener("focus", addfocus);
    input.addEventListener("blur", remfocus);
});

// Enhanced Notification handler for PHP errors - field specific
document.addEventListener('DOMContentLoaded', function() {
    if (window.hasPhpError && window.phpErrorMessage && window.phpErrorType) {
        showFieldSpecificNotification(window.phpErrorMessage, window.phpErrorType);
    }
});

function showFieldSpecificNotification(message, errorType) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;

    // Target field
    let targetField = null;
    let targetDiv = null;
    if (errorType === 'login_input') {
        targetField = document.getElementById('login_input');
        targetDiv = targetField.closest('.form__div');
    } else if (errorType === 'password') {
        targetField = document.getElementById('password');
        targetDiv = targetField.closest('.form__div');
    }

    // Add error class and focus
    if (targetDiv) {
        targetDiv.classList.add('error');
        targetDiv.classList.add('focus');  // Force label up
        targetField.focus();
        targetField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    const notification = document.createElement('div');
    notification.className = `notification-float show ${errorType === 'login_input' || errorType === 'password' ? 'notification-field' : ''}`;
    
    const iconClass = 'bx-error-circle';
    notification.innerHTML = `
        <i class='bx bx-${iconClass}'></i>
        <span>${message}</span>
        <i class='bx bx-x' onclick="this.parentElement.classList.remove('show'); setTimeout(() => { this.parentElement.remove(); }, 300);"></i>
    `;
    
    container.appendChild(notification);
    
    // Auto remove after 6s
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
            if (targetDiv) {
                // Keep error class until user interacts
            }
        }, 300);
    }, 6000);

    // Clear error on input
    if (targetField) {
        targetField.addEventListener('input', function() {
            targetDiv.classList.remove('error');
        });
    }
}

// Legacy support
function showNotification(message, type = 'error') {
    showFieldSpecificNotification(message, 'general');
}

