import $ from 'jquery';

Foundation.Abide.defaults.validators['same_value'] = ($el) => {
  return $(`#${$el.attr('data-same-as')}`).val() === $el.val();
};
