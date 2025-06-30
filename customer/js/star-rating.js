document.addEventListener('DOMContentLoaded', function () {
    const ratingSlider = document.getElementById('rating');
    const ratingValueSpan = document.getElementById('rating-value');

    if (ratingSlider && ratingValueSpan) {
        ratingValueSpan.textContent = ratingSlider.value;
        ratingSlider.addEventListener('input', function () {
            ratingValueSpan.textContent = this.value;
        });
    }
});
