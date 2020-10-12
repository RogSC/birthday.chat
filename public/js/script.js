$(document).ready(function () {
    let stepBtn = $('.js-init-next-step'),
        stepNumber = 0;

    stepBtn.click(function () {
        if (stepNumber < 2) {
            stepNumber++;
            nextStep(stepNumber);
        } else {
            $('form').submit();
        }
    });
});

function nextStep(stepNumber) {
    let stepListItem = $('.right-column__item'),
        stepBody = $('.form__step'),
        stepNubmerSpan = $('.footer-steps__number'),
        stepBtn = $('.form-btn-submit');

    stepListItem.removeClass('right-column__item_fat').eq(stepNumber).addClass('right-column__item_fat');
    stepBody.hide().eq(stepNumber).show();
    stepNubmerSpan.text(stepNumber + 1);

    if (stepNumber === 2) {
        stepBtn.text('Сохранить');
    }
}
