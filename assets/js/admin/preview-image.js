import jQuery from "jquery";

// Создаем локальный синоним $ из документации
const $ = jQuery;

function initPreviewImage() {
    $(document).ready(function() {
        function handleFileSelect(evt) {
            let file = evt.target.files;
            let f = file[0];
            if (!f.type.match('image.*')) {
                alert("Данный файл не изображение!!!");
            }
            let reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    let img = document.getElementById("preview_img");

                    img.src = e.target.result;
                };
            })(f);
            reader.readAsDataURL(f);
        }

        document.getElementById('image').addEventListener('change', handleFileSelect, false);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPreviewImage);
} else {
    initPreviewImage();
}
