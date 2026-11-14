// Potwierdzenie przed wysłaniem formularza z atrybutem data-confirm (np. usuwanie).
document.addEventListener('submit', function (event) {
    var message = event.target.getAttribute('data-confirm');

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

// Formularz użytkownika: pokazuje tylko pola pasujące do wybranej roli.
document.addEventListener('DOMContentLoaded', function () {
    var roleSelect = document.querySelector('[data-role-select]');

    if (!roleSelect) {
        return;
    }

    function refresh() {
        document.querySelectorAll('[data-role-field]').forEach(function (block) {
            var visible = block.getAttribute('data-role-field') === roleSelect.value;
            block.hidden = !visible;

            block.querySelectorAll('select').forEach(function (select) {
                select.required = visible;
            });
        });
    }

    roleSelect.addEventListener('change', refresh);
    refresh();
});
