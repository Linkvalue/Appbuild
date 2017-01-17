$(document).ready(function () {

    // animation on inputs

    const $forms = $('form');

    $forms.each(function() {
        const $form = $(this);
        const $formInputs = $form.find('.input-wrap__input');

        $form.on('change', () => {
            $formInputs.each(function() {
                const $input = $(this);
                $input.toggleClass('active', $input.val().length > 0);
            });
        });
    });

    // animation on login button if form is valid
    // TODO : check if the form is valid

    const $buttonLogin = $('[data-button-login]');

    if ($buttonLogin.length > 0) {
        const buttonPos = $buttonLogin.offset();
        const $loader = $('<div class="loader-login"></div>');

        $loader.css('top', buttonPos.top + 'px');

        $buttonLogin.after($loader);

        $buttonLogin.click((e) => {
            e.preventDefault();
            $loader.addClass('valid');
        });
    }

    const $elementAppear = $('[data-anim-appear]');

    function appear() {
        $elementAppear.css({
            'opacity': 1,
            'transform': 'translateY(0)'
        });
    }

    $elementAppear.each(function(idx) {
        $(this).css('transition', `all .3s ease-out .${ idx + 4 }s`);
    });

    setTimeout(appear, 200);
});
