

<div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar-drawer" id="sidebarDrawer">
        <div class="sidebar-header">
            <span class="close-btn" id="sidebarClose">&times;</span>
            <div class="user-avatar">80x80</div>
            <h6 class="mb-0 font-weight-bold text-dark"><?= htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></h6>
            <small class="text-muted">ICT / Procurement</small>
        </div>
        
        <div class="sidebar-body">
            <ul class="sidebar-menu">
                <li>
                    <a href="#" data-toggle="collapse" data-target="#procurementSub">
                        <span><i class="fas fa-shopping-cart mr-2 text-primary"></i> Procurement</span> 
                        <i class="fas fa-chevron-down small"></i>
                    </a>
                    <ul class="submenu collapse show" id="procurementSub">
                        <li><a href="procurement-ops">Procurement - Ops</a></li>
                        <li><a href="procurement-non-ops">Procurement - Non Ops</a></li>
                        <li><a href="index">Dashboard</a></li>
                        <li><a href="#">Edit PR (Form & Attach)</a></li>
                        <li><a href="approval-ops">Approval PR</a></li>
                        <li><a href="#">Tracking PR</a></li>
                        <li><a href="#">Confirm PO & BAST</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <a href="#" id="btnLogout" class="btn-logout-sidebar">
                <i class="fas fa-sign-out-alt mr-1"></i> Keluar (Logout)
            </a>
        </div>
    </div>