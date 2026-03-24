import './bootstrap';
import 'flowbite';

const STATUS_UI = {
    pending: {
        label: 'Pendente',
        tone: 'bg-slate-200 text-slate-700',
        message: 'Arquivo na fila. Aguardando worker iniciar o processamento...',
    },
    processing: {
        label: 'Processando',
        tone: 'bg-amber-100 text-amber-800',
        message: 'Processamento em andamento...',
    },
    completed: {
        label: 'Concluido',
        tone: 'bg-emerald-100 text-emerald-800',
        message: 'Processamento finalizado. Texto atualizado.',
    },
    failed: {
        label: 'Falhou',
        tone: 'bg-red-100 text-red-800',
        message: 'Falha no processamento. Verifique os detalhes abaixo.',
    },
};

const FINISHED_STATUSES = new Set(['completed', 'failed']);

const formatDateTime = (isoDate) => {
    if (!isoDate) {
        return '-';
    }

    const parsed = new Date(isoDate);

    if (Number.isNaN(parsed.getTime())) {
        return isoDate;
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'medium',
    }).format(parsed);
};

const updateBadge = (badge, status) => {
    const statusConfig = STATUS_UI[status] ?? STATUS_UI.pending;
    badge.textContent = statusConfig.label;
    badge.className = `rounded-full px-3 py-1 text-xs font-semibold ${statusConfig.tone}`;
};

const startOcrLivePolling = () => {
    const container = document.querySelector('[data-ocr-live]');

    if (!container) {
        return;
    }

    const endpoint = container.dataset.endpoint;
    const liveState = container.querySelector('[data-ocr-live-state]');
    const badge = container.querySelector('[data-ocr-status-badge]');
    const originalName = container.querySelector('[data-ocr-original-name]');
    const mimeType = container.querySelector('[data-ocr-mime-type]');
    const createdAt = container.querySelector('[data-ocr-created-at]');
    const updatedAt = container.querySelector('[data-ocr-updated-at]');
    const extractedText = container.querySelector('[data-ocr-extracted-text]');
    const errorMessage = container.querySelector('[data-ocr-error-message]');

    if (!endpoint || !badge || !extractedText) {
        return;
    }

    let timerId = null;

    const stopPolling = () => {
        if (timerId) {
            window.clearInterval(timerId);
            timerId = null;
        }
    };

    const applyDocumentState = (documentData) => {
        const status = documentData.processing_status ?? 'pending';
        const statusConfig = STATUS_UI[status] ?? STATUS_UI.pending;

        updateBadge(badge, status);

        if (liveState) {
            liveState.textContent = statusConfig.message;
        }

        if (originalName && documentData.original_name) {
            originalName.textContent = documentData.original_name;
        }

        if (mimeType && documentData.mime_type) {
            mimeType.textContent = documentData.mime_type;
        }

        if (createdAt) {
            createdAt.textContent = formatDateTime(documentData.created_at);
        }

        if (updatedAt) {
            updatedAt.textContent = formatDateTime(documentData.updated_at);
        }

        if (extractedText) {
            extractedText.textContent = documentData.extracted_text?.trim()
                ? documentData.extracted_text
                : 'Aguardando processamento da fila...';
        }

        if (errorMessage) {
            if (documentData.error_message) {
                errorMessage.classList.remove('hidden');
                errorMessage.textContent = documentData.error_message;
            } else {
                errorMessage.classList.add('hidden');
                errorMessage.textContent = '';
            }
        }

        if (FINISHED_STATUSES.has(status)) {
            stopPolling();
        }
    };

    const fetchStatus = async () => {
        try {
            const response = await window.axios.get(endpoint, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const documentData = response?.data?.data;

            if (!documentData) {
                return;
            }

            applyDocumentState(documentData);
        } catch (error) {
            if (liveState) {
                liveState.textContent = 'Nao foi possivel atualizar agora. Tentando novamente...';
            }
        }
    };

    fetchStatus();
    timerId = window.setInterval(fetchStatus, 3000);

    window.addEventListener(
        'beforeunload',
        () => {
            stopPolling();
        },
        { once: true }
    );
};

document.addEventListener('DOMContentLoaded', () => {
    startOcrLivePolling();
});
