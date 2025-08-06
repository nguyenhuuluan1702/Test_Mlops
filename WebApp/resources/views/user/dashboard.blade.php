@extends('layouts.app')

@section('title', 'User Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('sidebar')
    <x-navigation.user-sidebar />
@endsection

@section('content')
<div class="row">
    <!-- Info Boxes using Small Box Component -->
    <x-ui.small-box 
        color="info" 
        :value="$totalPredictions" 
        label="Total Predictions" 
        icon="fas fa-calculator" 
        :link="route('user.history')" 
        linkText="View History" />
    
    <x-ui.small-box 
        color="success" 
        value="Make New" 
        label="Prediction" 
        icon="fas fa-plus-circle" 
        :link="route('user.predict')" 
        linkText="Start Predicting" />
</div>

<div class="row">
    <div class="col-12">
        <x-ui.card title="Recent Predictions">
            @if($recentPredictions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Model</th>
                                <th>MXene</th>
                                <th>Peptide</th>
                                <th>Stimulation</th>
                                <th>Voltage</th>
                                <th>Result (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPredictions as $prediction)
                            <tr>
                                <td>{{ $prediction->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $prediction->mlModel->MLMName }}</td>
                                <td>{{ $prediction->MXene }}</td>
                                <td>{{ $prediction->Peptide }}</td>
                                <td>{{ $prediction->Stimulation }}</td>
                                <td>{{ $prediction->Voltage }}</td>
                                <td><strong>{{ number_format($prediction->Result, 2) }}%</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-2">No predictions yet</h5>
                    <p class="text-muted">Start by making your first prediction</p>
                    <a href="{{ route('user.predict') }}" class="btn btn-primary">
                        <i class="fas fa-calculator me-1"></i>
                        Make Prediction
                    </a>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <x-ui.card title="About Schwann Cell Viability Prediction">
            <p>This system uses machine learning models to predict Schwann cell viability based on various parameters:</p>
            <ul>
                <li><strong>pc-MXene loading:</strong> The concentration of MXene material (0 to 0.03)</li>
                <li><strong>Laminin peptide:</strong> The amount of laminin peptide used (0 to 5.9)</li>
                <li><strong>Stimulation frequency:</strong> The frequency of electrical stimulation (0 to 3)</li>
                <li><strong>Applied voltage:</strong> The voltage applied during the process (0 to 3)</li>
            </ul>
            <p>The prediction result shows the percentage viability of Schwann cells under the given conditions.</p>
        </x-ui.card>
    </div>
</div>
@endsection
