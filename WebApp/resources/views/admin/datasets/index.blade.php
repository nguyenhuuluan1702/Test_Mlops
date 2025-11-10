@extends('layouts.app')

@section('sidebar')
<x-navigation.admin-sidebar />
@endsection

@section('content')
<div class="container">
    <h2>Dataset Management</h2>
    <a href="{{ route('admin.datasets.create') }}" class="btn btn-primary mb-3">Upload New Dataset</a>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name Dataset</th>
                <th>Description</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datasets as $dataset)
            <tr>
                <td>{{ $dataset->DatasetName }}</td>
                <td>{{ $dataset->Description }}</td>
                <td>{{ $dataset->user->FullName ?? 'Unknown' }}</td>
                <td>{{ $dataset->UploadDate }}</td>
                <td>
                    <a href="{{ route('admin.datasets.show', $dataset->DatasetId) }}" class="btn btn-info btn-sm">Details</a>

                    <!-- Nút Train Model -->
                    <a href="{{ route('admin.datasets.train.form', $dataset->DatasetId) }}"
                        class="btn btn-success btn-sm">
                        <i class="bi bi-cpu"></i> Train Model
                    </a>

                    <form action="{{ route('admin.datasets.destroy', $dataset->DatasetId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this dataset?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection