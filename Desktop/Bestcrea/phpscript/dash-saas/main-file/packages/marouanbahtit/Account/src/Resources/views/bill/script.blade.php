<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('packages/workdo/Account/src/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>
<script>
    $(document).ready(function() {
        generatePDF();
    });

    function generatePDF() {
        var element = document.getElementById('boxes');
        var opt = {
            margin: 0.5,
            filename: '{{ Workdo\Account\Entities\Bill::billNumberFormat($bill->bill_id,$bill->created_by)}}',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, dpi: 72, letterRendering: true },
            jsPDF: { unit: 'in', format: 'A4' },
            pagebreak: { avoid: ['tr', 'td'] }
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
