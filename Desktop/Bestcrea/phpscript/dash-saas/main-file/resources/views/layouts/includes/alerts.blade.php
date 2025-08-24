 <script>
     @if (session('failed'))
         show_toastr('Falied!', '{{ session('failed') }}', 'failed');
     @endif
     @if ($errors = session('errors'))
         @if (is_object($errors))
             @foreach ($errors->all() as $error)
                 show_toastr('Error!', '{{ $error }}', 'danger');
             @endforeach
         @else
             show_toastr('Error!', '{{ session('errors') }}', 'danger');
         @endif
     @endif
     @if (session('successful'))
         show_toastr('Successfully!', '{{ session('successful') }}', 'success');
     @endif
     @if (session('success'))
         show_toastr('Success!', '{{ session('success') }}', 'success');
     @endif
     @if (session('warning'))
         show_toastr('Warning!', '{{ session('warning') }}', 'warning');
     @endif
     @if (session('status'))
         show_toastr('Great!', '{{ session('status') }}', 'info');
     @endif
 </script>
 <script>
     $(document).on('click', '.delete-action', function() {
         var form_id = $(this).attr('data-form-id')
         $.confirm({
             title: '{{ __('Alert !') }}',
             conentt: '{{ __('Are You sure ?') }}',
             buttons: {
                 confirm: function() {
                     $("#" + form_id).submit();
                 },
                 cancel: function() {}
             }
         });
     });
 </script>
 <script>
     const sweetAlert = Swal.mixin({
         customClass: {
             confirmButton: 'btn btn-success m-1',
             cancelButton: 'btn btn-danger m-1'
         },
         buttonsStyling: false,
         title: 'Are you sure?',
         text: "23sfdcvdcgvThis action can not be undone. Do you want to continue?",
         icon: 'warning',
         showCancelButton: true,
         confirmButtonText: 'Next Page',
         cancelButtonText: 'No',
         reverseButtons: true
     })
 </script>
