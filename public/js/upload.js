document.getElementById('uploadArea').addEventListener('click', function() {
    document.getElementById('image').click();
});

document.getElementById('image').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        const uploadArea = document.getElementById('uploadArea');
        uploadArea.style.display = 'none'; // Скрыть область загрузки

        const imagePreview = document.getElementById('imagePreview');
        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image preview">` +
                                 `<button type="button" id="removeImageBtn" class="remove-image-btn">Удалить</button>`;

        imagePreview.style.display = 'flex'; // Изменить на flex для корректного центрирования

        // Добавление обработчика событий для кнопки удаления
        document.getElementById('removeImageBtn').addEventListener('click', function() {
            const uploadArea = document.getElementById('uploadArea');
            const imagePreview = document.getElementById('imagePreview');
            const imageInput = document.getElementById('image');
        
            uploadArea.style.display = 'flex';
            imagePreview.style.display = 'none';
            imagePreview.innerHTML = '';
            imageInput.value = ''; // Сбросить значение инпута файла
        });
    };

    reader.readAsDataURL(file);
});


window.onload = function() {
    var modal = document.getElementById('blockModal');
    var span = document.getElementsByClassName('close-button')[0];

    if (modal) {
        modal.style.display = 'block';
    }

    span.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}
