var $fileInput = $('input[type="file"]');

$fileInput.each(function() {
  const $fileInputItem = $(this);
  const fileInputId = $fileInputItem.attr('id');
  const $fileInputResult = $(`[data-input-file="${fileInputId}"]`);

  $fileInputItem.on( "change", function() {
    const file = this.files[0].name;
    $fileInputResult.html(file);
    $fileInputResult.parent('.file-label__result').addClass('js-valid');
  });
})
