<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\EditRequest;
use App\Models\VehicleTransfer;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get statistics based on user role
        if ($user->hasRole(['admin', 'verifier'])) {
            // Admin and verifier can see all statistics
            $confiscatedCount = Vehicle::confiscated()->count();
            $governmentCount = Vehicle::government()->count();
            $pendingRequests = EditRequest::where('status', 'pending')->count();
            $pendingTransfers = VehicleTransfer::whereNull('return_date')->count();
            
            $vehiclesByStatus = [
                'محجوزة' => Vehicle::confiscated()->where('seizure_status', 'محجوزة')->count(),
                'مصادرة' => Vehicle::confiscated()->where('seizure_status', 'مصادرة')->count(),
                'مفرج عنها' => Vehicle::confiscated()->where('seizure_status', 'مفرج عنها')->count(),
                'مكتسبة' => Vehicle::confiscated()->where('final_degree_status', 'مكتسبة')->count(),
                'مصادق عليها' => Vehicle::confiscated()->where('authentication_status', 'تمت المصادقة عليها')->count(),
                'مثمنة' => Vehicle::confiscated()->where('valuation_status', 'مثمنة')->count()
            ];
        } elseif ($user->hasRole('data_entry')) {
            // Data entry only sees their directorate's vehicles
            $confiscatedCount = Vehicle::confiscated()
                ->where('directorate_id', $user->directorate_id)
                ->count();
            
            $governmentCount = 0; // Data entry doesn't manage government vehicles
            
            $pendingRequests = EditRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
            
            $pendingTransfers = 0; // Data entry doesn't manage transfers
            
            $vehiclesByStatus = [
                'محجوزة' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('seizure_status', 'محجوزة')
                    ->count(),
                'مصادرة' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('seizure_status', 'مصادرة')
                    ->count(),
                'مفرج عنها' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('seizure_status', 'مفرج عنها')
                    ->count(),
                'مكتسبة' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('final_degree_status', 'مكتسبة')
                    ->count(),
                'مصادق عليها' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('authentication_status', 'تمت المصادقة عليها')
                    ->count(),
                'مثمنة' => Vehicle::confiscated()
                    ->where('directorate_id', $user->directorate_id)
                    ->where('valuation_status', 'مثمنة')
                    ->count()
            ];
        } elseif ($user->hasRole('vehicles_dept')) {
            // Vehicles department sees vehicles that are in final degree or authenticated
            $confiscatedCount = Vehicle::confiscated()
                ->where(function($q) {
                    $q->where('final_degree_status', 'مكتسبة')
                      ->orWhere('authentication_status', 'تمت المصادقة عليها');
                })
                ->count();
            
            $governmentCount = Vehicle::government()->count();
            
            $pendingTransfers = VehicleTransfer::whereNull('return_date')->count();
            $pendingRequests = 0; // Vehicles dept doesn't handle edit requests
            
            $vehiclesByStatus = [
                'مكتسبة' => Vehicle::confiscated()->where('final_degree_status', 'مكتسبة')->count(),
                'مصادق عليها' => Vehicle::confiscated()->where('authentication_status', 'تمت المصادقة عليها')->count(),
                'مثمنة' => Vehicle::confiscated()->where('valuation_status', 'مثمنة')->count()
            ];
        } else {
            // Recipients only see transferred vehicles
            $confiscatedCount = Vehicle::confiscated()
                ->whereHas('transfers', function($q) use ($user) {
                    $q->where('destination_directorate_id', $user->directorate_id);
                })
                ->count();
            
            $governmentCount = Vehicle::government()
                ->whereHas('transfers', function($q) use ($user) {
                    $q->where('destination_directorate_id', $user->directorate_id);
                })
                ->count();
            
            $pendingRequests = 0;
            $pendingTransfers = 0;
            
            $vehiclesByStatus = [];
        }
        
        // Recent activities (for admin and verifier only)
        if ($user->hasRole(['admin', 'verifier'])) {
            $recentVehicles = Vehicle::latest()->take(5)->get();
            $recentRequests = EditRequest::latest()->take(5)->get();
            $recentTransfers = VehicleTransfer::latest()->take(5)->get();
        } elseif ($user->hasRole('data_entry')) {
            $recentVehicles = Vehicle::where('directorate_id', $user->directorate_id)
                ->latest()
                ->take(5)
                ->get();
            $recentRequests = EditRequest::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
            $recentTransfers = collect();
        } elseif ($user->hasRole('vehicles_dept')) {
            $recentVehicles = Vehicle::where(function($q) {
                $q->where('type', 'government')
                  ->orWhere(function($q2) {
                      $q2->where('type', 'confiscated')
                         ->where(function($q3) {
                             $q3->where('final_degree_status', 'مكتسبة')
                                ->orWhere('authentication_status', 'تمت المصادقة عليها');
                         });
                  });
            })
            ->latest()
            ->take(5)
            ->get();
            
            $recentRequests = collect();
            $recentTransfers = VehicleTransfer::latest()->take(5)->get();
        } else {
            $recentVehicles = Vehicle::whereHas('transfers', function($q) use ($user) {
                $q->where('destination_directorate_id', $user->directorate_id);
            })
            ->latest()
            ->take(5)
            ->get();
            
            $recentRequests = collect();
            $recentTransfers = collect();
        }
        
        return view('dashboard', compact(
            'confiscatedCount',
            'governmentCount',
            'pendingRequests',
            'pendingTransfers',
            'vehiclesByStatus',
            'recentVehicles',
            'recentRequests',
            'recentTransfers'
        ));
    }
}