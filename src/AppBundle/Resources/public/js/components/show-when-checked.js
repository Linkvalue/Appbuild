import $ from 'jquery';

const showWhenChecked = ($el) => {
  if ($el.prop('checked')) {
    $(`#${$el.attr('data-show-when-checked')}`).show();
  } else {
    $(`#${$el.attr('data-show-when-checked')}`).hide();
  }
};
const $inputs = $('input[data-show-when-checked]');

$inputs.each((idx, elem) => {
  const $input = $(elem);
  const $inputsWithSameName = $(`input[name="${$input.attr('name')}"]`);

  // input init
  showWhenChecked($input);

  // watch changes on inputs with same name
  $inputsWithSameName.on('change', () => {
    showWhenChecked($input);
  });
});
