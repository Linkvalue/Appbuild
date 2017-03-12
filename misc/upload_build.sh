#!/bin/sh

API_URL='http://majoraotastore.dev/app_dev.php/api'
EP_APP_LIST='application/'
EP_CREATE_BUILD='application/{application_id}/build/'

function getAPIUrl() {
  echo "${API_URL}/$1"
}

echo "Please enter the application id (digits) :"
read app_id

echo "Please enter the version : "
read build_version

echo "Please add a comment or just press enter :"
read build_comment

BUILD_CREATION_URL="${EP_CREATE_BUILD/\{application_id\}/$app_id}"

curl -H 'Content-Type: application/json' -X PUT -d '{"version":'$build_version',"comment":"'$build_comment'"}' $(getAPIUrl $BUILD_CREATION_URL)

#curl $(getAPIUrl $EP_APP_LIST)
#/api/application/{application_id}/build

#curl -X POST "${API_URL}${CREATE_BUILD_ENDPOINT}" --data @data.txt 
