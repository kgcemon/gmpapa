@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="fw-bold mb-4">Edit Payment Method</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('admin.payment-methods.update', $method->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Icon</label>
                            <input type="file" name="icon" class="form-control">
                            <img src="{{ $method->icon }}" alt="icon" class="mt-2" style="height:40px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Method</label>
                            <input type="text" name="method" class="form-control" value="{{ $method->method }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control tinymce-editor" rows="5">{{ $method->description }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Number</label>
                            <input type="text" name="number" class="form-control" value="{{ $method->number }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $method->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $method->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/rx33nh9mrg7zvtjoq6t8vd2ddu0l67uiw9stt1scrdjlb1dh/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '.tinymce-editor',
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            menubar: true,
            height: 250,
            content_style: "body { font-family:Arial,sans-serif; font-size:14px }"
        });
    </script>
@endsection
