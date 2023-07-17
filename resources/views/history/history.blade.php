@extends('layouts.admin')

@section('title', 'Inventory History')
@section('content-header', 'Inventory History')
@section('content-actions')
<a href="" class="btn btn-primary">Print</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card product-list">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Action</th>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($history as $history)
                <tr>
                    <td>{{$history->id}}</td>
                    <td>{{$history->action}}</td>
                    <td>{{$history->product}}</td>
                    <td><img class="product-img" src="{{ Storage::url($history->image) }}" alt=""></td>
                    <td>
                        <span
                            class="right badge badge-{{ $history->status ? 'success' : 'danger' }}">{{$history->status ? 'Active' : 'Inactive'}}</span>
                    </td>
                    <td>{{$history->description}}</td>
                    <td>{{$history->updated_at}}</td>
                 
                </tr>
                @endforeach
            </tbody>
        </table>
        {{-- {{ $products->render() }} --}}
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $(document).on('click', '.btn-delete', function () {
            $this = $(this);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this product?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No',
                reverseButtons: true
                }).then((result) => {
                if (result.value) {
                    $.post($this.data('url'), {_method: 'DELETE', _token: '{{csrf_token()}}'}, function (res) {
                        $this.closest('tr').fadeOut(500, function () {
                            $(this).remove();
                        })
                    })
                }
            })
        })
    })
</script>
@endsection
