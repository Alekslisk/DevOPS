<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Administrator;
use App\Models\Album;
use App\Models\Image;
use App\Models\Like;
use App\Models\Favorite;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Администраторы
        $admin1 = $this->createUser('admin1', 'admin1@example.com', 30, true);
        Administrator::create(['user_id' => $admin1->user_id, 'privileges_level' => 1]);

        $admin2 = $this->createUser('admin2', 'admin2@example.com', 35, true);
        Administrator::create(['user_id' => $admin2->user_id, 'privileges_level' => 2]);

        $admin3 = $this->createUser('admin3', 'admin3@example.com', 40, true);
        Administrator::create(['user_id' => $admin3->user_id, 'privileges_level' => 3]);

        // 2. Обычные пользователи
        $user1 = $this->createUser('john_doe', 'john@example.com', 25);
        $user2 = $this->createUser('jane_smith', 'jane@example.com', 22);
        $user3 = $this->createUser('bob_wilson', 'bob@example.com', 28);

        $users = [$user1, $user2, $user3];

        // 3. Для каждого пользователя создаём альбомы и изображения
        foreach ($users as $user) {
            $album1 = Album::create([
                'user_id' => $user->user_id,
                'name' => 'Пейзажи',
                'description' => 'Мои любимые пейзажи',
            ]);

            $album2 = Album::create([
                'user_id' => $user->user_id,
                'name' => 'Города',
                'description' => 'Фотографии городов',
            ]);

            // Создаём по 3 изображения для первого альбома и 2 для второго
            $this->createImagesForAlbum($album1, $user->user_id, 3);
            $this->createImagesForAlbum($album2, $user->user_id, 2);
        }

        // 4. Лайки, избранное, комментарии
        $allImages = Image::all();

        foreach ($users as $user) {
            // Лайки (5 случайных изображений)
            $randomImages = $allImages->random(5);
            foreach ($randomImages as $image) {
                Like::create([
                    'user_id' => $user->user_id,
                    'image_id' => $image->image_id,
                    'liked_at' => now(),
                ]);
            }

            // Избранное (3 случайных изображения)
            $favoriteImages = $allImages->random(3);
            foreach ($favoriteImages as $image) {
                Favorite::create([
                    'user_id' => $user->user_id,
                    'image_id' => $image->image_id,
                    'added_at' => now(),
                ]);
            }

            // Комментарии (2 случайных)
            $commentImages = $allImages->random(2);
            foreach ($commentImages as $image) {
                Comment::create([
                    'user_id' => $user->user_id,
                    'image_id' => $image->image_id,
                    'comment' => $this->randomComment(),
                    'commented_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Добавим пару комментариев от администратора
        $adminCommentImages = $allImages->random(2);
        foreach ($adminCommentImages as $image) {
            Comment::create([
                'user_id' => $admin1->user_id,
                'image_id' => $image->image_id,
                'comment' => 'Отличная работа!',
                'commented_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Сиды успешно созданы!');
    }

    protected function createUser($username, $email, $age, $isAdmin = false)
    {
        return User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password'),
            'age' => $age,
            'is_admin' => $isAdmin,
            'is_blocked' => false,
            'block_level' => 0,
        ]);
    }

    protected function createImagesForAlbum($album, $userId, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            // Используем picsum для гарантированного отображения
            $url = 'https://picsum.photos/800/600?random=' . rand(1, 10000);

            $image = Image::create([
                'user_id' => $userId,
                'title' => 'Изображение ' . ($i + 1),
                'description' => 'Описание для изображения ' . ($i + 1),
                'url' => $url,   // внешняя ссылка, не требует локальных файлов
                'is_adult' => false,
                'upload_date' => now(),
                'updated_at' => now(),
            ]);

            $album->images()->attach($image->image_id);
        }

        $album->last_image_added_at = now();
        $album->save();
    }

    protected function randomComment()
    {
        $comments = [
            'Очень красиво!',
            'Замечательная работа.',
            'Вдохновляет!',
            'Супер!',
            'Хотел бы так же научиться.',
            'Класс!',
            'Потрясающе.',
        ];
        return $comments[array_rand($comments)];
    }
}