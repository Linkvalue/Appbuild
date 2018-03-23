#!/bin/zsh

SCRIPT_NAME=$0

# Prerequisites
if ! type "jq" > /dev/null 2>&1; then
  echo "Error: jq command is missing" # only work on recent Bash versions and Zsh
  echo "Please install <jq> command to parse the json response of the api responses."
  echo "On Mac: brew install jq"
  echo "On Debian: apt-get install jq"
  exit 500
fi

# Constants
API_URL='http://local.appbuild.com/app_dev.php/api'
EP_APP_LIST='application/'
EP_CREATE_BUILD='application/:application_id/build/'

#############################################################################
# PARAMS
#############################################################################

######################################
# USAGE
function usage() {
  echo "Usage: $SCRIPT_NAME [-achpruv] file"
  echo " - a <application id>: Specify an application id"
  echo " - c <build comment>: Specify an build comment"
  echo " - h: Help, print this usage"
  echo " - p <password>: Specify a password"
  echo " - r <build release>: Specify a build version number"
  echo " - u <username>: Specify a username"
  echo " - v: verbose mode"
  echo ""
  echo "Note: All those options are customisable under the DEFAULT PARAMETERS section in the script"
  echo "Example:"
  echo " $SCRIPT_NAME -v -u superadmin@foo.fr -p superadmin -a 78 -r 1.2 -c \"A Comment on the build\" ./build/ios_v2.ipa"
}

######################################
# DEFAULT PARAMETERS
p_verbose='false'
p_interactive_fallback='false' # Allows the script to ask the user in prompt if the param is not passed
p_username='superadmin@superadmin.fr'
p_password='superadmin'
p_app_id=7
p_version='1.2'
p_comment=''

######################################
# OPTION PARAMETERS

while getopts 'u:p:a:f:r:c:vh' flag; do
  case "${flag}" in
    h) usage; exit 0 ;;
    v) p_verbose='true' ;;
    u) p_username="${OPTARG}" ;;
    p) p_password="${OPTARG}" ;;
    a) p_app_id="${OPTARG}" ;;
    r) p_version="${OPTARG}" ;;
    c) p_comment="${OPTARG}" ;;
    *) echo "Illegal option ${flag}"; usage; exit 400 ;;
  esac
done

shift $(( OPTIND - 1 ))
p_file="${1:-${BUILD_PATH}}"

if [ ! -f "$p_file" ]; then
  echo "File not found: ${p_file}"
  exit 404;
fi

filename=$(basename $p_file)

if test $p_verbose; then
  echo "File to upload: $p_file"
  echo "with the user: $p_username"
  echo "on the application: $p_app_id"
  echo "with the version: $p_version"
  echo "and the comment: $p_comment"
fi

#############################################################################
# FUNCTIONS
#############################################################################

function getAPIUrl() {
  echo "${API_URL}/$1"
}

function apiLogin() {
   username=$1
   password=$2

  #{
  #  "token": "[jwt token]",
  #}
  curl -s -X POST \
    $(getAPIUrl "login_check") \
    -H 'Cache-control: no-cache' \
    -H 'Content-type: multipart/form-data' \
    -d "{
      \"username\": ${username},
      \"password\": \"${password}\"
    }"
}

function apiCreateBuild() {
  jwt=$1
  app_id=$2
  build_version=$3
  build_comment=$4

  # Substitute the application_id
  build_creation_url="${EP_CREATE_BUILD//:application_id/$app_id}"

  #{
  #  "build_id": 10,
  #  "upload_location": "http://local.appbuild.com/app_dev.php/api/build/10/file"
  #}
  curl -s -X PUT \
    $(getAPIUrl ${build_creation_url}) \
    -H 'Cache-control: no-cache' \
    -H "Content-type: application/json" \
    -H "Authorization: Bearer ${jwt}" \
    -d "{
      \"version\": \"${build_version}\",
      \"comment\": \"${build_comment}\"
    }"
}

function apiUploadBuild() {
  jwt=$1
  upload_location=$2
  file_path=$3

  # 200
  #{}
  curl -s -w "%{http_code}" -X PUT \
    ${upload_location} \
    -H 'Cache-control: no-cache' \
    -H "Authorization: Bearer ${jwt}" \
    --data-binary "@${file_path}"
}

#############################################################################
# MAIN
#############################################################################

# Get the jwt to upload the build
echo ""
echo -n "Asking for authentication token: "
jwt=`apiLogin ${p_username} ${p_password} | jq -r ".token"`

if [ $? -eq 0 ]; then
  echo "done"
else
  echo "Error: Something wrong happened with authentication ";
  echo "Please make sure your credentials are ok"
  exit 403
fi

# Create the build with
#   - jwt: the authentication token
#   - app_id: the application id (ex: 7)
#   - build_version: the version of the build (ex: 1.2)
#   - build_comment: a comment on the new build (ex: "A fix on the offline mode")
echo -n "Creation of the build: "
api_create_build_ret=`apiCreateBuild ${jwt} ${p_app_id} ${p_version} ${p_comment}`
upload_location=`echo ${api_create_build_ret} | jq -r ".upload_location"`

if [ $? -eq 0 ] && [ "$upload_location" != "null" ]; then
  echo "done"
  echo "$upload_location"
else
  echo "Error: Something wrong happened with build creation";
  echo "Please make sure that the application exist and the version is correct"
  echo "${api_create_build_ret}"
  exit 530
fi

# Upload the build with
#   - jwt: the authentication token
#   - upload_location: the upload url got from apiCreateBuild
#   - file_path: the file's path
echo -n "Upload '${filename}' at ${upload_location}: "
httpcode=`apiUploadBuild ${jwt} ${upload_location} ${p_file}`
httpcode=${httpcode//{*}/}

if [ $httpcode -eq 200 ]; then
  echo "Build added with success :)"
else
  echo "Error: Something wrong happen :/ Please contact the Appbuild Administrator"
  exit 531
fi

exit 0
