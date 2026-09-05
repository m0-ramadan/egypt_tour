@component('mail::message')
# إعادة تعيين كلمة المرور

لقد طلبت إعادة تعيين كلمة المرور لحسابك.

@component('mail::button', ['url' => $resetUrl])
اضغط هنا لإعادة التعيين
@endcomponent

الرابط صالح لمدة **{{ config('auth.passwords.users.expire') }} دقيقة**.

إذا لم تطلب هذا الإجراء، يمكنك تجاهل الرسالة.

شكراً،  
{{ config('app.name') }}
@endcomponent