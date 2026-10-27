document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var navigation = document.querySelector('.primary-nav');
    if (toggle && navigation) {
        toggle.addEventListener('click', function () {
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            navigation.classList.toggle('is-open', !expanded);
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        function validateMatchingFields() {
            form.querySelectorAll('[data-match]').forEach(function (field) {
                var matchingField = document.getElementById(field.getAttribute('data-match'));
                field.setCustomValidity(matchingField && field.value !== matchingField.value ? 'The values do not match.' : '');
            });
        }

        form.addEventListener('input', validateMatchingFields);
        form.addEventListener('submit', function (event) {
            validateMatchingFields();
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });

    document.querySelectorAll('img[data-fallback-image]').forEach(function (image) {
        image.addEventListener('error', function () {
            var placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder';
            placeholder.textContent = 'TravelMate destination';
            image.parentNode.replaceChild(placeholder, image);
        }, { once: true });
    });
});
