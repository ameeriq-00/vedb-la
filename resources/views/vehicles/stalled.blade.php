@extends('layouts.app')

@section('title', 'العجلات المتوقفة')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">العجلات المتوقفة منذ شهرين أو أكثر</h5>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="stalledTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="seized-tab" data-bs-toggle="tab" data-bs-target="#seized" type="button" role="tab" aria-controls="seized" aria-selected="true">
                    محجوزة ({{ count($stalledVehicles['seizure']) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="confiscated-tab" data-bs-toggle="tab" data-bs-target="#confiscated" type="button" role="tab" aria-controls="confiscated" aria-selected="false">
                    مصادرة ({{ count($stalledVehicles['confiscation']) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="final-degree-tab" data-bs-toggle="tab" data-bs-target="#final-degree" type="button" role="tab" aria-controls="final-degree" aria-selected="false">
                    مكتسبة ({{ count($stalledVehicles['final_degree']) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="valuation-tab" data-bs-toggle="tab" data-bs-target="#valuation" type="button" role="tab" aria-controls="valuation" aria-selected="false">
                    مثمنة ({{ count($stalledVehicles['valuation']) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="authentication-tab" data-bs-toggle="tab" data-bs-target="#authentication" type="button" role="tab" aria-controls="authentication" aria-selected="false">
                    مصادق عليها ({{ count($stalledVehicles['authentication']) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="donation-tab" data-bs-toggle="tab" data-bs-target="#donation" type="button" role="tab" aria-controls="donation" aria-selected="false">
                    مهداة ({{ count($stalledVehicles['donation']) }})
                </button>
            </li>
        </ul>

        <div class="tab-content" id="stalledTabContent">
            @foreach(['seizure' => 'محجوزة', 'confiscation' => 'مصادرة', 'final_degree' => 'مكتسبة', 'valuation' => 'مثمنة', 'authentication' => 'مصادق عليها', 'donation' => 'مهداة'] as $key => $label)
                <div class="tab-pane fade {{ $key == 'seizure' ? 'show active' : '' }}" id="{{ $key == 'seizure' ? 'seized' : $key }}" role="tabpanel" aria-labelledby="{{ $key == 'seizure' ? 'seized' : $key }}-tab">
                    @if(count($stalledVehicles[$key]) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>النوع</th>
                                        <th>الرقم</th>
                                        <th>المديرية</th>
                                        <th>تاريخ آخر تحديث</th>
                                        <th>المتهم</th>
                                        <th>المدة المتوقفة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stalledVehicles[$key] as $vehicle)
                                    <tr>
                                        <td>{{ $vehicle->id }}</td>
                                        <td>{{ $vehicle->vehicle_type }}</td>
                                        <td>{{ $vehicle->vehicle_number ?: 'بلا رقم' }}</td>
                                        <td>{{ $vehicle->directorate->name }}</td>
                                        <td>{{ $vehicle->updated_at->format('Y-m-d') }}</td>
                                        <td>{{ $vehicle->defendant_name }}</td>
                                        <td>{{ $vehicle->updated_at->diffForHumans(['parts' => 2]) }}</td>
                                        <td>
                                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> عرض
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            لا توجد عجلات متوقفة في حالة {{ $label }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
