<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function dashboard(): View
    {
        $operators = User::query()
            ->where('role', User::ROLE_OPERATOR)
            ->latest()
            ->get();

        $stats = [
            'pending' => \App\Models\Pengaduan::where('status', 'pending')->count(),
            'diproses' => \App\Models\Pengaduan::where('status', 'diproses')->count(),
            'selesai' => \App\Models\Pengaduan::where('status', 'selesai')->count(),
            'ditolak' => \App\Models\Pengaduan::where('status', 'ditolak')->count(),
        ];

        $totalPelapor = \App\Models\Pengaduan::count();

        return view('admin.dashboard', [
            'operators' => $operators,
            'stats' => $stats,
            'totalPelapor' => $totalPelapor,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => [
                User::ROLE_ADMIN => 'Admin',
                User::ROLE_OPERATOR => 'Operator',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.create')->with('success', 'Akun admin/operator berhasil dibuat.');
    }
}
