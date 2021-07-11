@component('mail::message')
# Welcome {{$user['name']}}!

Thank you for signing up for {{ config('app.name') }}!<br>
Please verify your email address by clicking the button below.


@component('mail::button', ['url' => "http://testapi.test/api/email/verify/?hash={$user['token']}&id={$user['id']}"])
Confirm my account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
