@component('mail::message')
@lang('shopwire::mail.new_order_from') {{ $order->customer['firstname'] }} {{ $order->customer['lastname'] }},

{!! $order->html !!}<br>

<table>
    <tr>
        <td>@lang('shopwire::mail.order_number')</td>
        <td>{{ $order->id }}</td>
    </tr>
    @foreach ($order->customerSorted as $key => $value)
        <tr>
            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
            <td>{{ $value }}</td>
        </tr>
    @endforeach
</table>

@endcomponent
