<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Альбомы</title>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/albums.css') }}" rel="stylesheet">
</head>
<body>
    @include('partials.header')

    <div class="main-container">
        @if(count($albums) > 0)
            <div class="albums-container">
            @foreach ($albums as $album)
                <a href="{{ route('albums.show', $album->album_id) }}" class="album-link">
                    <div class="album">
                        @if($album->latest_image_url)
                            <img src="{{ $album->latest_image_url }}" alt="{{ $album->name }}">
                        @endif
                        <h3>{{ $album->name }}</h3>
                    </div>
                </a>
            @endforeach
            </div>
        @endif
    </div>
</body>
</html>
