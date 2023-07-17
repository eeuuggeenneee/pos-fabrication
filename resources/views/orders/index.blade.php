@extends('layouts.admin')

@section('title', 'Orders List')
@section('content-header', 'Order List')
@section('content-actions')
    <a href="{{route('cart.index')}}" class="btn btn-primary">Open POS</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-7"> 
                <div className="col">
                    <select id="customer-select" class="form-control" onchange="customerFilter(this.value)">
                        <option value="Walk-In Customer">Walk-In Customer</option>
                        {{-- Rest of the options will be populated dynamically by the script --}}
                    </select>
                </div>
            </div>
            
            <div class="col-md-5">
                <form action="{{route('orders.index')}}">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-5">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Total</th>
                    <th>Received Amount</th>
                    <th>Status</th>
                    <th>To Pay</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td>{{$order->id}}</td>
                    <td>
                        @if(($order->customer_id)==null)
                        Walk-in Customer
                        @else
                        {{$order->getCustomerName()}}
                        @endif
                    </td>
                    <td>{{ config('settings.currency_symbol') }} {{$order->formattedTotal()}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{$order->formattedReceivedAmount()}}</td>
                    <td>
                        @if($order->receivedAmount() == 0)
                            <span class="badge badge-danger">Not Paid</span>
                        @elseif($order->receivedAmount() < $order->total())
                            <span class="badge badge-warning">Partial</span>
                        @elseif($order->receivedAmount() == $order->total())
                            <span class="badge badge-success">Paid</span>
                        @elseif($order->receivedAmount() > $order->total())
                            <span class="badge badge-info">Change</span>
                        @endif
                    </td>
                    <td>{{config('settings.currency_symbol')}} {{number_format($order->total() - $order->receivedAmount(), 2)}}</td>
                    <td>{{$order->created_at}}</td>
                    <td>
                        @component('components.modal.modal', ['modalID' => 'modalComponent-'.$order->id, 'title' => 'Delete'])
                            <p>Are you sure you want to delete {{$order->id}}</p>
                            <form id="delete-form-{{$order->id}}" action="{{ route('orders.destroy', $order) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        @endcomponent
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($receivedAmount, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        {{ $orders->render() }}
    </div>
</div>
{{-- <script>
    // this.state = {
    //         customers: []
    //     };
    loadCustomers() {
        axios.get(`/admin/customers`).then((res) => {
            const customers = res.data;
            // this.setState({ customers });

        });
    }
</script> --}}

<script> 
    function loadCustomers() {
    axios.get('/admin/customers').then(function (response) {
        var customers = response.data;
        var selectElement = document.getElementById('customer-select');

        customers.forEach(function (cus) {
            var option = document.createElement('option');
            option.value = cus.id;
            option.text = cus.first_name + ' ' + cus.last_name;
            selectElement.appendChild(option);
        });
    });
}

function customerFilter(customerId) {
    window.location = "{{ route('orders.index') }}?customer_id=" + customerId;
}

document.addEventListener('DOMContentLoaded', function () {
    loadCustomers();
});
</script>

@endsection

