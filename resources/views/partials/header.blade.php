<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Header</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
</head>
<body>
    <div class="header">
        <div class="left-header">
            <a href="/?sort=popular_all_time" class="no-style-link">
                <span class="site-name">OtakuArtHub</span>
            </a>
            <a href="/" class="header-button">Главная</a>
            <a href="{{ route('albums.index') }}" class="header-button">Альбомы</a>
            @auth
                <a href="/upload" class="header-button">Загрузить изображение</a>
            @endauth
        </div>
        
        <div class="search-bar">
            <i class="fas fa-search search-icon" id="search-icon"></i>
            <form id="tag-search-form" action="/" method="GET">
                <input type="text" name="tag" placeholder="Поиск по тегам..." class="search-input" id="search-input">
            </form>
        </div>


        @php
            // Проверка, является ли текущая страница главной или страницей альбома
            $showSortingDropdown = request()->is('/') || request()->is('albums/*');
        @endphp

        @if ($showSortingDropdown)
            <div class="sorting-dropdown">
                <select id="sorting-select" onchange="sortImages()">
                    <option value="newest">Самые новые</option>
                    <option value="popular_all_time">Лидеры рейтинга</option>
                    <option value="popular_year">Популярные за год</option>
                    <option value="popular_month">Популярные за месяц</option>
                    <option value="popular_week">Популярные за неделю</option>
                </select>
            </div>
        @endif


        <div class="right-header">
            @auth
                <a href="{{ url('/profile/' . Auth::user()->user_id) }}" class="header-button">Профиль</a>
                <a href="/logout" class="header-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выйти</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <a href="/login" class="header-button">Войти</a>
                <a href="/register" class="header-button">Регистрация</a>
            @endauth
        </div>
        </div>
    </div>
    <script>
        window.addEventListener('scroll', function() {
            var header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        function sortImages() {
            var sortOption = document.getElementById('sorting-select').value;
            var tagInput = document.querySelector('input[name="tag"]').value;
            var url = new URL(window.location.href);
            url.searchParams.set('sort', sortOption);
            if (tagInput) {
                url.searchParams.set('tag', tagInput);
            }
            window.location.href = url.toString();
        }

        function setSortOptionFromUrl() {
            var url = new URL(window.location.href);
            var sortOption = url.searchParams.get('sort');
            if (sortOption) {
                document.getElementById('sorting-select').value = sortOption;
            }
        }

        // Функция для установки значения строки поиска
        function setSearchInputValue() {
            var urlParams = new URLSearchParams(window.location.search);
            var searchTag = urlParams.get('tag');
            if (searchTag) {
                document.getElementById('search-input').value = searchTag;
            }
        }

        window.addEventListener('DOMContentLoaded', (event) => {
            setSortOptionFromUrl();
            setSearchInputValue();
        });

        document.addEventListener('DOMContentLoaded', function() {
        var searchIcon = document.getElementById('search-icon');
        var searchForm = document.getElementById('tag-search-form');
        
        searchIcon.addEventListener('click', function() {
            searchForm.submit();
        });
    });

    </script>

</body>
</html>
