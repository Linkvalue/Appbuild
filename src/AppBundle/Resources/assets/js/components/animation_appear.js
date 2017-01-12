const $elementAppear = $('[data-anim-appear]');

function appear() {
  $elementAppear
    .css({
      'opacity': 1,
      'transform': 'translate3d(0, 0, 0)'
    })
}

let i = 0;

for (let elem of $elementAppear) {
  i += 100;
  $(elem).css('transition-delay', `${i}ms`);
}

setTimeout(appear, 200);
