<x-mail::message>
# Verify Your Email Address

We are so glad that you have joined us. Please verify your email address by clicking the button below.

<x-mail::button :url="$url">
Verify Email Address
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
