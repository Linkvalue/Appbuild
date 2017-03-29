#!/bin/sh

API_URL='http://majoraotastore.dev/app_dev.php/api'
EP_APP_LIST='application/'
EP_CREATE_BUILD='application/{application_id}/build/'

function getAPIUrl() {
  echo "${API_URL}/$1"
}

function apiLogin() {
  echo "Please enter the application login"
  read app_login

  echo "Please enter the application password"
  read app_password

  # return {"token": "[token]"}
  # return token from curl api/login
}

function apiCreateBuild() {

  jwt=$1

  echo "Please enter the application id (digits) :"
  read app_id

  echo "Please enter the version : "
  read build_version

  echo "Please add a comment or just press enter :"
  read build_comment

  build_creation_url="${EP_CREATE_BUILD/\{application_id\}/$app_id}"

  curl \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer ${jwt}" \
    -X PUT \
    -d '{"version":'$build_version',"comment":"'$build_comment'"}' $(getAPIUrl $build_creation_url)

  # return {"build_id":8,"upload_location":"http:\/\/majoraotastore.dev\/app_dev.php\/api\/build\/8\/file"}
  # return upload_location
}

function apiUploadBuild() {

  jwt=$1
  upload_location=$2
  file_path=$3

  #curl $(getAPIUrl $EP_APP_LIST)
  #/api/application/{application_id}/build

  #curl -X POST "${API_URL}${CREATE_BUILD_ENDPOINT}" --data @data.txt
  echo "Please specify file"
  read file_path
}

jwt=`apiLogin`
upload_location=`apiCreateBuild ${jwt}`
apiUploadBuild ${jwt} ${upload_location} ${file_path}

