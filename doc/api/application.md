# API - Application

## List applications

### Request

```
GET /api/application
```

### Response

```json
[
  {
    "id": 1,
    "label": "Test application iOS",
    "slug": "test-application-ios",
    "support": "ios",
    "package_name": "com.link-value.test",
    "builds": [
      {
        "id": 1,
        "version": "1.5.2",
        "is_latest": true
      }
    ]
  }
]
```

## Get application

### Request

```
GET /api/application/{id}
```

### Response

```json
{
  "id": 1,
  "label": "Test application iOS",
  "slug": "test-application-ios",
  "support": "ios",
  "package_name": "com.link-value.test",
  "builds": [
    {
      "id": 1,
      "version": "1.5.2",
      "is_latest": true
    }
  ]
}
```
