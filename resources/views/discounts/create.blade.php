@extends('layouts.admin')

@section('title', 'Discounts')
@section('content-header', 'Discounts')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('discounts.store') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="code">Code:</label>
                    <input type="text" class="form-control" name="code" id="code" value="{{ old('code') }}"
                        required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" step="0.01" class="form-control" name="amount" id="amount"
                        value="{{ old('amount') }}" required>
                </div>
                <div class="form-group">
                    <label for="available_from">Available From:</label>
                    <input type="date" class="form-control" name="available_from" id="available_from"
                        value="{{ old('available_from') }}" required>
                </div>
                <div class="form-group">
                    <label for="expires_at">Expires At:</label>
                    <input type="date" class="form-control" name="expires_at" id="expires_at"
                        value="{{ old('expires_at') }}" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Discount</button>
                    <a href="{{ route('discounts.index') }}" class="btn btn-secondary">Go Back</a>

                </div>
            </form>
        </div>
    </div>
@endsection
