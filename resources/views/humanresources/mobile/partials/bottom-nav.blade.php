@php
    $active = $active ?? 'dashboard';
@endphp
<style>
    .hcga-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-top: 1px solid #e5ebe8;
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 8px 4px calc(8px + env(safe-area-inset-bottom));
        z-index: 1050;
        box-shadow: 0 -4px 18px rgba(6, 78, 59, 0.06);
    }
    .hcga-bottom-nav a {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #9ca3af;
        font-size: 11px;
        font-weight: 600;
        gap: 3px;
        padding: 4px 2px;
        border-radius: 12px;
        transition: color 0.18s ease, background-color 0.18s ease;
    }
    .hcga-bottom-nav a i {
        font-size: 17px;
    }
    .hcga-bottom-nav a.active {
        color: #047857;
    }
    .hcga-bottom-nav a.active .nav-icon-wrap {
        background: #ecfdf5;
        color: #047857;
    }
    .hcga-bottom-nav .nav-icon-wrap {
        width: 34px;
        height: 28px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<nav class="hcga-bottom-nav" aria-label="Mobile Navigation">
    <a href="{{ route('mobile.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">
        <span class="nav-icon-wrap"><i class="fas fa-home"></i></span>
        <span>Home</span>
    </a>
    <a href="{{ route('mobile.ess') }}" class="{{ $active === 'ess' ? 'active' : '' }}">
        <span class="nav-icon-wrap"><i class="fas fa-briefcase"></i></span>
        <span>ESS</span>
    </a>
    <a href="{{ route('attendance.mobile.index') }}" class="{{ $active === 'attendance' ? 'active' : '' }}">
        <span class="nav-icon-wrap"><i class="fas fa-fingerprint"></i></span>
        <span>Absensi</span>
    </a>
    <a href="{{ route('approval.mobile.index') }}" class="{{ $active === 'approval' ? 'active' : '' }}">
        <span class="nav-icon-wrap"><i class="fas fa-check-double"></i></span>
        <span>Approval</span>
    </a>
    <a href="{{ route('employee.mobile.my-profile') }}" class="{{ $active === 'profile' ? 'active' : '' }}">
        <span class="nav-icon-wrap"><i class="fas fa-user"></i></span>
        <span>Profil</span>
    </a>
</nav>
