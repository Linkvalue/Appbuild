import $ from 'jquery';
import Routing from 'fos-js-routing';
import Translator from 'bazinga-translator';
import 'fine-uploader/jquery.fine-uploader/jquery.fine-uploader.min.js';

const $form = $('form[name="appbuild_build"]');
const $uploadContainer = $('#appbuild_build_file');

$uploadContainer.fineUploader({
  template: 'upload-container-template',
  request: {
    endpoint: Routing.generate('appbuild_admin_build_upload', {
      application_id: $form.data('application-id'),
      _locale: $('html').attr('lang'),
    }),
    inputName: 'build_file',
  },
  multiple: false,
  validation: {
    allowedExtensions: ['ipa', 'apk'],
    itemLimit: 1,
  },
  text: {
    defaultResponseError: Translator.trans('admin.upload.message.default'),
    failUpload: Translator.trans('admin.upload.message.upload_failure'),
    formatProgress: Translator.trans('admin.upload.message.progress_bar'),
    waitingForResponse: Translator.trans('admin.upload.message.waiting_for_response'),
  },
  messages: {
    typeError: Translator.trans('admin.upload.message.invalid_extension'),
    onLeave: Translator.trans('admin.upload.message.on_leave'),
    tooManyItemsError: Translator.trans('admin.upload.message.too_many_items'),
  },
}).on('submitted', () => {
  // Remove the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').hide();
}).on('cancel', () => {
  // Reset the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').show();
}).on('error', (event, id, name, errorReason) => {
  // Clear filename field
  $('#appbuild_build_filename').val('');
  // Show error message
  alert(errorReason);
}).on('complete', (event, id, name, responseJSON) => {
  if (responseJSON.success) {
    // Set filename field
    $('#appbuild_build_filename').val(responseJSON.filename);
  }
});
