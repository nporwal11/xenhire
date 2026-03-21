jQuery(document).ready(function ($) {

    // Odometer animation function
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);

            // Easing function for smooth odometer effect
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);

            obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString();

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    $('.billing-btn').on('click', function () {
        // Prevent clicking if already active
        if ($(this).hasClass('active')) return;

        $('.billing-btn').removeClass('active');
        $(this).addClass('active');

        var period = $(this).data('period');
        var periodText = period === 'monthly' ? 'per<br>month' : 'per<br>year';

        // Update prices with animation
        $('.package-item').each(function () {
            var $amount = $(this).find('.amount');
            var $period = $(this).find('.period');

            var currentPrice = parseInt($amount.text().replace(/,/g, ''));
            var targetPrice = parseInt($amount.data(period));

            // Animate the number
            animateValue($amount[0], currentPrice, targetPrice, 1000);

            // Update period text
            $period.html(periodText);
        });
    });
});
