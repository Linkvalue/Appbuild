$('[data-select-display]').each(function() {

  var $selectDisplay = $(this);
  var id = $(this).attr('data-toggle');
  var $selectOptions = $(`[data-select-options][id="${id}"]`);

  var $selectOptionItem = $selectOptions.children('li');

  $selectOptionItem.click(function() {
    $selectDisplay.html($(this).html());
    $selectOptions.foundation('close');

  });
});
