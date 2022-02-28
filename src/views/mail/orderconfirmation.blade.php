@component('mail::message')
@lang('shopwire::mail.salutation') {{ $order->customer['firstname'] }} {{ $order->customer['lastname'] }},

@lang('shopwire::mail.thank_you_for_your_order') @lang('shopwire::mail.order_number') {{ $order->id }}.

@lang('shopwire::mail.we_have_received_your') {{ $order->payment_method }} @lang('shopwire::mail.payment_and_the_following_order')

{!! $order->html !!}<br>

@lang('shopwire::mail.your_order_will_be_shipped_to') 
{{ $order->customer['company'] }}
<br>{{ $order->customer['firstname'] }} {{ $order->customer['lastname'] }}
<br>{{ $order->customer['address'] }}
<br>{{ $order->customer['postcode'] }}&nbsp; {{ $order->customer['city'] }}
<br>{{ Countries::getOne($order->customer['country'], app()->getLocale()) }}

@lang('shopwire::mail.question_about_your_order') 

@lang('shopwire::mail.greeting') 

@lang('shopwire::mail.sender_name') 

@endcomponent
