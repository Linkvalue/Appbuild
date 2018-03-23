import $ from 'jquery';
import Routing from 'fos-js-routing';
import Translator from 'bazinga-translator';
import 'fine-uploader/jquery.fine-uploader/jquery.fine-uploader.min.js';

const locale = $('html').attr('lang');
const $form = $('form[name="appbuild_application"]');

const commonFineUploaderConfig = {
  template: 'upload-container-template',
  multiple: false,
  validation: {
    allowedExtensions: ['jpeg', 'jpg', 'png'],
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
  deleteFile: {
    enabled: true,
    endpoint: Routing.generate('appbuild_admin_application_delete_image', {
      _locale: locale,
    }),
    method: 'POST',
    deletingFailedText: Translator.trans('admin.upload.message.delete_failed'),
    deletingStatusText: Translator.trans('admin.upload.message.deleting'),
  },
};

$('#appbuild_application_displayImageFile').fineUploader(Object.assign(
  {},
  commonFineUploaderConfig,
  {
    request: {
      endpoint: Routing.generate('appbuild_admin_application_display_image_upload', {
        _locale: locale,
      }),
      inputName: 'displayImageFile',
    },
  },
  !$form.data('id') ? {} : {
    session: {
      endpoint: Routing.generate('appbuild_admin_application_get_display_image', {
        _locale: locale,
        id: $form.data('id'),
      }),
    },
  }
)).on('submitted', () => {
  // Remove the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').hide();
}).on('cancel', () => {
  // Reset the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').show();
}).on('complete', (event, id, name, responseJSON) => {
  if (responseJSON.success) {
    // Set filename field
    $('#appbuild_application_displayImageFilename').val(responseJSON.filename);
  }
}).on('sessionRequestComplete', (event, files) => {
  if (files[0]) {
    // Set filename field
    $('#appbuild_application_displayImageFilename').val(files[0].name);
  }
}).on('deleteComplete', () => {
  // Clear filename field
  $('#appbuild_application_displayImageFilename').val('');
}).on('error', (event, id, name, errorReason) => {
  // Clear filename field
  $('#appbuild_application_displayImageFilename').val('');
  // Show error message
  alert(errorReason);
});

$('#appbuild_application_fullSizeImageFile').fineUploader(Object.assign(
  {},
  commonFineUploaderConfig,
  {
    request: {
      endpoint: Routing.generate('appbuild_admin_application_full_size_image_upload', {
        _locale: locale,
      }),
      inputName: 'fullSizeImageFile',
    },
  },
  !$form.data('id') ? {} : {
    session: {
      endpoint: Routing.generate('appbuild_admin_application_get_full_size_image', {
        _locale: locale,
        id: $form.data('id'),
      }),
    },
  }
)).on('submitted', () => {
  // Remove the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').hide();
}).on('cancel', () => {
  // Reset the ability to send files
  $form.find('.qq-upload-button-selector, qq-upload-drop-area-selector').show();
}).on('complete', (event, id, name, responseJSON) => {
  if (responseJSON.success) {
    // Set filename field
    $('#appbuild_application_fullSizeImageFilename').val(responseJSON.filename);
  }
}).on('sessionRequestComplete', (event, files) => {
  if (files[0]) {
    // Set filename field
    $('#appbuild_application_fullSizeImageFilename').val(files[0].name);
  }
}).on('deleteComplete', () => {
  // Clear filename field
  $('#appbuild_application_fullSizeImageFilename').val('');
}).on('error', (event, id, name, errorReason) => {
  // Clear filename field
  $('#appbuild_application_fullSizeImageFilename').val('');
  // Show error message
  alert(errorReason);
});
