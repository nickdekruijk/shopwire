@component('mail::message')
@lang('shopwire::mail.new_order_from') {{ $order->customer['firstname'] }} {{ $order->customer['lastname'] }},

{!! $order->html !!}<br>

<table>
    @foreach ($order->customerSorted as $key => $value)
        <tr>
            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
            <td>{{ $value }}</td>
        </tr>
    @endforeach
</table>

@endcomponent
