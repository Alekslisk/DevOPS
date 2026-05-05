<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование изображения</title>
    <link href="{{ asset('css/edit.css') }}" rel="stylesheet">
</head>
<body>
@include('partials.header')

<div class="edit-container">
    <h2 class="edit-title">Редактирование изображения</h2>

    @if ($errors->any())
    <div class="error-messages">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>увше
    </div>
    @endif

    <form method="POST" action="{{ route('images.update', $image->image_id) }}" enctype="multipart/form-data" class="edit-form">
        @csrf
        @method('PUT')

        <label for="title">Название:</label>
        <input type="text" name="title" id="title" placeholder="Введите название" value="{{ $image->title }}" required>

        <label for="description">Описание:</label>
        <textarea name="description" id="description" placeholder="Добавьте описание">{{ $image->description }}</textarea>

        {{-- Поле для тегов --}}
        <label for="tags">Теги:</label>
        <input type="text" name="tags" id="tags" placeholder="Введите теги" value="{{ implode(', ', $image->tags->pluck('name')->toArray()) }}">

        <div class="adult-content-toggle">
            <label class="switch">
            <input type="checkbox" name="is_adult" id="is_adult" {{ $image->is_adult ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
            <label for="is_adult" class="adult-content-label">Контент для взрослых</label>
        </div>

        <button type="submit" class="edit-button">Сохранить изменения</button>
    </form>

    <div class="edit-link">
        <a href="{{ route('images.show', $image->image_id) }}">Отмена</a>
    </div>
</div>

</body>
</html>
