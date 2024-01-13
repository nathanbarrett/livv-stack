<x-mail::message>
# Reset Your Password

Click on the link below to reset your password.

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
