@extends('admin.layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0">Payment SMS</h5>
                <form action="{{ route('admin.sms-search') }}" method="GET" class="d-flex" style="gap: 8px;">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-sm"
                           placeholder="Search by TrxID or Number">
                    <button type="submit" class="btn btn-sm btn-light">Search</button>
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success m-3">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger m-3">{{ session('error') }}</div>
            @endif

            <div class="card-body table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Order Number</th>
                        <th>Sender</th>
                        <th>Number</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data as $order)
                        <tr>
                            <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->sender }}</td>
                            <td>{{ $order->number }}</td>
                            <td>{{ $order->trxID }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted">No orders found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $data->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
