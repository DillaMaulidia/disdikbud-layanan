<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function dashboard(): View
    {
        $operators = User::query()
            ->where('role', User::ROLE_OPERATOR)
            ->latest()
            ->get();

        $operatorsByBidang = $operators->groupBy(fn (User $operator): string => $operator->bidang ?? 'Belum ditentukan');

        $pengaduans = Pengaduan::query()
            ->latest()
            ->take(8)
            ->get();

        $stats = [
            'pending' => Pengaduan::where('status', 'pending')->count(),
            'diproses' => Pengaduan::where('status', 'diproses')->count(),
            'selesai' => Pengaduan::where('status', 'selesai')->count(),
            'ditolak' => Pengaduan::where('status', 'ditolak')->count(),
        ];

        $totalPelapor = Pengaduan::count();

        return view('admin.dashboard', [
            'operators' => $operators,
            'operatorsByBidang' => $operatorsByBidang,
            'stats' => $stats,
            'totalPelapor' => $totalPelapor,
            'pengaduans' => $pengaduans,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => [
                User::ROLE_ADMIN => 'Admin',
                User::ROLE_OPERATOR => 'Operator',
            ],
            'bidang' => User::bidang(),
        ]);
    }

    public function reports(): View
    {
        $pengaduans = Pengaduan::query()
            ->with('operator')
            ->latest()
            ->get();

        return view('admin.reports', compact('pengaduans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR])],
            'bidang' => ['nullable', 'string', Rule::in(User::bidang()), Rule::requiredIf(fn (): bool => $request->input('role') === User::ROLE_OPERATOR)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'bidang' => $validated['bidang'] ?? null,
        ]);

        return redirect()->route('admin.users.create')->with('success', 'Akun admin/operator berhasil dibuat.');
    }
}
