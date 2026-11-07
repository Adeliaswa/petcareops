<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffAccountController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.staff.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function store(
        Request $request,
        AccountService $accountService
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                Rule::in(['admin', 'resepsionis', 'groomer']),
            ],
        ]);

        $accountService->buat($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Akun staf berhasil dibuat.');
    }

    public function destroy(
        User $user,
        AccountService $accountService
    ): RedirectResponse {
        $accountService->nonaktifkan($user, auth()->user());

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Akun staf berhasil dinonaktifkan.');
    }

    public function resetPassword(
        User $user,
        AccountService $accountService
    ): RedirectResponse {
        $temporaryPassword = $accountService->resetPassword($user);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Password akun berhasil direset.')
            ->with('temporary_password', $temporaryPassword)
            ->with('reset_user_name', $user->name);
    }

    public function activate(User $user, AccountService $accountService): RedirectResponse
    {
        $accountService->aktifkan($user);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Akun staf berhasil diaktifkan kembali.');
    }

}