document.addEventListener('DOMContentLoaded', function() {
    var likeButton = document.getElementById('like-btn');
    var favoriteButton = document.getElementById('favorite-btn');
    
    

    function getCsrfToken() {
        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        return csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    }

    function handleLikeOrFavorite(button, type) {
        var csrfToken = getCsrfToken();  // Получение CSRF-токена
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        var imageId = button.dataset.imageId;
        if (!imageId) {
            console.error('Image ID is missing');
            return;
        }

        var action = (button.dataset.liked === 'true' || button.dataset.favorited === 'true') ? 'DELETE' : 'POST';
        var url = '/images/' + imageId + '/' + type;

        fetch(url, {
            method: action,
            headers: {
                'X-CSRF-TOKEN': csrfToken,  // Использование CSRF-токена
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            updateButtonState(button, data, type);
        })
        .catch(error => console.error('Error:', error));
    }

    function updateButtonState(button, data, type) {
        var isLikedOrFavorited = type === 'likes' ? 'liked' : 'favorited';
    
        if (data.status === 'liked' || data.status === 'added_to_favorites') {
            button.dataset[isLikedOrFavorited] = 'true';
            button.classList.replace('far', 'fas');
            if (type === 'favorites') {
                button.classList.add('favorited');
            } else if (type === 'likes') {
                button.classList.add('liked');
            }
        } else if (data.status === 'unliked' || data.status === 'removed_from_favorites') {
            button.dataset[isLikedOrFavorited] = 'false';
            button.classList.replace('fas', 'far');
            if (type === 'favorites') {
                button.classList.remove('favorited');
            } else if (type === 'likes') {
                button.classList.remove('liked');
            }
        }
    
        // Обновление счетчика лайков
        if (type === 'likes' && data.likes !== undefined) {
            var likeCountElement = document.getElementById('like-count');
            if (likeCountElement) {
                likeCountElement.innerText = data.likes;
            }
        }
    }
    
    if (likeButton) {
        likeButton.addEventListener('click', function() {
            handleLikeOrFavorite(this, 'likes');
        });
    }

    if (favoriteButton) {
        favoriteButton.addEventListener('click', function() {
            handleLikeOrFavorite(this, 'favorites');
        });
    }
});

function openModal() {
    var modal = document.getElementById('myModal');
    var modalImg = document.getElementById('modalImage');
    var img = document.querySelector('.displayed-image');

    modal.style.display = "block";
    modalImg.src = img.src;
}

function closeModal() {
    var modal = document.getElementById('myModal');
    modal.style.display = "none";
}

// Обработчик клика вне изображения для закрытия модального окна
var modal = document.getElementById('myModal');
modal.addEventListener('click', function(event) {
    var modalImg = document.getElementById('modalImage');
    if (event.target === modalImg) {
        // Клик внутри изображения, не делаем ничего
        return;
    }
    // Клик вне изображения, закрываем модальное окно
    closeModal();
});

// Обработчик нажатия клавиши Esc для закрытия модального окна
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

function confirmDelete() {
    return confirm('Вы уверены, что хотите удалить это изображение?');
}

function editComment(commentId) {
    let commentDiv = document.getElementById('comment-' + commentId);
    let commentContentDiv = commentDiv.querySelector('.comment-content');
    let commentContent = commentContentDiv.innerText;
    let editTextArea = '<textarea id="edit-comment-text-' + commentId + '">' + commentContent + '</textarea>';
    let saveButton = '<button onclick="saveComment(' + commentId + ')">Сохранить</button>';

    commentContentDiv.innerHTML = editTextArea; // Замена только текста комментария
    commentDiv.querySelector('.edit-comment-button').outerHTML = saveButton; // Замена кнопки "Редактировать" на "Сохранить"
}

function saveComment(commentId) {
    let editedContent = document.getElementById('edit-comment-text-' + commentId).value;

    fetch('/comments/' + commentId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: editedContent })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Сетевой ответ был не ok.');
        }
        return response.json();
    })
    .then(data => {
        // Обновление комментария на странице
        let commentDiv = document.getElementById('comment-' + commentId);
        commentDiv.querySelector('.comment-content').innerText = editedContent; // Обновление текста комментария

        // Восстановление кнопки "Редактировать"
        let editButton = '<button class="edit-comment-button" onclick="editComment(' + commentId + ')">Редактировать</button>';
        commentDiv.querySelector('button').outerHTML = editButton;

        // Закрытие редактора и возврат к обычному отображению комментария
    })
    .catch(error => console.error('Ошибка:', error));
}


function deleteComment(commentId) {
    if (confirm('Вы уверены, что хотите удалить этот комментарий?')) {
        fetch('/comments/' + commentId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка при удалении комментария.');
            }
            document.getElementById('comment-' + commentId).remove();
        })
        .catch(error => console.error('Ошибка:', error));
    }
}

