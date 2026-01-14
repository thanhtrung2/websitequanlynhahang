<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; min-height: 100vh; }

/* Sidebar */
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, #001f3f 0%, #003366 100%);
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 0;
    box-shadow: 4px 0 15px rgba(0,0,0,0.1);
    z-index: 100;
    overflow-y: auto;
}
.sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-logo { display: flex; align-items: center; gap: 10px; color: #F5F5DC; font-size: 20px; font-weight: bold; }
.sidebar-logo i { color: #D4AF37; font-size: 24px; }
.sidebar-user { padding: 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-avatar { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #D4AF37; }
.sidebar-user-info h4 { color: #F5F5DC; font-size: 14px; }
.sidebar-user-info span { color: #D4AF37; font-size: 12px; }
.sidebar-menu { list-style: none; padding: 15px 0; }
.sidebar-menu li a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: #ccc; text-decoration: none; transition: all 0.3s; }
.sidebar-menu li a:hover { background: rgba(255,255,255,0.1); color: #F5F5DC; }
.sidebar-menu li a.active { background: rgba(212,175,55,0.2); color: #D4AF37; border-left: 3px solid #D4AF37; }
.sidebar-menu li a i { width: 20px; text-align: center; }
.sidebar-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 10px 20px; }
.sidebar-title { padding: 10px 25px; color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }

/* Main Content */
.admin-main { 
    margin-left: 260px; 
    padding: 20px; 
    min-height: 100vh; 
}

.page-header {
    background: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.page-header h1 { color: #001f3f; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
.page-header h1 i { color: #D4AF37; }
.page-header p { color: #666; }

/* Cards */
.card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
}
.card h2, .card h3 {
    color: #001f3f;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card h2 i, .card h3 i { color: #D4AF37; }

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}
.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    border-left: 4px solid #D4AF37;
}
.stat-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.stat-icon.primary { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
.stat-icon.success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.stat-icon.warning { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
.stat-icon.danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.stat-info h3 { font-size: 28px; color: #001f3f; border: none; padding: 0; margin: 0; }
.stat-info p { color: #666; font-size: 14px; margin: 0; }

/* Tables */
.table-container {
    overflow-x: auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th {
    background: linear-gradient(135deg, #001f3f, #003366);
    color: #F5F5DC;
    padding: 15px;
    text-align: left;
    font-weight: 600;
}
table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}
table tbody tr:hover { background: #f8f9fa; }

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}
.btn-primary { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
.btn-primary:hover { background: linear-gradient(135deg, #003366, #004080); }
.btn-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
.btn-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); color: #333; }
.btn-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-sm { padding: 6px 12px; font-size: 12px; }

/* Forms */
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #001f3f;
    font-weight: 500;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: #001f3f;
}

/* Alerts */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success { background: #d4edda; color: #155724; }
.alert-error, .alert-danger { background: #f8d7da; color: #721c24; }
.alert-warning { background: #fff3cd; color: #856404; }
.alert-info { background: #d1ecf1; color: #0c5460; }

/* Badges */
.badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-primary { background: #cce5ff; color: #004085; }

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal.active { display: flex; }
.modal-content {
    background: white;
    border-radius: 15px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-header {
    padding: 20px;
    border-bottom: 2px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 { color: #001f3f; }
.modal-body { padding: 20px; }
.modal-footer { padding: 15px 20px; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }
.close-btn:hover { color: #dc3545; }

/* Action buttons */
.action-btns { display: flex; gap: 8px; }
.btn-icon {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}
.btn-icon:hover { transform: scale(1.1); }
.btn-edit { background: #e3f2fd; color: #1976d2; }
.btn-delete { background: #ffebee; color: #c62828; }
.btn-view { background: #e8f5e9; color: #2e7d32; }

/* Responsive */
@media (max-width: 1024px) {
    .sidebar {
        width: 70px;
        overflow: visible;
    }
    .sidebar-header, .sidebar-user, .sidebar-title { display: none; }
    .sidebar-logo span, .sidebar-menu li a span { display: none; }
    .sidebar-menu li a { justify-content: center; padding: 15px; }
    .sidebar-divider { margin: 10px 10px; }
    .admin-main { margin-left: 70px; }
}
@media (max-width: 768px) {
    .sidebar { 
        transform: translateX(-100%);
        width: 260px;
    }
    .sidebar.show { transform: translateX(0); }
    .sidebar-header, .sidebar-user, .sidebar-title { display: block; }
    .sidebar-logo span, .sidebar-menu li a span { display: inline; }
    .admin-main { margin-left: 0; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>
