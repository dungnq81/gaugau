jQuery(function ($) {
    const wpg__image = $('.wpg__image');
    wpg__image.find('a').on('click', function (e) {
        e.preventDefault();
        $(this).next('.image-popup').trigger('click');
    });

    const wpg__thumb = $('.wpg__thumb');
    wpg__thumb.find('a').on('click', function (e) {
        e.preventDefault();
    });
});
