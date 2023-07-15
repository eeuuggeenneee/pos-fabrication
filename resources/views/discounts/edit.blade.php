@extends('layouts.admin')

@section('title', 'Edit Discounts')
@section('content-header', 'Edit Discounts')


@section('content')
    <div class="card">
        <div class="card-body">
            <h3>Edit Discount</h3>
            <form action="{{ route('discounts.update', $discount) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="code">Code:</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ $discount->code }}" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" id="amount" class="form-control" value="{{ $discount->amount }}" required>
                </div>
                <div class="form-group">
                    <label for="available_from">Available From:</label>
                    <input type="date" name="available_from" id="available_from" class="form-control" value="{{ $discount->available_from }}" required>
                </div>
                <div class="form-group">
                    <label for="expires_at">Expires At:</label>
                    <input type="date" name="expires_at" id="expires_at" class="form-control" value="{{ $discount->expires_at }}" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Discount</button>
                    <a href="{{ route('discounts.index') }}" class="btn btn-secondary">Go Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection
