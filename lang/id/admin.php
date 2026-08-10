<?php

return [
    // Header & Control Banner
    'dashboard_title' => 'Administrator Dashboard',
    'dashboard_subtitle' => 'Pusat kendali utama untuk memantau aktivitas platform, mengelola statistik pengguna (Viewer & Ranger), meninjau laporan temuan flora, dan mengontrol hak akses akun.',
    'system_control_title' => 'PANEL KONTROL SISTEM & MONITORING',
    'admin_label' => 'Admin Pengendali',

    // Metric Cards
    'total_users' => 'TOTAL PENGGUNA',
    'viewers' => 'VIEWER',
    'rangers' => 'RANGER',
    'sightings' => 'TEMUAN PETA',
    'sighting_reports' => 'LAPORAN PETA',
    'pending' => 'Pending',
    'verifications' => 'Verifikasi',
    'species_catalog' => 'KATALOG SPESIES',
    'total_exp' => 'TOTAL EXP SISTEM',
    'total_coins' => 'SALDO NC SISTEM',

    // Analytics & Charts
    'user_distribution_title' => 'Distribusi Peran Pengguna',
    'catalog_sightings_summary_title' => 'Ringkasan Katalog & Spesimen',
    'chart_realtime' => 'Real-time',
    'chart_flora_data' => 'Data Flora',
    'chart_catalog_label' => 'Katalog Spesies',
    'chart_sightings_label' => 'Temuan Peta',

    // Anomaly Detection Hub
    'anomaly_title' => 'Deteksi Anomali & Isu Sistem',
    'system_operational' => '100% Operasional',
    'report_issue_title' => 'Laporan Temuan Peta',
    'report_issue_sub' => 'Membutuhkan peninjauan admin',
    'exp_issued_title' => 'Total EXP Terbit',
    'exp_issued_sub' => 'Akumulasi seluruh pemain',
    'action_moderation' => '🚩 Moderasi Laporan',
    'action_user_mgmt' => '👥 Manajemen User',

    // Recent Activity Section
    'recent_sightings_title' => 'Ringkasan Temuan Spesies Terbaru',
    'recent_sightings_sub' => 'Aktivitas pemindaian flora terbaru dari seluruh Ranger di lapangan.',
    'view_all' => 'Lihat Selengkapnya &rarr;',

    // User Control & Role Management Table
    'user_management_title' => 'Manajemen & Kontrol Pengguna',
    'user_management_subtitle' => 'Kelola daftar akun terdaftar, periksa level/saldo, dan perbarui peran pengguna.',
    'search_placeholder' => 'Cari nama / email / role...',
    'search_btn' => 'Cari',
    'reset_btn' => 'Reset',
    'col_user' => 'Pengguna',
    'col_current_role' => 'Peran Saat Ini',
    'col_level_exp' => 'Level / EXP',
    'col_coin' => 'Koin (NC)',
    'col_joined' => 'Terdaftar',
    'col_role_action' => 'Aksi Kontrol Peran',
    'detail_btn' => '🔍 Detail',
    'save_btn' => 'Simpan',
    'btn_reset_password' => '🔑 Reset Pass',
    'btn_delete_user' => '🗑️ Hapus',
    'reset_password_modal_title' => 'Reset Password Pengguna',
    'reset_password_modal_sub' => 'Masukkan password baru untuk pengguna ini.',
    'new_password_label' => 'Password Baru',
    'confirm_password_label' => 'Konfirmasi Password Baru',
    'delete_user_confirm' => 'Apakah Anda yakin ingin MENGHAPUS akun pengguna ini secara permanen?',
    'no_users_found' => 'Tidak ada pengguna yang ditemukan.',

    // Reports Moderation Table
    'reports_title' => 'Moderasi Laporan Temuan Peta',
    'reports_subtitle' => 'Laporan dari pengguna mengenai keaslian, keberadaan, atau perubahan tumbuhan di lokasi nyata.',
    'pending_reports_badge' => ':count Laporan Menunggu',
    'col_reported_plant' => 'Tumbuhan Dilaporkan',
    'col_reporter' => 'Pelapor',
    'col_report_reason' => 'Alasan Pelaporan',
    'col_report_notes' => 'Catatan Pelapor',
    'col_report_time' => 'Waktu Laporan',
    'col_admin_action' => 'Aksi Admin',
    'deleted_marker_label' => 'Marker Sudah Dihapus',
    'action_delete_sighting' => '🗑️ Hapus Marker Peta',
    'action_dismiss_report' => '🛑 Abaikan Laporan',
    'delete_confirm' => 'Apakah Anda yakin ingin MENGHAPUS marker temuan tumbuhan ini dari peta?',
    'no_pending_reports' => '✨ Tidak ada laporan temuan tumbuhan yang menunggu. Semua marker di lokasi dalam keadaan valid.',

    // Monitoring Log Activity
    'monitoring_title' => 'Monitoring Aktivitas Pemindaian & Temuan',
    'monitoring_subtitle' => 'Log temuan spesies terbaru dari Ranger & Viewer secara terintegrasi.',
    'realtime_streaming' => 'Log Real-Time',
    'status_verified' => 'TERVERIFIKASI',
    'status_pending' => 'PENDING',
    'status_rejected' => 'DITOLAK',
    'scanned_by' => 'Dipindai',
    'no_sightings_log' => 'Belum ada log temuan spesies tercatat.',

    // Leaderboard Section
    'leaderboard_title' => 'Leaderboard Mingguan',
    'leaderboard_subtitle' => 'Peringkat perolehan EXP pemain (Ranger & Viewer) minggu ini.',
    'rank' => 'Peringkat',
    'exp_earned' => 'EXP Minggu Ini',
    'no_leaderboard_data' => 'Belum ada data peringkat untuk minggu ini.',

    // Sidebar Navigation
    'sidebar' => [
        'overview' => 'Dashboard Utama',
        'users' => 'Manajemen Pengguna',
        'reports' => 'Moderasi Laporan',
        'monitoring' => 'Monitoring Temuan',
        'mode_explorer' => 'MODE PENJELAJAH',
        'peta' => 'Peta Spesies',
        'system_active' => 'Sistem Aktif',
    ],
];
