@extends('admin.layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h3 class="mb-4">Notice Update</h3>

            <!-- Add / Update Notice Form -->
            <form id="noticeForm">
                @csrf
                <div class="form-group mb-3">
                    <textarea id="noticeInput" name="notice" class="form-control" rows="3" placeholder="Write your notice here...">{{ $notice->notice ?? '' }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Notice</button>
            </form>

            <hr>

            <!-- Show Latest Notice -->
            <div id="noticeSection">
                @if($notice)
                    <div class="alert alert-info d-flex justify-content-between align-items-center mt-3">
                        <span id="noticeText">{{ $notice->notice }}</span>
                        <button class="btn btn-danger btn-sm" onclick="deleteNotice({{ $notice->id }})">Delete</button>
                    </div>
                @else
                    <p class="text-muted">No notice available.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Save Notice (AJAX)
        $('#noticeForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.notice.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        $('#noticeSection').html(`
                        <div class="alert alert-info d-flex justify-content-between align-items-center mt-3">
                            <span id="noticeText">${res.notice.notice}</span>
                            <button class="btn btn-danger btn-sm" onclick="deleteNotice(${res.notice.id})">Delete</button>
                        </div>
                    `);
                        $('#noticeInput').val('');
                    }
                },
                error: function(err) {
                    alert('Something went wrong!');
                }
            });
        });

        // Delete Notice (AJAX)
        function deleteNotice(id) {
            if(confirm("Are you sure you want to delete this notice?")) {
                $.ajax({
                    url: "/notice/" + id,
                    type: "DELETE",
                    data: {_token: "{{ csrf_token() }}"},
                    success: function(res) {
                        if (res.success) {
                            $('#noticeSection').html('<p class="text-muted">No notice available.</p>');
                        }
                    }
                });
            }
        }
    </script>
@endsection
