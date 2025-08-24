<div class="action-btn">
    <a href="{{ route('notification-template.show', [$Notification->id, getActiveLanguage()]) }}"
        class="mx-3 btn btn-sm align-items-center bg-warning"
        data-bs-toggle="tooltip" data-bs-placement="top"
        title="{{ __('Manage Your ' . $Notification->type  . ' Message') }}">
        <i class="ti ti-eye text-white"></i>
    </a>
</div>
