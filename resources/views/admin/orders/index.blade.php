@extends('admin.layouts.app')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold">All Orders</h4>
        </div>

        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.orders') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search by name, phone or transaction ID">
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="{{ route('admin.orders') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Bulk action form -->
            <form method="POST" action="{{ route('admin.orders.updateStatus') }}" id="bulkActionForm">
                @csrf

                <div class="d-flex justify-content-start gap-2 mb-3">
                    <select name="action" class="form-select w-auto" id="bulkActionSelect">
                        <option value="">Bulk Action</option>
                        <option value="delete">Delete Selected</option>
                        <option value="processing">Mark as Processing</option>
                        <option value="delivered">Mark as Delivered</option>
                        <option value="cancelled">Mark as Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-danger" id="bulkActionBtn">Apply</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-nowrap">
                        <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Name</th>
                            <th>Variant</th>
                            <th>Customer Data</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="orderCheckbox">
                                    #{{ $order->id }}
                                </td>
                                <td>{{ $order->name }}</td>
                                <td class="d-flex align-items-start gap-3">
                                    <img src="{{ asset($order->product->image ?? 'default.png') }}" alt="Product Image" width="40" height="40" style="object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <strong>{{ $order->product->name ?? 'N/A' }}</strong><br>
                                        <small>{{$order->product->items[0]['name'] ?? ''}}</small>
                                        <small>{{ $order->order_note }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $order->product->input_name ?? 'N/A' }}</strong>: {{ $order->customer_data }}<br>
                                    <strong>{{ $order->product->input_others ?? 'N/A' }}</strong>: {{ $order->others_data }}
                                </td>
                                <td><strong>{{ $order->total }}৳</strong></td>
                                <td>{{ $order->quantity ?? 'N/A' }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td class="d-flex align-items-start gap-3">
                                    <img src="{{ asset($order->paymentMethod->icon ?? 'default.png') }}" alt="Payment Icon" width="40" height="40" style="object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <strong>{{ $order->number }}</strong><br>
                                        <small>{{ $order->transaction_id }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match(strtolower($order->status)) {
                                            'processing' => 'success',
                                            'delivered' => 'primary',
                                            'cancelled' => 'danger',
                                            'hold' => 'warning',
                                            'delivery running' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                </td>
                                <td class="d-flex gap-1">
                                    <!-- Single update forms -->
                                    @if($order->status != 'delivered')
                                        <form method="POST" action="{{ route('admin.orders.updateStatus') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                            <input type="hidden" name="status" value="Delivered">
                                            <button type="submit" class="btn btn-success btn-sm" title="Mark as Delivered">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($order->status != 'delivered' && $order->status != 'processing')
                                        <form method="POST" action="{{ route('admin.orders.updateStatus') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                            <input type="hidden" name="status" value="Processing">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Mark as Processing">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No orders found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-4">
                {{ $orders->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>

    @if(session('success') || session('error'))
        @push('scripts')
            <script>
                Swal.fire({
                    icon: '{{ session('success') ? 'success' : 'error' }}',
                    title: '{{ session('success') ? 'Success!' : 'Error!' }}',
                    text: '{{ session('success') ?? session('error') }}',
                    confirmButtonColor: '{{ session('success') ? '#3085d6' : '#d33' }}',
                    timer: 3000,
                    timerProgressBar: true
                });
            </script>
        @endpush
    @endif
@endsection

@push('scripts')
    <script>
        // Select all checkbox toggle
        document.getElementById('selectAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.orderCheckbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Prevent bulk submit without action or selection
        document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
            let selectedAction = document.getElementById('bulkActionSelect').value;
            let checkedOrders = document.querySelectorAll('.orderCheckbox:checked');

            if (!selectedAction) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Action Required',
                    text: 'Please select a bulk action.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            if (checkedOrders.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Orders Selected',
                    text: 'Please select at least one order to apply the bulk action.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
        });
    </script>
@endpush
