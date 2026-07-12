// image upload preview helper
document.addEventListener('DOMContentLoaded', () => {
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('photoPreview');

    if (photoInput && preview) {
        photoInput.addEventListener('change', () => {
            const [file] = photoInput.files;
            if (!file) {
                preview.classList.add('d-none');
                return;
            }

            // use reader to show preview on screen
            const reader = new FileReader();
            reader.onload = (event) => {
                // console.log("preview loaded!");
                preview.src = event.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});
