<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Куда перенаправлять пользователей после регистрации.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Создание нового экземпляра контроллера.
     *
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Отображение формы регистрации.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Обработка запроса регистрации.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Вы можете автоматически войти в систему после регистрации или перенаправить на страницу входа
        // Auth::login($user);

        return redirect($this->redirectTo);
    }

    /**
     * Получение валидатора для входящего запроса регистрации.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'age' => ['required', 'integer', 'min:1'],
        ]);
    }

    /**
     * Создание нового пользователя после действительного запроса регистрации.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'age' => $data['age'],
            // Здесь вы можете добавить дополнительные поля, если они нужны
        ]);
    }
}

