<div>
    {{-- <button type="button" data-toggle="modal" data-target="#modalComponent">{{ $buttonText }}</button> --}}
    <button class="btn btn-danger btn-delete" data-toggle="modal" data-target="#{{$modalID}}"><i
        class="fas fa-trash"></i></button>

    <div class="modal fade" id="{{$modalID}}" tabindex="-1" role="dialog" aria-labelledby="modalComponentLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalComponentLabel">{{ $title }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>