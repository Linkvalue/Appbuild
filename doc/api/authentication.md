# API - Login

## Get a token

### Request

```
POST /api/login_check
```
```json
{
  "username": "YOUR_USERNAME", // like "superadmin@superadmin.fr" in dev environment
  "password": "YOUR_PASSWORD" // like "superadmin" in dev environment
}
```

### Response

200 OK

```json
{"token":"azerty1234.very_long_token.4321azerty"}
```

## Use a token

When calling any API route (except the `/api/login_check` one of course), you'll have to send a token as a `Authorization` header with the following value: `Bearer my_token`.

### Request example for api/application

```
GET /api/application
Authorization: Bearer azerty1234.very_long_token.4321azerty
```

## Authentication related API responses

### 401 - Token not found

When you don't provide a token.

```json
{
  "code": 401,
  "message": "JWT Token not found"
}
```

### 401 - Expired Token

When your token is expired.

```json
{
  "code": 401,
  "message": "Expired JWT Token"
}
```
