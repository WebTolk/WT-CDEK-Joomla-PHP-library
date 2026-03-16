# Entity: oauth

Class: `Webtolk\Cdekapi\Entities\OauthEntity`

## Methods

### getOAuthToken()

```php
public function getOAuthToken(array $request_options = []): array
```

POST /v2/oauth/token

Получение токена авторизации

**Описание:**
Взаимодействие с сервисом требует клиентской авторизации. Авторизация обеспечивается с применением
протокола OAuth 2.0. Метод предназначен для получения токена авторизации.

Источник: https://apidoc.cdek.ru/#tag/auth/operation/getOAuthToken

@param   array{
            grant_type?: string,
            client_id?: string,
            client_secret?: string
        }  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->oauth()->getOAuthToken([
    'grant_type'    => 'client_credentials',
    'client_id'     => 'your_client_id',
    'client_secret' => 'your_client_secret',
]);
```
