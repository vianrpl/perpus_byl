<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberVerificationMail;
use App\Mail\MemberApprovedMail;
use App\Models\User;

class AdminMemberController extends Controller
{
    public function index()
    {
        $profiles = MemberProfile::with('user')->where('request_status', 'pending')->get();
        return view('admin.member_requests', compact('profiles'));
    }

    public function approve($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $profile->request_status = 'approved';
        $profile->approved_by = Auth::id();
        $profile->approved_at = now();
        $profile->save();

        $user = $profile->user;
        $user->is_member = true;
        $user->save();

        Mail::to($user->email)->send(new MemberApprovedMail($user));

        return back()->with('success', 'Pendaftar disetujui.');
    }

    public function reject($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $profile->request_status = 'rejected';
        $profile->save();

        return back()->with('success', 'Pendaftar ditolak.');
    }

    public function sendCodeToUser($id)
    {
        $profile = MemberProfile::findOrFail($id);
        $code = strtoupper(Str::random(6));
        $profile->verification_code = $code;
        $profile->code_sent_at = now();
        $profile->save();

        Mail::to($profile->user->email)->send(new MemberVerificationMail($profile->user, $code));

        return back()->with('success', 'Kode verifikasi dikirim ke user.');
    }

    public function showProfile($userId)
    {
        // Ambil profil beserta data user-nya
        $profile = MemberProfile::with('user')->where('user_id', $userId)->first();

        if (! $profile) {
            // Kalau belum ada profil, kembali dengan pesan error
            return redirect()->back()->with('error', 'Profil member belum tersedia.');
        }

        // Ambil user dari relasi profil
        $user = $profile->user;

        // Kirim data ke view
        return view('admin.profile_show', compact('user', 'profile'));
    }

}
