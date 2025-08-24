@permission('setsalary edit')
    <div class="action-btn">
        <a href="{{ route('setsalary.show', $employees->id) }}" class="mx-3 btn bg-warning btn-sm  align-items-center"
            data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('View') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
