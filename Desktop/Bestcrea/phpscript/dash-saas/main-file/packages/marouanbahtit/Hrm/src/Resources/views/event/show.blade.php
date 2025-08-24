<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($event->title) ? $event->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ company_date_formate($event->start_date) }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ company_date_formate($event->end_date) }}</td>
            </tr>
            <tr>
                <th>{{ __('Description') }}</th>
                <td>{{ !empty($event->description) ? $event->description : '-' }}</td>
            </tr>
        </table>
    </div>
</div>
