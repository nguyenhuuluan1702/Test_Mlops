@extends('layouts.app')

@section('title', 'Prediction History')
@section('page-title', 'Prediction History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">History</li>
@endsection

@section('sidebar')
    <x-navigation.user-sidebar />
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Prediction History</h3>
                <div class="card-tools">
                    <a href="{{ route('user.predict') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> New Prediction
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($predictions->count() > 0)
                    <div class="table-responsive">
                        <table id="user-history-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Model Used</th>
                                    <th>pc-MXene Loading</th>
                                    <th>Laminin Peptide</th>
                                    <th>Stimulation Frequency</th>
                                    <th>Applied Voltage</th>
                                    <th>Result (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($predictions as $prediction)
                                <tr>
                                    <td>{{ $prediction->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $prediction->mlModel->MLMName }}</span>
                                        <br><small class="text-muted">{{ $prediction->mlModel->LibType }}</small>
                                    </td>
                                    <td>{{ $prediction->MXene }}</td>
                                    <td>{{ $prediction->Peptide }}</td>
                                    <td>{{ $prediction->Stimulation }}</td>
                                    <td>{{ $prediction->Voltage }}</td>
                                    <td>
                                        <strong class="text-success">{{ number_format($prediction->Result, 2) }}%</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history icon-64 text-muted-light"></i>
                        <h4 class="mt-3">No Predictions Yet</h4>
                        <p class="text-muted">You haven't made any predictions yet. Start by making your first prediction!</p>
                        <a href="{{ route('user.predict') }}" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Make First Prediction
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($predictions->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="bi bi-calculator"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Predictions</span>
                                <span class="info-box-number">{{ $predictions->total() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="bi bi-graph-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Average Result</span>
                                <span class="info-box-number">{{ number_format($predictions->avg('Result'), 2) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="bi bi-arrow-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Highest Result</span>
                                <span class="info-box-number">{{ number_format($predictions->max('Result'), 2) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="bi bi-arrow-down"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lowest Result</span>
                                <span class="info-box-number">{{ number_format($predictions->min('Result'), 2) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-tables.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/user-history-table.js') }}"></script>
@endsection
