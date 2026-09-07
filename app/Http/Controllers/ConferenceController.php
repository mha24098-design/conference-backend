<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ConferenceParticipation;
use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConferenceController extends Controller
{
    /**
     * إنشاء مؤتمر جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $conference = Conference::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'admin_id' => Auth::id(),
            'status' => 'scheduled',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // إضافة منشئ المؤتمر كمشارك مقبول
        ConferenceParticipation::create([
            'user_id' => Auth::id(),
            'conference_id' => $conference->id,
            'participation_status' => 'accepted',
            'current_status' => 'listening',
            'is_muted' => false,
            'forced_muted_by_admin' => false,
            'is_streaming' => false,
        ]);

        return response()->json([
            'message' => 'Conference created successfully',
            'conference' => $conference
        ], 201);
    }


    /**
     * جلب المؤتمرات التي أنشأها المستخدم الحالي
     */
    public function myConferences()
    {
        $user = Auth::user();

        $conferences = Conference::with('category')
            ->where('admin_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'conferences' => $conferences
        ]);
    }


    /**
     * دعوة مستخدم إلى المؤتمر
     */
    public function invite(Request $request, Conference $conference)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // التأكد أن المستخدم الحالي هو Admin المؤتمر
        if ($conference->admin_id !== Auth::id()) {
            return response()->json([
                'message' => 'Only conference admin can invite users'
            ], 403);
        }

        // البحث عن المستخدم بواسطة الإيميل
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // التأكد أن المستخدم ليس مدعواً مسبقاً
        $exists = ConferenceParticipation::where(
            'conference_id',
            $conference->id
        )->where(
            'user_id',
            $user->id
        )->exists();

        if ($exists) {
            return response()->json([
                'message' => 'User already invited'
            ], 400);
        }

        // إنشاء الدعوة بحالة pending
        ConferenceParticipation::create([
            'user_id' => $user->id,
            'conference_id' => $conference->id,
            'participation_status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Invitation sent successfully'
        ]);
    }


    /**
     * عرض تفاصيل المؤتمر والمشاركين
     */
    public function show(Conference $conference)
    {
        // جلب مشاركة المستخدم الحالي
        $myParticipation = ConferenceParticipation::where(
            'conference_id',
            $conference->id
        )
        ->where(
            'user_id',
            Auth::id()
        )
        ->where(
            'participation_status',
            'accepted'
        )
        ->first();

        // التأكد أن المستخدم مشارك مقبول
        if (!$myParticipation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        // تحميل بيانات التصنيف والـ Admin
        $conference->load([
            'category',
            'admin'
        ]);

        // المشاركون المقبولون
        $participants = ConferenceParticipation::with('user')
            ->where('conference_id', $conference->id)
            ->where('participation_status', 'accepted')
            ->orderBy('id', 'asc')
            ->get();

        // المدعوون الذين لم يقبلوا أو يرفضوا بعد
        $invitedParticipants = ConferenceParticipation::with('user')
            ->where('conference_id', $conference->id)
            ->where('participation_status', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'conference' => $conference,
                'my_participation' => $myParticipation,
                'participants' => $participants,
                'invited_participants' => $invitedParticipants,
                'channel' => 'private-conference.' . $conference->id
            ]
        ]);
    }
}
