import $ from 'jquery';

const currentUserId = $('form[name="majoraotastore_application"]').data('current-user-id');
const applicationSupportIOS = $('form[name="majoraotastore_application"]').data('application-support-ios');

// Check and hide current user from Users list
// (if this was unchecked by mistake, current user would lost his ability to act on this application)
$('#majoraotastore_application_users_' + currentUserId)
  .prop('checked', true)
  .closest('.checkbox').hide()
;

// Set up application packageName field visibility.
const $supportField = $('#majoraotastore_application_support');
setupPackageNameVisibility($supportField);
$supportField.change(function() {
  setupPackageNameVisibility($supportField);
});

/**
 * Show package name field depending on application support.
 *
 * @param {object} $supportField
 */
function setupPackageNameVisibility($supportField) {
  if ($supportField.val() === applicationSupportIOS) {
    $('#majoraotastore_application_packageName').prop('disabled', false)
      .closest('.form-group').removeClass('hidden')
    ;
  } else {
    $('#majoraotastore_application_packageName').prop('disabled', true)
      .closest('.form-group').addClass('hidden')
    ;
  }
}
