<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberVerificationMail;
use Carbon\Carbon;

class AdminMemberController extends Controller
{
    // Halaman Kelola Member (dengan search & pagination)
    public function kelolaMember(Request $request)
    {
        $search = $request->input('search');

        $members = MemberProfile::with('user')
            ->where('request_status', 'approved')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('no_member', 'like', "%{$search}%")
                        ->orWhere('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%")
                        ->orWhereHas('user', function($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.member.kelola', compact('members'));
    }

    // Proses daftar member langsung dari admin
    public function storeMember(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:12',
            'profesi' => 'nullable|string|max:150',
            'foto_3x4' => 'required|image|max:2048',
            'ktp' => 'nullable|image|max:2048',
            'student_card' => 'nullable|image|max:2048',
        ]);

        // 1. Buat user baru
        $user = User::create([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make('member123'), // password default
            'role' => 'konsumen',
            'status' => 'member',
            'is_verified_member' => true,
            'is_member' => true,
        ]);

        // 2. Generate nomor member
        $noMember = $this->generateNomorMember($user->id_user);

        // 3. Upload files
        $foto3x4Path = $request->file('foto_3x4')->store('member_docs', 'public');
        $ktpPath = $request->hasFile('ktp') ?
            $request->file('ktp')->store('member_docs', 'public') : null;
        $studentCardPath = $request->hasFile('student_card') ?
            $request->file('student_card')->store('member_docs', 'public') : null;

        // 4. Buat member profile
        MemberProfile::create([
            'user_id' => $user->id_user,
            'no_member' => $noMember,
            'nama_lengkap' => $request->nama_lengkap,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'profesi' => $request->profesi,
            'foto_3x4' => $foto3x4Path,
            'ktp_path' => $ktpPath,
            'student_card_path' => $studentCardPath,
            'request_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.member.kelola')
            ->with('success', 'Member berhasil didaftarkan! No. Member: ' . $noMember);
    }

    // Approve permintaan member (dari konsumen)
    public function approve($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $user = $profile->user;

        // Generate nomor member
        $noMember = $this->generateNomorMember($user->id_user);

        $profile->update([
            'no_member' => $noMember,
            'request_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $user->update([
            'is_member' => true,
            'status' => 'member',
        ]);

        return back()->with('success', 'Member disetujui! No. Member: ' . $noMember);
    }

    public function reject($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $profile->update(['request_status' => 'rejected']);
        return back()->with('success', 'Permintaan member ditolak.');
    }

    // Detail member profile
    public function showProfile($userId)
    {
        $user = User::with('memberProfile')->findOrFail($userId);
        $profile = $user->memberProfile;

        if (!$profile) {
            return back()->withErrors('Member profile tidak ditemukan.');
        }

        return view('admin.member.profile', compact('user', 'profile'));
    }

    // Generate nomor member
    private function generateNomorMember($userId)
    {
        $tanggal = date('Ymd'); // YYYYMMDD
        $idKegiatan = '02'; // Konsisten untuk daftar member

        // Cari nomor urut terakhir hari ini
        $lastMember = MemberProfile::whereDate('created_at', today())
            ->whereNotNull('no_member')
            ->orderBy('id', 'desc')
            ->first();

        $noUrut = $lastMember ?
            intval(substr($lastMember->no_member, -3)) + 1 : 1;

        // Format: YYYYMMDD + 02 + user_id(4 digit) + no_urut(3 digit)
        return $tanggal . $idKegiatan .
            str_pad($userId, 4, '0', STR_PAD_LEFT) .
            str_pad($noUrut, 3, '0', STR_PAD_LEFT);
    }

    // Kirim kode verifikasi
    public function sendCodeToUser($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $user = $profile->user;

        $code = strtoupper(Str::random(6));
        $profile->update([
            'verification_code' => $code,
            'code_sent_at' => now(),
        ]);

        Mail::to($user->email)->send(new MemberVerificationMail($user, $code));

        return back()->with('success', 'Kode verifikasi dikirim ke ' . $user->email);
    }

    public function index()
    {
        $profiles = MemberProfile::with('user')
            ->where('request_status', 'pending')
            ->orWhereNull('request_status')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.member_requests', compact('profiles'));
    }
}
