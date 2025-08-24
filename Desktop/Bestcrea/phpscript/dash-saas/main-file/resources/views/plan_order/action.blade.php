@foreach ($userOrders as $userOrder)
    @if ($user->active_plan == $order->plan_id && $order->order_id == $userOrder->order_id && $order->is_refund == 0)
         <div class="action-btn">
            {!! Form::open([
                'method' => 'get',
                'route' => ['order.refund', [$order->id, $order->user_id]],
                'id' => 'refund-form-' . $order->id,
            ]) !!}
            <a href="#" class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-warning"
                data-text="{{ __('You want to confirm refund the plan. Press Yes to continue or No to go back') }}"
                data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Refund') }}"
                aria-label="{{ __('Refund') }}" data-confirm-yes="refund-form-{{ $order->id }}"><i
                    class="ti ti-refresh text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endif
@endforeach
