document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('photo');
    const previewContainer = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');

    if (!fileInput || !previewContainer || !previewImage || !fileName) {
        return;
    }

    // handle when user changes file input
    fileInput.addEventListener('change', function () {
        const selectedFile = this.files && this.files[0];

        if (!selectedFile) {
            previewContainer.classList.add('d-none');
            return;
        }

        // use filereader to show image on screen
        const reader = new FileReader();

        reader.onload = function (event) {
            // console.log("photo loaded: " + selectedFile.name);
            previewImage.src = event.target.result;
            fileName.textContent = selectedFile.name;
            previewContainer.classList.remove('d-none');
        };

        reader.readAsDataURL(selectedFile);
    });
});
