<?php

return [
    'required' => ':attribute обязательно для заполнения.',
    'max' => 'Максимальная длина - :max символов.',
    'min' => 'Минимальная длина - :min символов.',
    'string' => 'Поле :attribute должно быть строкой.',

    'password' => [
        'numbers' => ':attribute должен включать хотя бы одну цифру.',
        'symbols' => ':attribute должен включать хотя бы один спец-символ.',
    ],

    'custom' => [
        'email' => [
            'required' => 'Email обязательно для заполнения.',
            'email' => 'Введите корректный email.',
            'unique' => 'Такой email уже существует.',
            'exists' => 'Переданный email не был зарегистрирован.',
        ],
        'password' => [
            'min' => ':attribute должен быть минимум :min символов.',
            'confirmed' => 'Пароли не совпадают.',
        ],
        'unique_nickname' => [
            'regex' => ':attribute должен включать только латиницу, _ и -',
            'unique' => ':attribute уже существует.',
        ],
    ],

    'attributes' => [
        'email' => 'Email',
        'password' => 'Пароль',
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'middle_name' => 'Отчество',
        'unique_nickname' => 'Уникальный никнейм',
        'reset_token' => 'Токен сброса пароля',
    ],
];
