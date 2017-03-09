$('[data-select-display]').each((idx, elem) => {

  const $selectDisplay = $(elem);
  const id =  $selectDisplay.attr('data-toggle');
  const $selectOptions = $(`[data-select-options][id="${id}"]`);

  const $selectOptionItem = $selectOptions.children('li');

  $selectOptionItem.on('click', (e) => {
    $selectDisplay.html($(e.currentTarget).html());
    $selectOptions.foundation('close');
  });
});
