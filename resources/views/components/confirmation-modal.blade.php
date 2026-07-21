<div
    id="stockify-confirmation-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
    aria-hidden="true"
>
    <div
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-gray-700 dark:bg-gray-800"
        role="dialog"
        aria-modal="true"
        aria-labelledby="stockify-confirmation-title"
    >
        <div class="flex items-start gap-4">
            <div
                id="stockify-confirmation-icon"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-200"
            >
                ?
            </div>

            <div>
                <h2 id="stockify-confirmation-title" class="text-lg font-bold text-slate-900 dark:text-white">
                    Konfirmasi tindakan
                </h2>
                <p id="stockify-confirmation-message" class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-gray-400"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button
                id="stockify-confirmation-cancel"
                type="button"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
            >
                Batal
            </button>
            <button
                id="stockify-confirmation-accept"
                type="button"
                class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
            >
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>