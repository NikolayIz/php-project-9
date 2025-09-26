<?php

namespace App;

use Valitron\Validator;

class UrlValidator
{
    /**
     * Проверяет данные формы.
     *
     * @param array $data Входные данные, например ['name' => 'https://example.com']
     * @return array Массив ошибок (пустой если всё ок)
     */
    public static function validate(array $data): array
    {
        // Указываем, что будем валидировать поле "name"
        $v = new Validator($data);

        // Правила валидации
        $v->rule('required', 'name')->message('URL не должен быть пустым');
        $v->rule('url', 'name')->message('Некорректный URL');
        $v->rule('lengthMax', 'name', 255)->message('URL не должен превышать 255 символов');

        // Проверяем
        if ($v->validate()) {
            return []; // ✅ Нет ошибок
        }

        // Приведём ошибки к простому формату
        // Valitron возвращает массив с ключами полей, например ['name' => ['Ошибка1', 'Ошибка2']]
        $errors = [];
        foreach ($v->errors() as $fieldErrors) {
            $errors = array_merge($errors, $fieldErrors);
        }
        return $errors;
    }
}
