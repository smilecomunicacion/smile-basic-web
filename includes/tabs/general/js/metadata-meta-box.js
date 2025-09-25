(function () {
        'use strict';

        const initialiseCounter = function (field) {
                const targetId = field.id;
                if (!targetId) {
                        return;
                }

                const counter = document.querySelector('[data-sbwscf-count-for="' + targetId + '"]');
                if (!counter) {
                        return;
                }

                const update = function () {
                        counter.textContent = String(field.value.length);
                };

                update();
                field.addEventListener('input', update);
        };

        document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-sbwscf-count-field]').forEach(initialiseCounter);
        });
}());
