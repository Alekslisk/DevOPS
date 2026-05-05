<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заблокирован</title>
    <link href="{{ asset('css/blocked.css') }}" rel="stylesheet"> {{-- Подключение CSS файла --}}
</head>
<body>
    <div class="blocked-content">
        <h1>Вы заблокированы</h1>
        @if($user->is_blocked)
            <p>Причина блокировки: {{ $user->block_reason }}</p>
            <p>Дата окончания блокировки: {{ $user->blocked_until->format('d-m-Y H:i') }}</p>
        @else
            <p>Ваш аккаунт не заблокирован.</p>
        @endif
    </div>
</body>
</html>