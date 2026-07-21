const modal = document.getElementById('stockify-confirmation-modal');

function initializeConfirmationModal() {
    if (!modal) {
        return;
    }

    const title = document.getElementById('stockify-confirmation-title');
    const message = document.getElementById('stockify-confirmation-message');
    const icon = document.getElementById('stockify-confirmation-icon');
    const cancelButton = document.getElementById('stockify-confirmation-cancel');
    const acceptButton = document.getElementById('stockify-confirmation-accept');
    let resolveConfirmation = null;

    function closeConfirmation(confirmed) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');

        if (resolveConfirmation) {
            resolveConfirmation(confirmed);
            resolveConfirmation = null;
        }
    }

    function ask(options = {}) {
        title.textContent = options.title || 'Konfirmasi tindakan';
        message.textContent = options.message || 'Apakah Anda yakin ingin melanjutkan?';
        acceptButton.textContent = options.confirmLabel || 'Ya, Lanjutkan';

        const isDanger = options.variant === 'danger';
        icon.textContent = isDanger ? '!' : '?';
        icon.className = isDanger
            ? 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-lg font-bold text-rose-700 dark:bg-rose-900/50 dark:text-rose-200'
            : 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-200';
        acceptButton.className = isDanger
            ? 'rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700'
            : 'rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        acceptButton.focus();

        return new Promise((resolve) => {
            resolveConfirmation = resolve;
        });
    }

    cancelButton.addEventListener('click', () => closeConfirmation(false));
    acceptButton.addEventListener('click', () => closeConfirmation(true));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeConfirmation(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeConfirmation(false);
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-stockify-confirm]');

        if (!form || form.dataset.stockifyConfirmed === 'true') {
            return;
        }

        event.preventDefault();

        const confirmed = await ask({
            title: form.dataset.stockifyConfirmTitle,
            message: form.dataset.stockifyConfirm,
            confirmLabel: form.dataset.stockifyConfirmLabel,
            variant: form.dataset.stockifyConfirmVariant,
        });

        if (confirmed) {
            form.dataset.stockifyConfirmed = 'true';
            form.requestSubmit();
        }
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-stockify-confirm-form]');

        if (!button) {
            return;
        }

        event.preventDefault();

        const confirmed = await ask({
            title: button.dataset.stockifyConfirmTitle,
            message: button.dataset.stockifyConfirm,
            confirmLabel: button.dataset.stockifyConfirmLabel,
            variant: button.dataset.stockifyConfirmVariant,
        });

        if (confirmed) {
            document.getElementById(button.dataset.stockifyConfirmForm)?.requestSubmit();
        }
    });

    window.StockifyConfirm = { ask };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeConfirmationModal, {
        once: true,
    });
} else {
    initializeConfirmationModal();
}