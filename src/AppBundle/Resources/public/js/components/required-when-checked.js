import $ from 'jquery';

const requireWhenChecked = ($el) => {
  if ($el.prop('checked')) {
    $(`#${$el.attr('data-required-when-checked')}`).attr('required', 'required');
  } else {
    $(`#${$el.attr('data-required-when-checked')}`).removeAttr('required');
  }
};
const $inputs = $('input[data-required-when-checked]');

$inputs.each((idx, elem) => {
  const $input = $(elem);
  const $inputsWithSameName = $(`input[name="${$input.attr('name')}"]`);

  // input init
  requireWhenChecked($input);

  // watch changes on inputs with same name
  $inputsWithSameName.on('change', () => {
    requireWhenChecked($input);
  });
});
