<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })

            ->orderBy('id_user', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
{
    $user = User::findOrFail($id);
    $user->role = $request->role;
    $user->save();

    // Kalau user yang diubah itu user yang sedang login
    if (auth()->id_user() == $user->id_user) {
        return redirect()->route('dashboard')->with('success', 'Role berhasil diubah, silakan lanjut.');
    }

    return redirect()->route('users.index')->with('success', 'Role user berhasil diubah.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('users.index')
        ->with('success','Akun berhasil dihapus');
}

}
