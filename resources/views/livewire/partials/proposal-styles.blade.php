<style>
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.65);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000; padding: 20px;
        backdrop-filter: blur(2px);
    }
    .modal-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-strong);
        border-radius: var(--radius-lg);
        width: 100%; max-width: 560px;
        box-shadow: var(--shadow-lg);
        max-height: 90vh;
        display: flex; flex-direction: column;
    }
    .modal-panel.modal-lg { max-width: 880px; }
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .modal-header h3 {
        font-size: 14.5px; font-weight: 600; color: var(--text-white);
    }
    .modal-close {
        background: none; border: none; color: var(--text-muted);
        font-size: 22px; cursor: pointer; line-height: 1; padding: 0;
    }
    .modal-close:hover { color: var(--text-white); }
    .modal-body {
        padding: 20px;
        overflow-y: auto;
        display: flex; flex-direction: column; gap: 12px;
    }
    .modal-footer {
        margin-top: 16px; padding-top: 14px;
        border-top: 1px solid var(--border);
        display: flex; justify-content: flex-end; gap: 8px;
    }

    .form-label {
        display: block; font-size: 11.5px;
        color: var(--text-secondary);
        margin-bottom: 4px;
        font-weight: 500;
    }
    .form-input {
        width: 100%;
        background: var(--bg-input);
        border: 1px solid var(--border-strong);
        color: var(--text-primary);
        border-radius: var(--radius-sm);
        padding: 8px 10px;
        font-size: 12.5px;
        font-family: var(--font-sans);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-input:focus {
        border-color: var(--border-focus);
        box-shadow: var(--shadow-glow);
    }
    .form-error { color: var(--red); font-size: 11px; margin-top: 2px; }

    /* Wizard step indicator */
    .wizard-steps {
        display: flex; gap: 4px;
        padding: 14px 20px;
        background: var(--bg-surface);
        border-bottom: 1px solid var(--border);
    }
    .wizard-step {
        flex: 1; text-align: center;
        padding: 8px 6px;
        font-size: 11px; color: var(--text-muted);
        border-bottom: 2px solid var(--border);
        cursor: pointer;
        transition: all .15s;
    }
    .wizard-step.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
        font-weight: 600;
    }
    .wizard-step.completed {
        color: var(--green);
        border-bottom-color: var(--green);
    }

    /* Pipeline columns */
    .pipeline-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(220px, 1fr));
        gap: 14px;
        overflow-x: auto;
    }
    .pipeline-col {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px;
        min-height: 200px;
    }
    .pipeline-col-head {
        font-size: 11px; font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase; letter-spacing: 0.06em;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 10px;
        display: flex; justify-content: space-between;
    }
    .pipeline-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 12px;
        margin-bottom: 8px;
        cursor: default;
        transition: border-color .15s;
    }
    .pipeline-card:hover { border-color: var(--border-strong); }

    /* Builder layout */
    .builder-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 0;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .builder-sidebar {
        background: var(--bg-surface);
        border-right: 1px solid var(--border);
        padding: 14px;
        display: flex; flex-direction: column; gap: 4px;
    }
    .builder-step {
        padding: 10px 12px;
        font-size: 12.5px;
        color: var(--text-secondary);
        border-radius: var(--radius-sm);
        cursor: pointer;
        display: flex; align-items: center; gap: 10px;
        background: none; border: none; text-align: left;
        font-family: inherit;
    }
    .builder-step.active {
        background: var(--accent-subtle);
        color: var(--accent);
        font-weight: 600;
    }
    .builder-step:hover { background: var(--bg-hover); }
    .builder-step .step-num {
        width: 22px; height: 22px;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 10.5px; font-weight: 600;
    }
    .builder-step.active .step-num {
        background: var(--accent); border-color: var(--accent); color: white;
    }
    .builder-step.completed .step-num {
        background: var(--green); border-color: var(--green); color: var(--bg-base);
    }
    .builder-content { padding: 24px; overflow-y: auto; max-height: 75vh; }
    .builder-content h4 {
        font-size: 14px; font-weight: 600;
        color: var(--text-white); margin-bottom: 14px;
    }
    .builder-content h5 {
        font-size: 12.5px; font-weight: 600;
        color: var(--text-white); margin: 14px 0 8px;
    }

    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
    .field-stack { display: flex; flex-direction: column; gap: 12px; }

    /* Module / quotation cards */
    .builder-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        margin-bottom: 10px;
    }
    .builder-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 8px; margin-bottom: 10px;
    }
    .feature-row {
        display: flex; align-items: center; gap: 6px;
        margin-bottom: 4px;
    }
    .feature-row input { flex: 1; }
    .icon-btn {
        background: none; border: none; cursor: pointer;
        color: var(--text-muted); padding: 4px 6px;
        font-size: 14px; line-height: 1;
        border-radius: var(--radius-sm);
    }
    .icon-btn:hover { color: var(--red); background: var(--red-bg); }

    /* Buttons */
    .btn-ghost {
        background: transparent; color: var(--text-secondary);
        border: 1px dashed var(--border-strong);
    }
    .btn-ghost:hover { color: var(--accent); border-color: var(--accent); }
</style>
