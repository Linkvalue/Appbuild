import $ from 'jquery';

$('[data-select-display]').each((idx, elem) => {
  const $selectDisplay = $(elem);
  const $input = $(`#${$selectDisplay.attr('data-input')}`);
  const $selectOptions = $(`#${$selectDisplay.attr('data-toggle')}`);
  const $selectOptionItem = $selectOptions.children('li');
  const $initiallySelectedItem = $selectOptionItem.filter('[data-selected="true"]:first');
  const updateSelect = ($selected) => {
    $input.val($selected.attr('data-value'));
    $selectDisplay.html($selected.html());
  };

  // Initially selected item
  if ($initiallySelectedItem.length) {
    updateSelect($initiallySelectedItem);
  }

  // Watch for selected item
  $selectOptionItem.on('click', (e) => {
    updateSelect($(e.currentTarget));
    $selectOptions.foundation('close');
  });
});
