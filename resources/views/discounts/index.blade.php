@extends('layouts.admin')

@section('title', 'Discounts')
@section('content-header', 'Discounts')
@section('content-actions')
<a href="{{route('discounts.create')}}" class="btn btn-primary">Create Discount</a>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead class="">
                    <tr>
                        <th scope="col">Code</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Available From</th>
                        <th scope="col">Expires At</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($discounts as $discount)
                        <tr>
                            <td>{{ $discount->code }}</td>
                            <td>{{ $discount->amount }}</td>
                            <td>{{ $discount->available_from }}</td>
                            <td>{{ $discount->expires_at}}</td>
                            <td>
                                <a href="{{ route('discounts.edit', $discount) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>

                                <button class="btn btn-danger btn-delete"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

@endsection
