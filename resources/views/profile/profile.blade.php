<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
</head>
<body>
@include('partials.header')
@php
    $currentUser = Auth::user();
    $isEligibleAdmin = $currentUser && $currentUser->administrator && $currentUser->administrator->privileges_level >= 2;
    $canBlockAdmins = $currentUser && $currentUser->administrator && $currentUser->administrator->privileges_level >= 3;
@endphp

<div class="main-container">
        <div class="profile-header">
            <h2>Профиль {{ $user->username }}</h2>
        </div>

        <div class="admin-controls">
            {{-- Проверка на администратора с правом блокировки --}}
            @if ($isEligibleAdmin && (!$user->administrator || $canBlockAdmins))
                <button id="blockButton" class="block-button">Заблокировать пользователя</button>
            @endif
            
            @if ($isEligibleAdmin && $user->is_blocked)
                <button id="unblockButton" class="unblock-button">Снять блокировку</button>
            @endif
        </div>

        <div id="confirmationModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Подтвердите действие</h2>
                <p>Вы уверены, что хотите снять блокировку с этого пользователя?</p>
                <button onclick="unblockUser()">Подтвердить</button>
            </div>
        </div>


        <div id="blockUserModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Блокировка пользователя</h2>
                <form class="modal-form" action="{{ route('admin.blockUser', ['userId' => $user->user_id]) }}" method="post">
                    @csrf
                    <div>
                        <input type="radio" id="restrict" name="blockType" value="restrict" class="radio-input">
                        <label for="restrict" class="radio-label">Ограничить пользователя</label>
                    </div>
                    <div>
                        <input type="radio" id="block" name="blockType" value="block" checked class="radio-input">
                        <label for="block" class="radio-label">Заблокировать пользователя</label>
                    </div>
                    <div>
                        <label for="blockReason">Причина блокировки:</label>
                        <input type="text" id="blockReason" name="block_reason">
                    </div>
                    <div>
                        <label for="blockedUntil">Заблокировать до (дата):</label>
                        <input type="date" id="blockedUntil" name="blocked_until">
                    </div>
                    <button type="submit" class="submit-button">Применить</button>
                </form>
            </div>
        </div>

    <div class="tabs">
        <div class="tab active" onclick="showSection('uploaded')">Загруженные</div>
        <div class="tab" onclick="showSection('favorites')">Избранные</div>
        <div class="tab" onclick="showSection('liked')">Лайкнутые</div>
    </div>

    <div class="images-section" id="uploaded">
        @foreach($uploadedImages as $image)
            <div class="image-container">
                <a href="{{ route('images.show', ['id' => $image->image_id]) }}">
                    <img src="{{ $image->url }}" alt="{{ $image->title }}" class="{{ $image->show_blur ? 'blurred' : '' }}">
                    <div class="image-title">{{ $image->title }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="images-section hidden" id="favorites">
        @foreach($favoriteImages as $image)
            <div class="image-container">
                <a href="{{ route('images.show', ['id' => $image->image_id]) }}">
                    <img src="{{ $image->url }}" alt="{{ $image->title }}" class="{{ $image->show_blur ? 'blurred' : '' }}">
                    <div class="image-title">{{ $image->title }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="images-section hidden" id="liked">
        @foreach($likedImages as $image)
            <div class="image-container">
                <a href="{{ route('images.show', ['id' => $image->image_id]) }}">
                    <img src="{{ $image->url }}" alt="{{ $image->title }}" class="{{ $image->show_blur ? 'blurred' : '' }}">
                    <div class="image-title">{{ $image->title }}</div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<script>
    function showSection(section) {
        document.querySelectorAll('.images-section').forEach(div => div.classList.add('hidden'));
        document.getElementById(section).classList.remove('hidden');
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.querySelector(`[onclick="showSection('${section}')"]`).classList.add('active');
    }

    // Управление модальным окном для блокировки пользователя
    var blockUserModal = document.getElementById("blockUserModal");
    var blockBtn = document.getElementById("blockButton");
    var unblockBtn = document.getElementById("unblockButton");
    var confirmationModal = document.getElementById("confirmationModal");

    // Обработчики для кнопки блокировки
    if (blockBtn && blockUserModal) {
        var blockModalClose = blockUserModal.querySelector(".close");
        blockBtn.onclick = function() {
            blockUserModal.style.display = "block";
        }
        blockModalClose.onclick = function() {
            blockUserModal.style.display = "none";
        }
    }

    // Обработчики для кнопки разблокировки
    if (unblockBtn && confirmationModal) {
        unblockBtn.onclick = function() {
            confirmationModal.style.display = "block";
        }
        var confirmModalClose = confirmationModal.querySelector(".close");
        confirmModalClose.onclick = function() {
            confirmationModal.style.display = "none";
        }
    }

    // Функция для снятия блокировки
    function unblockUser() {
        fetch("{{ route('admin.unblockUser', ['userId' => $user->user_id]) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ _method: 'POST' })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            // После успешного снятия блокировки скрываем кнопку и модальное окно
            if (unblockBtn) unblockBtn.style.display = 'none';
            if (confirmationModal) confirmationModal.style.display = "none";
        });
    }

    // Закрытие модальных окон при клике вне их
    window.onclick = function(event) {
        if (blockUserModal && event.target == blockUserModal) {
            blockUserModal.style.display = "none";
        }
        if (confirmationModal && event.target == confirmationModal) {
            confirmationModal.style.display = "none";
        }
    }
</script>

</body>
</html>
