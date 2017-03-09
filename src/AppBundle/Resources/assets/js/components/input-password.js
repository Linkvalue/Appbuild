function checkPassword() {

  // Foundation documentation
  // http://foundation.zurb.com/sites/docs/abide.html

  Foundation.Abide.defaults.validators['check_password'] =
  function($el,required,parent) {

    if (!required) return true;

    const equal = $('#'+$el.attr('data-equal-to')).val();
    const to = $el.val();

    return (equal == to);
  };
};


export { checkPassword }
