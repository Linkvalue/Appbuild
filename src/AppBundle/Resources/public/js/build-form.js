import $ from 'jquery';
import Routing from 'fos-js-routing';
import Translator from 'bazinga-translator';
import 'fine-uploader/jquery.fine-uploader/jquery.fine-uploader.min.js';
import 'fine-uploader/jquery.fine-uploader/fine-uploader-new.min.css';

const applicationId = $('form[name="majoraotastore_build"]').data('application-id');
const $uploadContainer = $('#upload-container');

$uploadContainer.fineUploader({
  template: 'upload-container-template',
  request: {
    endpoint: Routing.generate('majoraotastore_admin_build_upload', {
      application_id: applicationId,
    }),
    inputName: 'build_file'
  },
  multiple: false,
  validation: {
    allowedExtensions: ['ipa', 'apk'],
    itemLimit: 1
  },
  text: {
    defaultResponseError: Translator.trans('admin.upload.message.default'),
    failUpload: Translator.trans('admin.upload.message.upload_failure'),
    formatProgress: Translator.trans('admin.upload.message.progress_bar'),
    waitingForResponse: Translator.trans('admin.upload.message.waiting_for_response')
  },
  messages: {
    typeError: Translator.trans('admin.upload.message.invalid_extension'),
    onLeave: Translator.trans('admin.upload.message.on_leave'),
    tooManyItemsError: Translator.trans('admin.upload.message.too_many_items')
  }
}).on('error', function (event, id, name, errorReason) {
  // Clear filename field
  $('#majoraotastore_build_filename').val('');
  // Show error message
  alert(errorReason);
}).on('complete', function (event, id, name, responseJSON) {
  if (responseJSON.success) {
    // Set filename field
    $('#majoraotastore_build_filename').val(responseJSON.filename);
  }
});
