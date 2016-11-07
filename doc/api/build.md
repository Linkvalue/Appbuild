# API - Build

## Get build

### Request

```
GET /api/build/{id}
```

### Response

```json
{
  "id": 1,
  "label": "Test application iOS [1.5.2]",
  "version": "1.5.2",
  "comment": "This is a comment for this build.\nWith a line break.\nAnd another one.\n",
  "is_latest": true,
  "download_link": "http://app-build.dev/app_dev.php/fr/application/1/build/1/download",
  "application": {
    "id": 1
  }
}
```

## List application builds

### Request

```
GET /api/application/{application_id}/build
```

### Response

```json
[
  {
    "id": 1,
    "label": "Test application iOS [1.5.2]",
    "version": "1.5.2",
    "comment": "This is a comment for this build.\nWith a line break.\nAnd another one.\n",
    "is_latest": true,
    "download_link": "http://app-build.dev/app_dev.php/fr/application/1/build/1/download",
    "application": {
      "id": 1
    }
  }
]
```

## Get application latest build

Note: This one has a smaller footprint in order to be used by devices to check for newer application build.

### Request

```
GET /api/application/{application_id}/build/latest
```

### Response

```json
{
  "id": 1,
  "version": "1.5.2",
  "comment": "This is a comment for this build.\nWith a line break.\nAnd another one.\n",
  "download_link": "http://app-build.dev/app_dev.php/fr/application/1/build/1/download"
}
```
