@extends('admin.layouts.app')

@section('content')
    <div class="container mt-4">

        <!-- Dashboard Overview -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Dashboard Overview</h5>

                {{-- ===== Today, Yesterday, Custom Range, Alltime ===== --}}
                <div class="row g-4">
                    @php
                        $sections = [
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'custom' => 'Custom Range',
                            'alltime' => 'All Time'
                        ];
                    @endphp

                    @foreach($sections as $key => $title)
                        @if(isset($dashboardData[$key]))
                            <div class="col-12 mb-4">
                                <h5 class="fw-bold text-primary mb-3">{{ $title }}</h5>
                                <div class="row g-3">
                                    @php
                                        $data = $dashboardData[$key];
                                        $items = [
                                            ['label' => 'Total Orders', 'value' => $data['total_orders'] ?? 0, 'icon' => 'fas fa-shopping-cart', 'bg' => 'bg-success'],
                                            ['label' => 'Completed Orders', 'value' => $data['completed_orders'] ?? 0, 'icon' => 'fas fa-check-circle', 'bg' => 'bg-primary'],
                                            ['label' => 'Users', 'value' => $data['users'] ?? 0, 'icon' => 'fas fa-users', 'bg' => 'bg-warning'],
                                            ['label' => 'New Users', 'value' => $data['new_users'] ?? 0, 'icon' => 'fas fa-user-plus', 'bg' => 'bg-info'],
                                            ['label' => 'Sales (Completed Orders)', 'value' => number_format($data['sales'] ?? 0, 2) . '৳', 'icon' => 'fas fa-dollar-sign', 'bg' => 'bg-danger'],
                                        ];
                                    @endphp

                                    @foreach($items as $item)
                                        <div class="col-md-3 col-sm-6">
                                            <div class="d-flex justify-content-between align-items-center border rounded p-3 h-100 bg-light shadow-sm hover-shadow">
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-box {{ $item['bg'] }} text-white rounded d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                                                        <i class="{{ $item['icon'] }} fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold fs-5">{{ $item['value'] }}</div>
                                                        <small class="text-muted">{{ $item['label'] }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-4">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-dark">
                        <tr>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Total (৳)</th>
                            <th>Status</th>
                            <th>TrxID</th>
                            <th>Placed</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->name }}</td>
                                <td>{{ $order->product->name ?? '' }}</td>
                                <td>{{ $order->item->name ?? '' }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td class="text-bg-info">{{ number_format($order->total, 2) }}৳</td>
                                <td>
                                    @php
                                        $statusClass = match($order->status) {
                                            'completed' => 'badge bg-primary',
                                            'processing' => 'badge bg-success',
                                            'hold' => 'badge bg-warning',
                                            'cancelled' => 'badge bg-secondary',
                                            'delivery running' => 'badge bg-orange',
                                            default => 'badge bg-light text-dark',
                                        };
                                    @endphp
                                    <span class="{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>{{ $order->transaction_id ?? '-' }}</td>
                                <td>{{ $order->created_at ? $order->created_at->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">No orders found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Custom Orange Badge --}}
    <style>
        .bg-orange {
            background-color: #fd7e14 !important;
            color: #fff !important;
        }
    </style>
@endsection
