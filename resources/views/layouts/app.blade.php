<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'نظام إدارة العجلات') - {{ config('app.name') }}</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">

    <!-- RTL Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #ced4da;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .nav-link .bi {
            margin-left: 0.5rem;
        }
        .main-content {
            padding: 1.5rem;
        }
        .navbar-brand {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
        .status-badge {
            padding: 0.5rem;
            border-radius: 5px;
            font-weight: 500;
        }
        .required-field::after {
            content: '*';
            color: red;
            margin-right: 4px;
        }
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Top bar elements - smaller font */
        .navbar .dropdown-toggle {
            font-size: 0.85rem;
        }

        .navbar .nav-link,
        .navbar .text-white {
            font-size: 0.85rem;
        }

        /* Improved dropdown styling */
        .dropdown-menu {
            z-index: 1050; /* Higher z-index to ensure it displays above other elements */
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        /* Notification dropdown positioning */
        .navbar .dropdown-menu {
            position: absolute;
            right: auto !important;
            left: auto !important;
            top: 100% !important;
            transform: none !important;
            margin-top: 0.5rem;
        }

        /* Mobile view adjustments */
        @media (max-width: 767.98px) {
            .navbar .dropdown-menu {
                position: fixed;
                right: 10px !important;
                width: calc(100% - 20px) !important;
                max-width: 300px;
                left: auto !important;
            }
        }

        /* Dropdown content styling */
        .dropdown-menu .card {
            border-radius: 4px;
            overflow: hidden;
        }

        .dropdown-menu .list-group-item {
            border-left: none;
            border-right: none;
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .dropdown-menu .list-group-item:first-child {
            border-top: none;
        }

        .dropdown-menu .list-group-item:last-child {
            border-bottom: none;
        }

        /* Small notification dot */
        .bi-circle-fill {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* Make notification text smaller */
        .dropdown-menu .small {
            font-size: 0.8rem;
        }

        .dropdown-menu .fw-bold {
            font-size: 0.85rem;
        }

        .dropdown-menu .text-muted {
            font-size: 0.75rem;
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('dashboard') }}">
            نظام إدارة العجلات
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- شريط البحث إذا كان موجودًا -->
        <div class="w-100"></div>

        <!-- قائمة الإشعارات والمستخدم -->
        <div class="d-flex align-items-center">
            <!-- قائمة الإشعارات -->
            @auth
            <div class="dropdown me-3 position-static">
                <button class="btn btn-dark position-relative dropdown-toggle" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background:none; border:none;">
                    <i class="bi bi-bell text-light"></i>
                    @if(auth()->check() && auth()->user()->unreadNotifications()->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        {{ auth()->user()->unreadNotifications()->count() > 99 ? '99+' : auth()->user()->unreadNotifications()->count() }}
                    </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0">
                    <div class="card m-0 border-0">
                        <div class="card-header bg-light d-flex justify-content-between py-2">
                            <span class="fw-bold">الإشعارات</span>
                            @if(auth()->check() && auth()->user()->unreadNotifications()->count() > 0)
                            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                    تحديد الكل كمقروء
                                </button>
                            </form>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @if(auth()->check())
                                    @forelse(auth()->user()->unreadNotifications()->take(5)->get() as $notification)
                                    <a href="{{
                                        isset($notification->data['action_url']) ? $notification->data['action_url'] :
                                        (isset($notification->data['vehicle_id']) ? route('vehicles.show', $notification->data['vehicle_id']) :
                                        (isset($notification->data['transfer_id']) ? route('transfers.show', $notification->data['transfer_id']) :
                                        (isset($notification->data['edit_request_id']) ? route('edit-requests.show', $notification->data['edit_request_id']) :
                                        route('notifications.index'))))
                                    }}" class="list-group-item list-group-item-action py-2 lh-sm">
                                        <div class="d-flex w-100 align-items-center">
                                            <div class="me-2">
                                                <i class="bi bi-circle-fill text-primary"></i>
                                            </div>
                                            <div class="small w-100">
                                                <div class="fw-bold">{{ $notification->data['title'] ?? 'إشعار جديد' }}</div>
                                                <div>{{ $notification->data['message'] ?? '' }}</div>
                                                @if(isset($notification->data['vehicle_type']))
                                                <div class="text-muted">
                                                    العجلة: {{ $notification->data['vehicle_type'] }}
                                                    @if(isset($notification->data['vehicle_number']) && $notification->data['vehicle_number'])
                                                     - {{ $notification->data['vehicle_number'] }}
                                                    @endif
                                                    @if(isset($notification->data['vehicle_name']) && $notification->data['vehicle_name'])
                                                     ({{ $notification->data['vehicle_name'] }})
                                                    @endif
                                                </div>
                                                @endif
                                                @if(isset($notification->data['old_status']) && isset($notification->data['new_status']))
                                                <div class="text-muted">
                                                    من: {{ $notification->data['old_status'] }} → إلى: {{ $notification->data['new_status'] }}
                                                </div>
                                                @endif
                                                @if(isset($notification->data['causer_name']))
                                                <div class="text-muted">
                                                    تم بواسطة: {{ $notification->data['causer_name'] }}
                                                </div>
                                                @endif
                                                <div class="text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </a>
                                    @empty
                                    <div class="list-group-item text-center text-muted py-3">لا توجد إشعارات جديدة</div>
                                    @endforelse
                                @else
                                    <div class="list-group-item text-center text-muted py-3">يرجى تسجيل الدخول لعرض الإشعارات</div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer text-center p-2 bg-light">
                            <a href="{{ route('notifications.index') }}" class="text-decoration-none">عرض جميع الإشعارات</a>
                        </div>
                    </div>
                </div>
            </div>
            @endauth

            <!-- اسم المستخدم -->
            <div class="px-3 text-white d-none d-md-block">
                @auth
                <span style="font-size: 0.85rem;">{{ auth()->user()->name }}</span>
                @endauth
            </div>

            <!-- زر تسجيل الخروج -->
            <div>
                @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link px-3 bg-dark border-0 text-white" style="font-size: 0.85rem;">
                        <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}"
                               href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> لوحة التحكم
                            </a>
                        </li>

                        @can('view vehicles')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('vehicles.*') ? 'active' : '' }}"
                               href="{{ route('vehicles.index') }}">
                                <i class="bi bi-truck"></i> العجلات
                            </a>
                        </li>
                        @endcan

                        @can('view transfers')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('transfers.*') ? 'active' : '' }}"
                               href="{{ route('transfers.index') }}">
                                <i class="bi bi-arrow-left-right"></i> المناقلات
                            </a>
                        </li>
                        @endcan

                        @can('view edit requests')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('edit-requests.*') ? 'active' : '' }}"
                               href="{{ route('edit-requests.index') }}">
                                <i class="bi bi-pencil-square"></i> طلبات التعديل
                                @php
                                    $pendingRequests = auth()->user()->hasRole('admin', 'verifier')
                                        ? \App\Models\EditRequest::where('status', 'pending')->count()
                                        : \App\Models\EditRequest::where('user_id', auth()->id())
                                            ->where('status', 'pending')->count();
                                @endphp
                                @if($pendingRequests > 0)
                                <span class="badge bg-danger">{{ $pendingRequests }}</span>
                                @endif
                            </a>
                        </li>
                        @endcan

                        <!-- Notification link available to all users -->
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('notifications.*') ? 'active' : '' }}"
                               href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell"></i> الإشعارات
                                @if(auth()->check() && auth()->user()->unreadNotifications()->count() > 0)
                                <span class="badge bg-danger">{{ auth()->user()->unreadNotifications()->count() }}</span>
                                @endif
                            </a>
                        </li>


                        @role('admin')
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('vehicles.stalled') ? 'active' : '' }}"
                               href="{{ route('vehicles.stalled') }}">
                                <i class="bi bi-clock-history"></i> العجلات المتوقفة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::routeIs('users.*') ? 'active' : '' }}"
                               href="{{ route('users.index') }}">
                                <i class="bi bi-people"></i> إدارة المستخدمين
                            </a>
                        </li>
                        @endrole
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('actions')
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

    <script>
        // Confirm delete
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const form = this.closest('form');

                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "لا يمكن التراجع عن هذا الإجراء!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'نعم، احذف',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
