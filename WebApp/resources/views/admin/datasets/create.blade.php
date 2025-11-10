@extends('layouts.app')

@section('sidebar')
    <x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="container">
    <h2>Upload Dataset</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong> Please check your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.datasets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="DatasetName" class="form-label">Name Dataset</label>
            <input type="text" name="DatasetName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description (optional)</label>
            <textarea name="Description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="dataset_file" class="form-label">Choose file (.csv, .xlsx, .txt)</label>
            <input type="file" name="dataset_file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Upload</button>
        <a href="{{ route('admin.datasets.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
