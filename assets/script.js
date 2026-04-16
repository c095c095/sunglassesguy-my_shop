var swiper = new Swiper('.swiper', {
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    loop: true,
    simulateTouch: true,
    touchRatio: 1,
    slidesPerView: 2,
    breakpoints: {
        576: {
          slidesPerView: 4,
        },
        992: {
          slidesPerView: 6,
        }
    }
});