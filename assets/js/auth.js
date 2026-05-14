document.addEventListener("DOMContentLoaded", function () {
    let toggle_password = document.querySelectorAll(".toggle-password");
    toggle_password.forEach(btn => {
        btn.addEventListener('click', () => {
            const container = btn.closest('.relative');
            if (!container) return;
            const input = container.querySelector('input');
            if (!input) return;
            
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (eyeOpen) eyeOpen.classList.toggle('hidden');
            if (eyeClosed) eyeClosed.classList.toggle('hidden');
        });
    });
});