<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    /**
     * Display the Admin Executive Dashboard.
     */
    public function dashboard(): View
    {
        $stats = $this->adminService->getDashboardStats();
        $recentSightings = $this->adminService->getRecentSightings(6);
        $reports = $this->adminService->getPendingReports(5);

        return view('admin.dashboard', compact('stats', 'recentSightings', 'reports'));
    }

    /**
     * Display dedicated User Management & Control Page.
     */
    public function users(Request $request): View
    {
        $search = $request->input('search');
        $users = $this->adminService->getUsersList($search);
        $stats = $this->adminService->getDashboardStats();

        return view('admin.users', compact('users', 'stats', 'search'));
    }

    /**
     * Display dedicated Sighting Reports Moderation Page.
     */
    public function reports(): View
    {
        $reports = $this->adminService->getPaginatedReports(15);
        $stats = $this->adminService->getDashboardStats();

        return view('admin.reports', compact('reports', 'stats'));
    }

    /**
     * Display dedicated Sightings & Scan Monitoring Page.
     */
    public function monitoring(): View
    {
        $sightings = $this->adminService->getPaginatedSightings(12);
        $stats = $this->adminService->getDashboardStats();

        return view('admin.monitoring', compact('sightings', 'stats'));
    }

    /**
     * Get detailed activity and plant history for a user (JSON).
     */
    public function userDetails(User $user): JsonResponse
    {
        $activity = $this->adminService->getUserActivityDetails($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'level' => $user->level,
                'exp' => $user->exp,
                'coin' => $user->coin,
                'locale' => $user->locale ?? 'id',
                'created_at' => $user->created_at ? $user->created_at->format('d M Y H:i') : '-',
            ],
            'activity' => $activity,
        ]);
    }

    /**
     * Update user role (Viewer / Ranger / Admin).
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:viewer,ranger,admin',
        ]);

        try {
            $this->adminService->updateUserRole($user, $validated['role']);

            return redirect()->back()->with('success', "Role pengguna {$user->name} berhasil diperbarui menjadi " . strtoupper($validated['role']) . '.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['role' => $e->getMessage()]);
        }
    }

    /**
     * Resolve a reported plant sighting (Delete marker sighting or Dismiss report).
     */
    public function resolveReport(Request $request, int $reportId): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete_sighting,dismiss',
        ]);

        try {
            $this->adminService->resolveReport($reportId, $validated['action'], $request->user());
            $msg = $validated['action'] === 'delete_sighting'
                ? 'Marker temuan tumbuhan berhasil dihapus dan laporan diselesaikan.'
                : 'Laporan temuan berhasil diabaikan/diarsipkan.';

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['report' => $e->getMessage()]);
        }
    }
}
