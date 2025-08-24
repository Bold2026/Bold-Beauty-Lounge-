@extends('hrm::layouts.contractheader')
@section('page-title')
    {{ __('Experience Certificate') }}
@endsection
@push('css')
    <style>
        html[dir="rtl"] {
            letter-spacing: 0.1px;
        }
    </style>
@endpush
@section('content')
    <div class="row justify-content-center">

        <div class="col-lg-10">
            <div class="container">
                <div>
                    <div class="card mt-5" id="printTable">

                        <div class="card-body" id="boxes">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 text-end">
                                    <img src="{{ !empty(get_file(sidebar_logo())) ? get_file(sidebar_logo()) : 'WorkDo' }}"
                                        style="max-width: 150px;" />
                                </div>
                                <p data-v-f2a183a6="">
                                <div class="p-5">{!! $experience_certificate->content !!}</div>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    @endsection
    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                generatePDF();
            });

            function generatePDF() {
                var element = document.getElementById('boxes');
                var opt = {
                    margin: 0.5,
                    filename: '{{ $employees->name }}',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2,
                        dpi: 72,
                        letterRendering: true
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'A4'
                    },
                    pagebreak: {
                        avoid: ['tr', 'td']
                    }
                };

                html2pdf().set(opt).from(element).save().then(() => {
                    closeWindow();
                });
            }


            function closeWindow() {
                setTimeout(function() {
                    window.close();
                }, 1000);
            }
        </script>
    @endpush
