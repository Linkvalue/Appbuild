$(document).ready(function () {

  // animation on inputs

  var $form = $('form');

  $form.each(function() {
    $(this).on('change', function() {
      var $formInput = $(this).find('.input-wrap__input');
      $formInput.each(function() {
        if($(this).val().length != 0) {
          $(this).addClass('active');
        } else {
          $(this).removeClass('active');
        }
      })
    })
  });

  // animation on login button if form is valid
  // TODO : check if the form is valid

  if ($('[data-button-login]').length != '') {
    var buttonPos = $('[data-button-login]').offset();
    $('[data-button-login]').after('<div class="loader-login"></div>');
    $('.loader-login').css('top', buttonPos.top + 'px');

    $('[data-button-login]').click(function(e) {
      e.preventDefault();
      $(this).next('.loader-login').addClass('valid');
    });
  }



  var $elementAppear = $('[data-anim-appear]');
  var $elementNumber = $elementAppear.length;

  function appear() {
    $elementAppear.css({
      'opacity': 1,
      'transform': 'translateY(0)'
    })
  }

  for (var i = 0; i <= $elementNumber; i++) {
    $elementAppear.eq(i).css('transition', 'all .3s ease-out .' + (i + 4) + 's'  )
  };

  setTimeout(appear, 200);


});
