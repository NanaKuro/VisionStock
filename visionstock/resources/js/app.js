const menuButtons = document.querySelectorAll('[data-menu-button]');
const sidebar = document.querySelector('[data-sidebar]');
const overlay = document.querySelector('[data-sidebar-overlay]');

function setSidebarOpen(isOpen) {
    sidebar?.classList.toggle('translate-x-0', isOpen);
    sidebar?.classList.toggle('-translate-x-full', !isOpen);
    overlay?.classList.toggle('hidden', !isOpen);
    menuButtons.forEach((button) => button.setAttribute('aria-expanded', String(isOpen)));
}

menuButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const isCloseButton = button.hasAttribute('data-menu-close');
        const isOpen = button.getAttribute('aria-expanded') !== 'true';
        setSidebarOpen(isCloseButton ? false : isOpen);
    });
});

overlay?.addEventListener('click', () => setSidebarOpen(false));

const productDialog = document.querySelector('[data-product-dialog]');
const productForm = document.querySelector('[data-product-form]');
const productMethod = document.querySelector('[data-product-method]');
const productId = productForm?.querySelector('[name="product_id"]');
const productImage = productForm?.querySelector('[data-product-image]');
const productPreview = productForm?.querySelector('[data-product-preview]');
const productPlaceholder = productForm?.querySelector('[data-product-placeholder]');

function setProductPreview(source) {
    if (!productPreview || !productPlaceholder) {
        return;
    }

    productPreview.src = source;
    productPreview.classList.toggle('hidden', !source);
    productPlaceholder.classList.toggle('hidden', Boolean(source));
}

function openProductDialog(button) {
    if (!productDialog || !productForm || !productMethod || !productId) {
        return;
    }

    productForm.reset();
    productForm.action = productDialog.dataset.storeUrl;
    productMethod.value = 'POST';
    productId.value = '';
    productDialog.querySelector('[data-dialog-title]').textContent = 'Input Manual';
    productForm.querySelector('[name="name"]').value = '';
    productForm.querySelector('[name="description"]').value = '';
    productForm.querySelector('[name="stock"]').value = '0';
    productForm.querySelector('[name="category_id"]').value = '';
    setProductPreview('');

    if (button?.hasAttribute('data-product-edit')) {
        productForm.action = button.dataset.updateUrl;
        productMethod.value = 'PUT';
        productId.value = button.dataset.productId;
        productDialog.querySelector('[data-dialog-title]').textContent = 'Edit Stok';
        productForm.querySelector('[name="name"]').value = button.dataset.name;
        productForm.querySelector('[name="description"]').value = button.dataset.description;
        productForm.querySelector('[name="stock"]').value = button.dataset.stock;
        productForm.querySelector('[name="category_id"]').value = button.dataset.category;
        setProductPreview(button.dataset.image);
    }

    if (productDialog instanceof HTMLDialogElement && !productDialog.open) {
        productDialog.showModal();
    }
}

document.querySelectorAll('[data-product-create]').forEach((button) => {
    button.addEventListener('click', () => openProductDialog());
});

document.querySelectorAll('[data-product-edit]').forEach((button) => {
    button.addEventListener('click', () => openProductDialog(button));
});

document.querySelectorAll('[data-product-close]').forEach((button) => {
    button.addEventListener('click', () => {
        if (productDialog instanceof HTMLDialogElement) {
            productDialog.close();
        }
    });
});

productImage?.addEventListener('change', () => {
    const file = productImage.files?.[0];
    setProductPreview(file ? URL.createObjectURL(file) : '');
});

if (productDialog?.dataset.autoOpen === 'true') {
    if (productDialog instanceof HTMLDialogElement && productForm && productMethod) {
        if (productDialog.dataset.autoMethod === 'PUT') {
            productForm.action = productDialog.dataset.autoUpdateUrl;
            productMethod.value = 'PUT';
            productDialog.querySelector('[data-dialog-title]').textContent = 'Edit Stok';
        }

        productDialog.showModal();
    }
}

const stockImportForm = document.querySelector('[data-stock-import-form]');
const excelFile = stockImportForm?.querySelector('[data-excel-file]');
const dropzone = stockImportForm?.querySelector('[data-dropzone]');
const fileName = stockImportForm?.querySelector('[data-file-name]');
const uploadError = stockImportForm?.querySelector('[data-upload-error]');
const uploadProgress = stockImportForm?.querySelector('[data-upload-progress]');
const uploadSubmit = stockImportForm?.querySelector('[data-upload-submit]');

function updateExcelFile(file) {
    if (!fileName || !uploadError) {
        return false;
    }

    fileName.textContent = file?.name ?? 'Pilih sebuah file atau tahan dan lepaskan di sini.';
    uploadError.textContent = '';

    if (!file) {
        return false;
    }

    if (!file.name.toLowerCase().endsWith('.xlsx')) {
        uploadError.textContent = 'Berkas harus berformat .xlsx.';

        return false;
    }

    if (file.size > 10 * 1024 * 1024) {
        uploadError.textContent = 'Ukuran berkas impor maksimal 10 MB.';

        return false;
    }

    return true;
}

excelFile?.addEventListener('change', () => updateExcelFile(excelFile.files?.[0]));

dropzone?.addEventListener('dragover', (event) => {
    event.preventDefault();
    dropzone.classList.add('ring-2', 'ring-white');
});

dropzone?.addEventListener('dragleave', () => {
    dropzone.classList.remove('ring-2', 'ring-white');
});

dropzone?.addEventListener('drop', (event) => {
    event.preventDefault();
    dropzone.classList.remove('ring-2', 'ring-white');

    const file = event.dataTransfer?.files?.[0];
    if (!file || !excelFile) {
        return;
    }

    const transfer = new DataTransfer();
    transfer.items.add(file);
    excelFile.files = transfer.files;
    updateExcelFile(file);
});

stockImportForm?.addEventListener('submit', (event) => {
    const file = excelFile?.files?.[0];

    if (!updateExcelFile(file)) {
        event.preventDefault();
        return;
    }

    uploadProgress?.classList.remove('hidden');
    if (uploadSubmit) {
        uploadSubmit.disabled = true;
        uploadSubmit.textContent = 'Sedang mengimpor…';
    }
});
