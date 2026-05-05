<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $album->name }}</title>
    <link href="{{ asset('css/album.css') }}" rel="stylesheet">
    <!-- Дополнительные теги head -->
</head>
<body>
    @include('partials.header')

    <div class="main-container">
        <div class="albums-container">
            <h1>{{ $album->name }}</h1>
            <p>{{ $album->description }}</p>
            <!-- Выпадающий список сортировки (если необходим) -->
            <div class="images-container">
                @foreach ($album->images as $image)
                    <div class="image-container">
                        <a href="{{ route('images.show', ['id' => $image->image_id]) }}">
                            <img src="{{ $image->url }}" alt="{{ $image->title }}" class="{{ $image->show_blur ? 'blurred' : '' }}">
                        </a>
                        <div class="image-title">{{ $image->title }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

<script>
    // Функция для сортировки изображений
    function sortImages() {
        var sortOption = document.getElementById('sorting-select').value;
        var url = new URL(window.location.href);
        url.searchParams.set('sort', sortOption);
        window.location.href = url.toString();
    }

    // Функция для установки выбранного варианта сортировки на основе URL
    function setSortOptionFromUrl() {
        var url = new URL(window.location.href);
        var sortOption = url.searchParams.get('sort');
        if (sortOption) {
            document.getElementById('sorting-select').value = sortOption;
        }
    }

    window.addEventListener('DOMContentLoaded', setSortOptionFromUrl);
</script>
</body>
</html>
