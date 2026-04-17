# OauthEntity

`Webtolk\Cdekapi\Entities\OauthEntity` — это низкоуровневая Entity для ручного получения OAuth-токена. В обычной интеграции она нужна редко, потому что библиотека сама авторизуется через `CdekRequest`. Но для отладки и нестандартных сценариев метод полезен.

Получение Entity:

```php
$oauth = $cdek->oauth();
```

## `getOAuthToken(array $request_options = []): array`

**Сигнатура**

```php
public function getOAuthToken(array $request_options = []): array
```

**REST API**

`POST /v2/oauth/token`

**Что делает**

Запрашивает OAuth-токен напрямую.

**Параметры**

Обязательны все три поля:

- `grant_type`;
- `client_id`;
- `client_secret`.

На практике `grant_type` должен быть `client_credentials`.

**Что возвращает**

Массив с `access_token`, `token_type`, `expires_in` или ошибкой.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$tokenData = $cdek->oauth()->getOAuthToken([
    'grant_type'    => 'client_credentials',
    'client_id'     => 'your_client_id',
    'client_secret' => 'your_client_secret',
]);
```
