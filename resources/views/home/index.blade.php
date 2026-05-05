<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
</head>
<body>
@include('partials.header')

<div class="main-container">
    @if(count($albums) > 0)
        <div class="albums-container">
            @foreach($albums as $album)
                <a href="{{ route('albums.show', $album->album_id) }}" class="album-link">
                    <div class="album">
                        @if($album->latest_image_url)
                            <img src="{{ $album->latest_image_url }}" alt="{{ $album->name }}">
                        @endif
                        <h3>{{ $album->name }}</h3>
                        {{-- Остальной контент альбома --}}
                    </div>
                </a>
            @endforeach
        </div>
    @endif


    <div class="images-container">
        @foreach($images as $image)
            <div class="image-container">
                <a href="{{ route('images.show', ['id' => $image->image_id]) }}">
                    <img src="{{ $image->url }}" alt="{{ $image->title }}"
                         class="{{ $image->show_blur ? 'blurred' : '' }}">
                </a>
                <div class="image-title">{{ $image->title }}</div>
            </div>
        @endforeach
    </div>
</div>

</body>
</html>
