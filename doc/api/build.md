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
  "download_link": "http://my-appbuild.domain/fr/application/1/build/1/download",
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
    "download_link": "http://my-appbuild.domain/fr/application/1/build/1/download",
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
  "download_link": "http://my-appbuild.domain/fr/application/1/build/1/download"
}
```

## Create a new build

### Request

Note : The route return an url to upload and it can be somewhere else than the API.

```
PUT /api/application/{application_id}/build
```
```json
{
  "version": "1.5.2",
  "comment": "This is a comment for this build.\nWith a line break.\nAnd another one.\n"
}
```

### Response

200 OK

```json
{
  "build_id": 4,
  "upload_location": "http://my-appbuild.domain/api/build/4/file"
}
```

## Upload a file for a build

Note: This route is given by `Create a new build` through `upload_location` response field.

### Request

```
PUT /api/build/{build_id}/file
```

```
Binary file in body
```

### Response

200 OK
