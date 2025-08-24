@extends('layouts.main')
@section('page-title')
    {{ __('Settings') }}
@endsection
@section('page-breadcrumb')
    {{ __('Settings') }}
@endsection
@push('css')
<link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top setting-sidebar" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            {!! getSettingMenu() !!}
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 setting-menu-div">
                    {{-- {!! getSettings() !!} --}}
                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                getSettingSection('Base');
            });
            $(document).on("click", ".setting-menu-nav", function() {
                var module = $(this).attr('data-module');
                var method = $(this).attr('data-method');
                getSettingSection(module,method);
            });

            function getSettingSection(module,method = null) {

                var url = '{{ route("setting.section.get", ["module" => ":module", "method" => ":method"]) }}';
                url = url.replace(':module', module);
                url = url.replace(':method', method ? method : '');

                $.ajax({
                    url: url,
                    type: 'get',
                    beforeSend: function() {
                        $(".loader-wrapper").removeClass('d-none');
                    },
                    success: function(data) {
                        $(".loader-wrapper").addClass('d-none');

                        if (data.status == 200) {
                            $('.setting-menu-div').empty();
                            $('.setting-menu-div').append(data.html);
                        } else {
                            // error code
                        }
                    },
                    error: function(xhr) {
                        $(".loader-wrapper").addClass('d-none');
                        toastrs('Error', xhr.responseJSON.error, 'error');
                    }
                });
            }
        </script>

    <script>
        /* Open Test Mail Modal */
        $(document).on('click', '.test-mail', function(e) {
            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');
            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');

                $.post(url, {
                    custom_email: $("#custom_email").val(),
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_host: $("#mail_host").val(),

                    _token: "{{ csrf_token() }}",
                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        })
        /* End Test Mail Modal */

        /* Test Mail Send
        ----------------------------------------*/

        $(document).on('click', '#test-send-mail', function() {
            $('#test-mail-form').ajaxForm({
                beforeSend: function() {
                    $(".loader-wrapper").removeClass('d-none');
                },
                success: function(res) {
                    $(".loader-wrapper").addClass('d-none');
                    if (res.flag == 1) {
                        toastrs('Success', res.msg, 'success');
                        $('#commonModal').modal('hide');
                    } else {
                        toastrs('Error', res.msg, 'error');
                    }
                },
                error: function(xhr) {
                    $(".loader-wrapper").addClass('d-none');
                    toastrs('Error', xhr.responseJSON.error, 'error');
                }
            }).submit();
        });
    </script>

    @endpush
