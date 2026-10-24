import jQuery from "jquery";
import select2 from "select2";

// Создаем локальный синоним $ из документации
const $ = jQuery;

// Принудительно передаем jQuery в Select2
select2($);

function initSelect2() {
    $(document).ready(function() {
        $('.select2-category-filter').select2({
            theme: "bootstrap-5",
            placeholder: 'Выберите категорию...',
            allowClear: true,
            templateResult: function (data) {
                if (!data.element) {
                    return data.text;
                }
                let $element = $(data.element);
                let $wrapper = $('<span></span>');
                $wrapper.html($element.text());

                return $wrapper;
            }
        });

        $('.select2-status-filter').select2({
            theme: "bootstrap-5",
            placeholder: 'Выберите статус...',
            allowClear: true
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelect2);
} else {
    initSelect2();
}
