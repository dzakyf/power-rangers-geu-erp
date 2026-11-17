<style>
    :root {
        --emerald-dark: #064E3B;
        --emerald-primary: #047857;
        --emerald-teal: #0F766E;
        --emerald-soft: #ECFDF5;
        --emerald-border: #A7F3D0;
        --bg-canvas: #F6F8F7;
        --text-dark: #111827;
        --text-muted: #6B7280;
        --border-soft: #E5EBE8;
        --bs-primary: #047857;
        --bs-primary-rgb: 4, 120, 87;
    }

    html, body {
        overflow-x: hidden;
        background-color: var(--bg-canvas) !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--text-dark);
    }

    body {
        padding-bottom: 92px !important;
    }

    .finora-shell {
        max-width: 480px;
        margin: 0 auto;
        padding: 16px 16px 24px;
    }

    .finora-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 16px;
    }

    .finora-back-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid var(--border-soft);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--emerald-dark);
        text-decoration: none;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(6, 78, 59, 0.04);
    }

    .finora-hero {
        background: linear-gradient(135deg, #064E3B 0%, #047857 65%, #0F766E 100%) !important;
        color: #FFFFFF !important;
        border-radius: 22px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 26px rgba(6, 78, 59, 0.20);
        margin-bottom: 16px;
    }

    .finora-hero::after {
        content: '';
        position: absolute;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        right: -30px;
        top: -30px;
        pointer-events: none;
    }

    .finora-card {
        background: #FFFFFF;
        border: 1px solid var(--border-soft);
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .finora-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--emerald-soft);
        color: var(--emerald-dark);
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .finora-btn-primary {
        background: linear-gradient(135deg, #064E3B 0%, #047857 100%);
        color: #FFFFFF !important;
        border: none;
        border-radius: 999px;
        padding: 11px 18px;
        font-weight: 700;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 8px 18px rgba(4, 120, 87, 0.22);
    }

    .finora-btn-soft {
        background: var(--emerald-soft);
        color: var(--emerald-dark) !important;
        border: 1px solid var(--emerald-border);
        border-radius: 999px;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* Vertical Key-Value Stack */
    .finora-kv-row {
        display: flex;
        flex-direction: column;
        padding: 10px 0;
        border-bottom: 1px solid #F1F5F3;
    }
    .finora-kv-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .finora-kv-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 2px;
    }
    .finora-kv-val {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        word-break: break-word;
    }

    /* Tone Icon Badges */
    .tone-mint { background: #ECFDF5; color: #059669; }
    .tone-emerald { background: #D1FAE5; color: #047857; }
    .tone-blue { background: #DBEAFE; color: #2563EB; }
    .tone-amber { background: #FEF3C7; color: #D97706; }
    .tone-purple { background: #EDE9FE; color: #7C3AED; }
    .tone-coral { background: #FEE2E2; color: #DC2626; }
    .tone-teal { background: #CCFBF1; color: #0D9488; }
    .tone-slate { background: #F1F5F9; color: #475569; }

    /* Unified Finora Overrides for Legacy Mobile Headers, Tabs & Status Badges */
    .header-card, .header-mobile-appr, .header-announcement, .header-profile {
        background: linear-gradient(135deg, #064E3B 0%, #047857 70%, #0F766E 100%) !important;
        color: #FFFFFF !important;
        border-bottom-left-radius: 24px !important;
        border-bottom-right-radius: 24px !important;
        box-shadow: 0 8px 22px rgba(6, 78, 59, 0.18) !important;
    }

    .btn-primary {
        background-color: #047857 !important;
        border-color: #047857 !important;
        color: #FFFFFF !important;
    }
    .btn-outline-primary {
        color: #047857 !important;
        border-color: #047857 !important;
    }
    .btn-outline-primary:hover {
        background-color: #047857 !important;
        color: #FFFFFF !important;
    }
    .text-primary {
        color: #047857 !important;
    }
    .border-primary {
        border-color: #047857 !important;
    }

    /* Fix contrast for status badges (prevent dark-green on dark-green) */
    .badge.bg-primary:not(.bg-opacity-10):not(.bg-opacity-25) {
        background-color: #047857 !important;
        color: #FFFFFF !important;
    }
    .bg-primary.bg-opacity-10 {
        background-color: rgba(4, 120, 87, 0.12) !important;
        color: #064E3B !important;
    }
    .bg-primary.bg-opacity-25 {
        background-color: rgba(255, 255, 255, 0.18) !important;
        color: #FFFFFF !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
    }

    /* Tab pills & category pills */
    .tab-pill-btn.active,
    .cat-pill.active,
    .nav-pills .nav-link.active {
        background: #047857 !important;
        border-color: #047857 !important;
        color: #FFFFFF !important;
        box-shadow: 0 3px 10px rgba(4, 120, 87, 0.25) !important;
    }
    .cat-pill.active .badge {
        background: #FFFFFF !important;
        color: #047857 !important;
    }
</style>
