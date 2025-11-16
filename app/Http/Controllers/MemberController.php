<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberVerificationMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function showAskPage()
    {
        $user = Auth::user();
        $profile = MemberProfile::firstOrCreate(['user_id' => $user->id_user]);
        return view('member.ask', compact('user', 'profile'));
    }

    public function sendVerificationCode(Request $req)
    {
        $user = Auth::user();
        $profile = MemberProfile::firstOrCreate(['user_id' => $user->id_user]);

        $code = strtoupper(Str::random(6));
        $profile->verification_code = $code;
        $profile->code_sent_at = Carbon::now();
        $profile->save();

        Mail::to($user->email)->send(new MemberVerificationMail($user, $code));

        return back()->with('success', 'Kode verifikasi sudah dikirim ke email Anda.');
    }

    public function verifyCode(Request $req)
    {
        $req->validate(['code' => 'required|string']);
        $user = Auth::user();
        $profile = MemberProfile::where('user_id', $user->id_user)->first();

        if (!$profile) return back()->withErrors('Silakan minta kode terlebih dahulu.');

        if ($profile->verification_code === $req->code && $profile->code_sent_at && Carbon::parse($profile->code_sent_at)->diffInMinutes(now()) <= 30) {
            $user->is_verified_member = true;
            $user->save();

            // status user otomatis berubah
            $user->status = 'terverifikasi';
            $user->save();

            return redirect()->route('member.ask')->with('success', 'Email terverifikasi, tombol Daftar Member muncul.');
        } else {
            return back()->withErrors('Kode salah atau kadaluarsa (30 menit).');
        }
    }

    public function submitRegistration(Request $req)
    {
        $user = Auth::user();
        $profile = MemberProfile::firstOrCreate(['user_id' => $user->id_user]);

        $req->validate([
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:12',
            'profesi' => 'nullable|string|max:150',
            'ktp' => 'nullable|image|max:2048',
            'student_card' => 'nullable|image|max:2048',
        ]);

        if (!$user->is_verified_member) {
            return back()->withErrors('Email belum terverifikasi.');
        }

        if ($req->hasFile('ktp')) {
            $profile->ktp_path = $req->file('ktp')->store('ktp', 'public');
        }

        if ($req->hasFile('student_card')) {
            $profile->student_card_path = $req->file('student_card')->store('student_card', 'public');
        }


        $profile->nama_lengkap = $req->nama_lengkap;
        $profile->alamat = $req->alamat;
        $profile->no_hp = $req->no_hp;
        $profile->profesi = $req->profesi;
        $profile->request_status = 'pending';
        $profile->save();

        // status otomatis berubah setelah daftar member
        $user->status = 'member';
        $user->save();

        return back()->with('success', 'Permintaan pendaftaran terkirim. Tunggu persetujuan admin.');
    }
}
