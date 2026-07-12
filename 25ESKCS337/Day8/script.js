document.addEventListener('DOMContentLoaded', () => {
    const photoInput = document.getElementById('student_photo');
    const photoPreview = document.getElementById('photo_preview');
    const defaultAvatar = document.getElementById('default_avatar');
    const uploadOverlay = document.getElementById('upload_overlay');
    const registrationForm = document.getElementById('registrationForm');

    // click the camera overlay to trigger file chooser
    if (uploadOverlay) {
        uploadOverlay.addEventListener('click', () => {
            photoInput.click();
        });
    }

    // show picture preview using FileReader
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // check if it's an image
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG or PNG).');
                    this.value = ''; // clear input
                    resetPreview();
                    return;
                }

                // file can't be over 2MB
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('File size exceeds 2MB. Please select a smaller image.');
                    this.value = ''; // clear input
                    resetPreview();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    // change profile picture display
                    defaultAvatar.style.display = 'none';
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                resetPreview();
            }
        });
    }

    function resetPreview() {
        photoPreview.src = '';
        photoPreview.style.display = 'none';
        defaultAvatar.style.display = 'block';
    }

    // validation checks before sending form
    if (registrationForm) {
        registrationForm.addEventListener('submit', (e) => {
            let isValid = true;

            const name = document.getElementById('full_name');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const course = document.getElementById('course');
            const address = document.getElementById('address');
            
            // check gender radio buttons
            const genderSelected = document.querySelector('input[name="gender"]:checked');
            const genderGroup = document.querySelector('.gender-group');

            // clear invalid highlights
            document.querySelectorAll('.form-control, .form-select').forEach(el => {
                el.classList.remove('is-invalid');
            });
            if (genderGroup) {
                genderGroup.classList.remove('border', 'border-danger', 'rounded', 'p-2');
            }

            // Name verification
            if (name && name.value.trim() === '') {
                name.classList.add('is-invalid');
                isValid = false;
            }

            // Email verification
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value.trim())) {
                    email.classList.add('is-invalid');
                    isValid = false;
                }
            }

            // Phone verification
            if (phone && phone.value.trim() === '') {
                phone.classList.add('is-invalid');
                isValid = false;
            }

            // Gender verification
            if (!genderSelected) {
                if (genderGroup) {
                    genderGroup.classList.add('border', 'border-danger', 'rounded', 'p-2');
                }
                alert('Please select your gender.');
                isValid = false;
            }

            // Course verification
            if (course && course.value === '') {
                course.classList.add('is-invalid');
                isValid = false;
            }

            // Address verification
            if (address && address.value.trim() === '') {
                address.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // stop submit
                // focus on first field with error
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});
