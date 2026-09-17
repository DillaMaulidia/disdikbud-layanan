<?php

namespace App\Http\Controllers;

use App\Exports\PengaduanExport;
use App\Models\Pengaduan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

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

    public function reports(Request $request): View
    {
        $filters = $this->validatedReportDates($request);
        $status = $request->string('status')->toString() ?: 'semua';

        abort_unless(in_array($status, ['semua', 'selesai', 'proses'], true), 404);

        $pengaduans = $this->reportQuery($status, $filters['from'] ?? null, $filters['to'] ?? null)->get();

        return view('admin.reports', [
            'selesai' => $pengaduans->where('status', 'selesai'),
            'diproses' => $pengaduans->where('status', '!=', 'selesai'),
            'selectedStatus' => $status,
            'dateFrom' => $filters['from'] ?? '',
            'dateTo' => $filters['to'] ?? '',
        ]);
    }

    public function showReport(Pengaduan $pengaduan): View
    {
        return view('tickets.show', [
            'pengaduan' => $pengaduan,
            'isAdminContext' => true,
        ]);
    }

    public function exportReport(Request $request, string $status, string $format): Response
    {
        abort_unless(in_array($status, ['semua', 'selesai', 'proses'], true), 404);
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        $filters = $this->validatedReportDates($request);
        $pengaduans = $this->reportQuery($status, $filters['from'] ?? null, $filters['to'] ?? null)->get();

        $filename = 'laporan-pengaduan-'.$status;

        if ($format === 'excel') {
            return Excel::download(new PengaduanExport($pengaduans), $filename.'.xlsx');
        }

        $buktiPendukung = $pengaduans->mapWithKeys(function (Pengaduan $pengaduan): array {
            if (! $pengaduan->bukti_pendukung || ! Storage::disk('local')->exists($pengaduan->bukti_pendukung)) {
                return [$pengaduan->getKey() => null];
            }

            $disk = Storage::disk('local');
            $mimeType = File::mimeType($disk->path($pengaduan->bukti_pendukung));

            return [$pengaduan->getKey() => 'data:'.$mimeType.';base64,'.base64_encode($disk->get($pengaduan->bukti_pendukung))];
        });

        return Pdf::loadView('admin.reports-pdf', [
            'pengaduans' => $pengaduans,
            'statusLabel' => match ($status) {
                'selesai' => 'Selesai',
                'proses' => 'Masih Diproses',
                default => 'Semua Laporan',
            },
            'buktiPendukung' => $buktiPendukung,
        ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR])],
            'bidang' => ['nullable', 'string', Rule::in(User::validBidang()), Rule::requiredIf(fn (): bool => $request->input('role') === User::ROLE_OPERATOR)],
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

    private function reportQuery(string $status = 'semua', ?string $dateFrom = null, ?string $dateTo = null): Builder
    {
        return Pengaduan::query()
            ->with('operator')
            ->when($status === 'selesai', fn (Builder $query) => $query->where('status', 'selesai'))
            ->when($status === 'proses', fn (Builder $query) => $query->whereIn('status', ['pending', 'diproses']))
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest();
    }

    /**
     * @return array{from?: string, to?: string}
     */
    private function validatedReportDates(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
    }
}
