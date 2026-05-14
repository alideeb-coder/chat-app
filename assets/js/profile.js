document.addEventListener("DOMContentLoaded", function () {
    const avatarForm = document.getElementById('avatarForm');
    if (!avatarForm) return;
    avatarForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const fileInput = document.querySelector('input[name="avatar"]');
        if (!fileInput || fileInput.files.length === 0) {
            alert('Please select an image file first.');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);
const saveBtn = avatarForm.querySelector('button[type="submit"]');
saveBtn.disabled = true;
saveBtn.textContent = 'Uploading..';
        fetch('ajax/upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const avatarImg = document.querySelector('img[alt="Avatar"]');
                if (avatarImg) {
                    avatarImg.src = data.image_url + '?t=' + new Date().getTime();
                }
            } else {
                alert(data.error || 'Upload failed. Please try again.');
            }
        }).finally(() => {
    saveBtn.disabled = false;
    saveBtn.textContent = 'Save';
})
        .catch(error => {
            alert('Network error. Please check your connection and try again.');
            console.error('Upload error:', error);
        });
    });
});