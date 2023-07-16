@extends('layouts.admin')

@section('content-header', 'Generate Report')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Sale Report Generator</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('generateReport') }}" target="_blank">
                            @csrf

                            <div class="form-group">
                                <label for="fromDate">From:</label>
                                <input type="date" class="form-control" id="fromDate" name="fromDate" required>
                            </div>

                            <div class="form-group">
                                <label for="toDate">To:</label>
                                <input type="date" class="form-control" id="toDate" name="toDate" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
