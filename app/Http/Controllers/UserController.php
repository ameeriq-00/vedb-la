<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Directorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage users');
    }
    
    public function index(Request $request)
    {
        $query = User::with(['roles', 'directorate']);
        
        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Filter by directorate
        if ($request->has('directorate_id')) {
            $query->where('directorate_id', $request->directorate_id);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(10)->withQueryString();
        
        $roles = Role::all();
        $directorates = Directorate::all();
        
        return view('users.index', compact('users', 'roles', 'directorates'));
    }

    public function create()
    {
        $roles = Role::all();
        $directorates = Directorate::all();
        
        return view('users.create', compact('roles', 'directorates'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'directorate_id' => 'required|exists:directorates,id',
            'role' => 'required|exists:roles,name',
        ]);
        
        // Create user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'directorate_id' => $validatedData['directorate_id'],
        ]);
        
        // Assign role
        $user->assignRole($validatedData['role']);
        
        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'directorate']);
        
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $directorates = Directorate::all();
        
        return view('users.edit', compact('user', 'roles', 'directorates'));
    }

    public function update(Request $request, User $user)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'directorate_id' => 'required|exists:directorates,id',
            'role' => 'required|exists:roles,name',
        ]);
        
        // Update user
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->directorate_id = $validatedData['directorate_id'];
        
        if ($validatedData['password']) {
            $user->password = Hash::make($validatedData['password']);
        }
        
        $user->save();
        
        // Update role
        $user->syncRoles([$validatedData['role']]);
        
        return redirect()->route('users.show', $user)
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }
}