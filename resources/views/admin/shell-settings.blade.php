@extends('admin.layouts.app')

@section('content')
    <div class="p-2">

        <div class="container mt-4">
            <h4 class="fw-bold mb-3">Shell Settings</h4>

            <!-- Add Button -->
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add New Shell</button>

            <!-- Table Responsive -->
            <div id="shellTable" class="table-responsive">
                <table class="table table-bordered text-center align-middle table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Server Name</th>
                        <th>Key</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="shellBody">
                    @foreach($shells as $shell)
                        <tr id="shellRow{{ $shell->id }}">
                            <td>{{ $shell->id }}</td>
                            <td>{{ $shell->username }}</td>
                            <td>{{ $shell->servername }}</td>
                            <td>{{ $shell->key }}</td>
                            <td>
                                @if($shell->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $shell->id }}">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteBtn" data-id="{{ $shell->id }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div id="shellToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold" id="toastMessage"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addShellForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Shell</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <label>Username</label>
                    <input type="text" name="username" class="form-control mb-3" required>

                    <label>Password</label>
                    <input type="text" name="password" class="form-control mb-3" required>

                    <label>Server Name</label>
                    <input type="text" name="servername" class="form-control mb-3" required>

                    <label>Key</label>
                    <input type="text" name="key" class="form-control mb-3" required>

                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Add Shell</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach($shells as $shell)
        <div class="modal fade" id="editModal{{ $shell->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content editShellForm" data-id="{{ $shell->id }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Shell</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <label>Username</label>
                        <input type="text" name="username" class="form-control mb-3" value="{{ $shell->username }}" required>

                        <label>Password</label>
                        <input type="text" name="password" class="form-control mb-3" value="{{ $shell->password }}" required>

                        <label>Server Name</label>
                        <input type="text" name="servername" class="form-control mb-3" value="{{ $shell->servername }}" required>

                        <label>Key</label>
                        <input type="text" name="key" class="form-control mb-3" value="{{ $shell->key }}" required>

                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ $shell->status ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$shell->status ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Update Shell</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('shellToast');
            const toast = new bootstrap.Toast(toastEl);

            function showToast(message, success = true) {
                toastEl.className = 'toast align-items-center border-0 shadow-lg ' + (success ? 'bg-success text-white' : 'bg-danger text-white');
                document.getElementById('toastMessage').innerText = message;
                toast.show();
            }

            // ADD SHELL
            document.getElementById('addShellForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                fetch("{{ route('admin.shell-settings.store') }}", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.success);
                            this.reset();
                            location.reload();
                        } else {
                            showToast(data.error || 'Failed!', false);
                        }
                    }).catch(() => showToast('Something went wrong!', false));
            });

            // EDIT SHELL
            document.querySelectorAll('.editShellForm').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(`/admin/shell-settings/${id}`, {
                        method: "POST",
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.success);
                                location.reload();
                            } else {
                                showToast(data.error || 'Failed!', false);
                            }
                        }).catch(() => showToast('Something went wrong!', false));
                });
            });

            // DELETE SHELL
            document.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!confirm('Are you sure?')) return;
                    const id = this.dataset.id;

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'DELETE');

                    fetch(`/admin/shell-settings/${id}`, {
                        method: "POST",
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.success);
                                document.getElementById('shellRow' + id).remove();
                            } else {
                                showToast(data.error || 'Failed!', false);
                            }
                        }).catch(() => showToast('Something went wrong!', false));
                });
            });
        });
    </script>
@endsection
