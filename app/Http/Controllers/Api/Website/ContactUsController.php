<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\ContactUsRequest;
use App\Http\Requests\Website\StoreContactUsReplyRequest;
use App\Http\Resources\Website\ContactUsReplyResource;
use App\Http\Resources\Website\ContactUsResource;
use App\Mail\ContactUsNotification;
use App\Models\ContactUs;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    use ApiResponseTrait;
    public function store(ContactUsRequest $request)
    {

        $contact = ContactUs::create([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "email" => $request->email ?? null,
            "phone" => $request->phone,
            "company" => $request->type_complaint,
            "message" => $request->message,
            "user_id" => auth('sanctum')->id() ?? null,
        ]);

        //   Mail::to(env("EMAIL_RECIEVERED"))->send(new ContactUsNotification($contact));

        return $this->success(new ContactUsResource($contact), 'تم جلب بيانات بنجاح');
    }
    public function index()
    {
        return ContactUsResource::collection(ContactUs::where('user_id', auth('sanctum')->id())->latest()->get());
    }
    public function show($id)
    {
        $contact = ContactUs::where('user_id', auth('sanctum')->id())->findOrFail($id);
        return new ContactUsResource($contact);
    }
    public function reply(StoreContactUsReplyRequest $request, ContactUs $contact)
    {

        $user = $request->user();

        // ✅ مهم: امنع المستخدم يبعت على تذكرة مش بتاعته (لو التذكرة مرتبطة بمستخدم)
        if (!is_null($contact->user_id) && (int) $contact->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'غير مسموح لك بالرد على هذه التذكرة.',
            ], 403);
        }

        // ✅ (اختياري) امنع الرد لو التذكرة مقفولة
        if (($contact->status ?? null) === 'closed') {
            return response()->json([
                'status' => false,
                'message' => 'هذه التذكرة مغلقة ولا يمكن الرد عليها.',
            ], 422);
        }

        $reply = $contact->replies()->create([
            'user_id'     => $user->id,
            'message'     => $request->message,
            'sender_type' => 'user',
        ]);

        // ✅ (اختياري) لو عندك status خليه in_progress أول ما اليوزر يرد
        if ($contact->isFillable('status') && ($contact->status ?? null) === 'pending') {
            $contact->update(['status' => 'in_progress']);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم إرسال الرسالة بنجاح.',
            'data'    => new ContactUsReplyResource($reply->load('user')),
        ], 201);
    }
}
