const container = document.getElementById('product-images-container');
const prototypeTemplate = document.getElementById('image-prototype-template');
const addImageButton = document.getElementById('js-add-image');

function initGallery() {
    if (!container || !prototypeTemplate || !addImageButton) {
        return;
    }

    let index = container.children.length;

    function markForRemoval(item) {
        const checkbox = item.querySelector('input[type="checkbox"]');
        const fileInput = item.querySelector('input[type="file"]');
        const removeButton = item.querySelector('.js-image-remove');

        if (checkbox) {
            checkbox.checked = true;
        }
        if (fileInput) {
            fileInput.disabled = true;
        }
        if (removeButton) {
            removeButton.disabled = true;
            removeButton.title = 'Будет удалена после сохранения';
        }
        item.classList.add('opacity-50');
    }

    function attachPreview(fileInput) {
        if (!fileInput) {
            return;
        }

        fileInput.addEventListener('change', () => {
            const item = fileInput.closest('[data-image-item]');
            const file = fileInput.files && fileInput.files[0];
            if (!item || !file) {
                return;
            }

            const url = URL.createObjectURL(file);
            const preview = item.querySelector('.js-image-preview');
            const placeholder = item.querySelector('.js-image-placeholder');

            if (preview) {
                preview.src = url;
            } else if (placeholder) {
                const img = document.createElement('img');
                img.className = 'js-image-preview';
                img.style.width = '140px';
                img.src = url;
                img.alt = '';
                placeholder.replaceWith(img);
            }
        });
    }

    document.querySelectorAll('[data-image-item]').forEach((item) => {
        item.querySelector('.js-image-remove')?.addEventListener('click', () => markForRemoval(item));
        attachPreview(item.querySelector('input[type="file"]'));
    });

    addImageButton.addEventListener('click', () => {
        const innerHtml = prototypeTemplate.innerHTML.replaceAll('__name__', String(index));
        const item = document.createElement('div');
        item.className = 'col-auto';
        item.setAttribute('data-image-item', '');
        item.innerHTML = `
            <div class="border rounded p-2 position-relative">
                <div class="js-image-placeholder bg-light rounded d-flex align-items-center justify-content-center"
                     style="width: 140px; height: 100px">
                    <i class="bi bi-image fs-1 text-secondary"></i>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-1 js-image-remove"
                        title="Удалить">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
                ${innerHtml}
            </div>`;

        container.appendChild(item);
        index += 1;

        item.querySelector('.js-image-remove')?.addEventListener('click', () => markForRemoval(item));
        attachPreview(item.querySelector('input[type="file"]'));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGallery);
} else {
    initGallery();
}
