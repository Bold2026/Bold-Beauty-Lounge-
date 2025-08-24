@extends('helpdesk_ticket.master')

@section('page-title')
    {{ __('Ticket') }} - {{ $ticket->ticket_id }}
@endsection
@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
@endpush

@section('content')
    <div class="auth-wrapper create-ticket ticket-desc justify-content-between flex-column auth-v1">
        <div class="bg-auth-side"></div>
        <div class="auth-content">
            <div class="row align-items-center justify-content-center text-start">
                <div class="col-12">
                    <div class="card rounded-4">
                        <div class="ticket-title text-center bg-primary">
                            <h4 class="text-white mb-0">{{ __('Ticket') }} - {{ $ticket->ticket_id }}</h4>
                        </div>
                       <div class="card-body w-100 p-sm-4 p-3">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header p-3">
                                <h6 class="mb-0">{{ $ticket->name }}
                                    <small>({{ $ticket->created_at->diffForHumans() }})</small></h6>
                            </div>
                            <div class="card-body p-3 w-100">
                                <div>
                                    <p>{!! $ticket->description !!}</p>
                                </div>
                                @php
                                    $attachments = json_decode($ticket->attachments);
                                @endphp
                                @if (!is_null($attachments) && count($attachments) > 0)
                                    <div>
                                        <b>{{ __('Attachments') }} :</b>
                                        <ul class="list-group list-group-flush">
                                            @foreach ($attachments as $index => $attachment)
                                                <li class="list-group-item pb-0 px-0">
                                                    {{ $attachment->name }}<a download="{{ $attachment->name }}"
                                                        href="{{ get_file($attachment->path) }}" class="edit-icon py-1 ml-2"
                                                        title="{{ __('Download') }}"><i
                                                            class="fa fa-download ms-2"></i></a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @foreach ($ticket->conversions as $conversion)
                            <div class="card mb-3">
                                <div class="card-header p-3">
                                    <h6 class="mb-0">{{ $conversion->replyBy()->name }}
                                        <small>({{ $conversion->created_at->diffForHumans() }})</small>
                                    </h6>
                                </div>
                                <div class="card-body p-3 w-100">
                                    <div>{!! $conversion->description !!}</div>
                                    @php
                                        $attachments = json_decode($conversion->attachments);
                                    @endphp
                                    @if (count($attachments))
                                        <div class="m-1">
                                            <b>{{ __('Attacbhments') }} :</b>
                                            <ul class="list-group list-group-flush">

                                                @foreach ($attachments as $index => $attachment)
                                                    <li class="list-group-item px-0">
                                                        {{ $attachment->name }}<a download="{{ $attachment->name }}"
                                                            href="{{ get_file($attachment->path) }}"
                                                            class="edit-icon py-1 ml-2" title="{{ __('Download') }}"><i
                                                                class="fa fa-download ms-2"></i></a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if ($ticket->status != 'Closed')
                            <div class="card mb-3">
                                <div class="card-body p-3 w-100">
                                    <form method="post"
                                        action="{{ route('helpdesk-ticket.reply', [$ticket->ticket_id]) }}"
                                        enctype="multipart/form-data" id="ckeditorForm">
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                <label class="require form-label">{{ __('Description') }}</label>

                                                <textarea name="reply_description"
                                                    class="form-control summernote  {{ !empty($errors->first('reply_description')) ? 'is-invalid' : '' }}" required
                                                    id="help-desc">{{ old('reply_description') }}</textarea>

                                                <div class="invalid-feedback">
                                                    {{ $errors->first('reply_description') }}
                                                </div>
                                                <p class="text-danger d-none" id="skill_validation">
                                                    {{ __('Description filed is required.') }}</p>
                                            </div>
                                            <div class="form-group col-md-12 file-group">
                                                <label class="require form-label">{{ __('Attachments') }}</label>
                                                <label
                                                    class="form-label"><small>({{ __('You can select multiple files') }})</small></label>
                                                <div class="choose-file form-group mb-0">
                                                    <label for="file" class="form-label mb-0">

                                                        <input type="file"
                                                            class="form-control {{ $errors->has('reply_attachments') ? 'is-invalid' : '' }}"
                                                            multiple="" name="reply_attachments[]" id="file"
                                                            data-filename="multiple_reply_file_selection">
                                                        <div class="invalid-feedback">
                                                            {{ $errors->first('reply_attachments') }}
                                                        </div>
                                                    </label>
                                                    <p class="multiple_reply_file_selection"></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0 col-md-12">
                                            <div class="text-center">
                                                <input type="hidden" name="status" value="In Progress" />
                                                <button
                                                    class="btn btn-submit btn-primary btn-block">{{ __('Submit') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="card">
                                <div class="card-body p-3">
                                    <p class="text-blue font-weight-bold text-center mb-0">
                                        {{ __('Ticket is closed you cannot replay.') }}</p>
                                </div>
                            </div>
                        @endif

                       </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

    <script>

        $("#SummernoteForm").submit(function(e)
        {

            var desc = $("#reply_description").val();
            if(!isNaN(desc))
            {
                $('#skill_validation').removeClass('d-none')
                event.preventDefault();
            }
            else
            {
                $('#skill_validation').addClass('d-none')
            }

        });
    </script>

    <script>
        // for Choose file
        $(document).on('change', 'input[type=file]', function() {
            var names = '';
            var files = $('input[type=file]')[0].files;

            for (var i = 0; i < files.length; i++) {
                names += files[i].name + '<br>';
            }
            $('.' + $(this).attr('data-filename')).html(names);
        });
    </script>
@endpush
