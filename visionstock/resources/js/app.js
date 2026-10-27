const menuButtons = document.querySelectorAll('[data-menu-button]');
const sidebar = document.querySelector('[data-sidebar]');
const overlay = document.querySelector('[data-sidebar-overlay]');
const menuOpener = document.querySelector('[data-menu-button]:not([data-menu-close])');
const menuCloser = document.querySelector('[data-menu-close]');
const desktopSidebar = window.matchMedia('(min-width: 1024px)');

function setSidebarOpen(isOpen, restoreFocus = true) {
    sidebar?.classList.toggle('translate-x-0', isOpen);
    sidebar?.classList.toggle('-translate-x-full', !isOpen);
    overlay?.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);
    menuOpener?.setAttribute('aria-expanded', String(isOpen));

    if (isOpen) {
        menuCloser?.focus();
    } else if (restoreFocus && !desktopSidebar.matches) {
        menuOpener?.focus();
    }
}

menuButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const isCloseButton = button.hasAttribute('data-menu-close');
        const isOpen = button.getAttribute('aria-expanded') !== 'true';
        setSidebarOpen(isCloseButton ? false : isOpen);
    });
});

overlay?.addEventListener('click', () => setSidebarOpen(false));
desktopSidebar.addEventListener('change', (event) => {
    if (event.matches) {
        setSidebarOpen(false, false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && sidebar && !sidebar.classList.contains('-translate-x-full')) {
        setSidebarOpen(false);
    }
});

const searchForm = document.querySelector('[data-topbar-search]');
const searchInput = searchForm?.querySelector('[data-search-input]');
const searchToggle = searchForm?.querySelector('[data-search-toggle]');

searchToggle?.addEventListener('click', (event) => {
    if (window.matchMedia('(min-width: 768px)').matches || !searchInput || !searchInput.classList.contains('hidden')) {
        return;
    }

    event.preventDefault();
    searchInput.classList.remove('hidden');
    searchInput.classList.add('block', 'absolute', 'right-4', 'top-full', 'mt-2', 'z-40', 'w-[min(70vw,20rem)]', 'shadow-lg');
    searchToggle.setAttribute('aria-expanded', 'true');
    searchInput.focus();
});

searchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        searchInput.classList.add('hidden');
        searchInput.classList.remove('block', 'absolute', 'right-4', 'top-full', 'mt-2', 'z-40', 'w-[min(70vw,20rem)]', 'shadow-lg');
        searchToggle?.setAttribute('aria-expanded', 'false');
        searchToggle?.focus();
    }
});

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

    productForm.querySelectorAll('[data-legacy-category]').forEach((option) => option.remove());
    productForm.reset();
    productForm.action = productDialog.dataset.storeUrl;
    productMethod.value = 'POST';
    productId.value = '';
    productDialog.querySelector('[data-dialog-title]').textContent = 'Input Manual';
    productForm.querySelector('[name="name"]').value = '';
    productForm.querySelector('[name="description"]').value = '';
    productForm.querySelector('[name="stock"]').value = '0';
    productForm.querySelector('[name="category_name"]').value = '';
    setProductPreview('');

    if (button?.hasAttribute('data-product-edit')) {
        productForm.action = button.dataset.updateUrl;
        productMethod.value = 'PUT';
        productId.value = button.dataset.productId;
        productDialog.querySelector('[data-dialog-title]').textContent = 'Edit Stok';
        productForm.querySelector('[name="name"]').value = button.dataset.name;
        productForm.querySelector('[name="description"]').value = button.dataset.description;
        productForm.querySelector('[name="stock"]').value = button.dataset.stock;
        const categorySelect = productForm.querySelector('[name="category_name"]');
        const categoryName = button.dataset.categoryName;

        if (categoryName && !Array.from(categorySelect.options).some((option) => option.value === categoryName)) {
            const option = new Option(categoryName, categoryName);
            option.dataset.legacyCategory = 'true';
            categorySelect.add(option);
        }

        categorySelect.value = categoryName;
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
const transactionImportForm = document.querySelector('[data-transaction-import-form]');

function setupExcelUpload(form, progressPercent) {
    if (!form) {
        return;
    }

    const excelFile = form.querySelector('[data-excel-file]');
    const dropzone = form.querySelector('[data-dropzone]');
    const fileName = form.querySelector('[data-file-name]');
    const uploadError = form.querySelector('[data-upload-error]');
    const uploadProgress = form.querySelector('[data-upload-progress]');
    const uploadSubmit = form.querySelector('[data-upload-submit]');
    const progressLabel = form.querySelector('[data-progress-percent]');
    const uploadFileLabel = form.querySelector('[data-upload-file-label]');
    const progressBar = uploadProgress?.querySelector('progress');

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

    form.addEventListener('submit', (event) => {
        const file = excelFile?.files?.[0];

        if (!updateExcelFile(file)) {
            event.preventDefault();
            return;
        }

        if (progressLabel) {
            progressLabel.textContent = `${progressPercent}%`;
        }
        if (uploadFileLabel) {
            uploadFileLabel.textContent = file.name;
        }
        if (progressBar) {
            progressBar.value = progressPercent;
            progressBar.textContent = `${progressPercent}%`;
        }
        uploadProgress?.classList.remove('hidden');
        if (uploadSubmit) {
            uploadSubmit.disabled = true;
            uploadSubmit.textContent = 'Sedang mengimpor…';
        }
    });
}

setupExcelUpload(stockImportForm, 90);
setupExcelUpload(transactionImportForm, 77);

const invoiceFile = document.querySelector('[data-invoice-file]');
const invoiceFileName = document.querySelector('[data-invoice-file-name]');
invoiceFile?.addEventListener('change', () => {
    if (invoiceFileName) {
        invoiceFileName.textContent = invoiceFile.files?.[0]?.name ?? 'PDF, JPG, atau PNG · Maksimum 5 MB';
    }
});

const transactionSuccess = document.querySelector('[data-transaction-success]');
if (transactionSuccess instanceof HTMLDialogElement) {
    transactionSuccess.showModal();
}
