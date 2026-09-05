<h2>لديك رسالة جديدة:</h2>

<p><strong>الاسم:</strong> {{ $contact->first_name }} {{ $contact->last_name }}</p>
<p><strong>رقم الهاتف:</strong> {{ $contact->phone }}</p>
<p><strong>البريد الإلكتروني:</strong> {{ $contact->email }}</p>
<p><strong>الشركة:</strong> {{ $contact->company ?? '—' }}</p>

<p><strong>الرسالة:</strong></p>
<p>{{ $contact->message }}</p>
