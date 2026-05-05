<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка изображения</title>
    <link href="{{ asset('css/upload.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
</head>
<body>
@include('partials.header')

<div class="container">
    <form method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="left-panel">
            <div class="upload-area" id="uploadArea">Кликните здесь или перетащите изображение для загрузки</div>
            <input type="file" name="image" id="image" required style="display: none;">
            <div id="imagePreview">
                <button type="button" id="removeImageBtn" style="display: none;">&#10006;</button>
            </div>
        </div>

        <div class="right-panel">
            @csrf
            <input type="hidden" name="image_url" id="imageUrl"> <!-- Скрытое поле для URL изображения -->

            <label for="title">Название:</label>
            <input type="text" name="title" id="title" placeholder="Введите название" required>

            <label for="description">Описание:</label>
            <textarea name="description" id="description" placeholder="Добавьте описание"></textarea>

            <label for="album_id">Альбом:</label>
            <select name="album_id" id="album_id">
                <option value="">Выберите альбом</option>
                @foreach ($albums as $album)
                    <option value="{{ $album->album_id }}">{{ $album->name }}</option>
                @endforeach
            </select>


            <input type="text" name="new_album" placeholder="Название нового альбома">

            <label for="tags">Теги:</label>
            <input type="text" name="tags" id="tags" placeholder="Введите теги">

            <div class="adult-content-toggle">
                <label class="switch">
                    <input type="checkbox" name="is_adult">
                    <span class="slider"></span>
                </label>
                <span class="adult-content-label">Контент для взрослых</span>
            </div>
            <button type="submit">Сохранить информацию</button>
        </div>
    </form>
</div>
@if ($errors->has('blocked'))
    <div id="blockModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Внимание! Вы заблокированы</h2>
            <p>{{ $errors->first('blocked') }}</p>
            <p>Причина блокировки: {{ Auth::user()->block_reason }}</p>
            <p>Ваша блокировка закончится: {{ Auth::user()->blocked_until }}</p>
        </div>
    </div>
@endif

<script src="{{ asset('js/upload.js') }}"></script>
</body>
</html>
