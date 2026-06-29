<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentChangeLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $students = Student::all();

        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully',
            'data' => $students,
        ]);
    }

    public function show($id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $student,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:15',
            'gender' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'course' => 'nullable|string|max:100',
            'password' => 'required|min:8',
        ]);

        $lastStudent = Student::latest('id')->first();

        $nextId = $lastStudent
            ? ((int) substr($lastStudent->student_id, 3)) + 1
            : 1;

        $studentId = 'STU'.str_pad($nextId, 5, '0', STR_PAD_LEFT);

        $student = Student::create([
            ...$validated,
            'student_id' => $studentId,
            'password' => bcrypt($validated['password']),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:students,email,'.$id,
            'phone' => 'sometimes|string|max:15',
            'gender' => 'sometimes|string|max:20',
            'course' => 'sometimes|string|max:100',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
        ]);

        foreach ($validated as $field => $newValue) {

            $oldValue = $student->$field;

            if ($oldValue != $newValue) {

                StudentChangeLog::create([
                    'student_id' => $student->id,
                    'changed_field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ]);
            }
            Notification::create([
                'student_id' => $student->id,
                'title' => 'Profile Updated',
                'message' => 'Student ID '.$student->id.' updated '.$field,
                'is_read' => false,
            ]);
        }

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student,
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', '=', $request->email, 'and')->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email',
            ], 401);
        }

        if (! Hash::check($request->password, $student->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Password',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Successful',
            'student' => $student,
        ]);
    }

    public function notifications()
    {
        return response()->json(
            Notification::latest('created_at')->get()
        );
    }

    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('is_active', true)->count(),
            'inactive_students' => Student::where('is_active', false)->count(),
            'unread_notifications' => Notification::where('is_read', false)->count(),
            'total_notifications' => Notification::count(),
            'profile_updates' => StudentChangeLog::count(),

            'today_registrations' => Student::whereDate(
                'created_at',
                Carbon::today()
            )->count(),

            'week_registrations' => Student::where(
                'created_at',
                '>=',
                Carbon::now()->startOfWeek()
            )->count(),

            'month_registrations' => Student::where(
                'created_at',
                '>=',
                Carbon::now()->startOfMonth()
            )->count(),
        ];

        $recentStudents = Student::latest('created_at')
            ->take(5)
            ->get([
                'id',
                'student_id',
                'first_name',
                'last_name',
                'course',
                'is_active',
                'created_at',
            ]);

        $recentNotifications = Notification::latest('created_at')
            ->take(5)
            ->get();

        $recentUpdates = StudentChangeLog::latest('changed_at')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics fetched successfully',
            'stats' => $stats,
            'recent_students' => $recentStudents,
            'recent_notifications' => $recentNotifications,
            'recent_updates' => $recentUpdates,
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where(
            'email',
            $request->email
        )->first();

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email',
            ], 401);
        }

        if (! Hash::check(
            $request->password,
            $admin->password
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Password',
            ], 401);
        }

        // Delete old tokens (optional)
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin Login Successful',
            'token' => $token,
            'admin' => $admin,
        ]);
    }

    public function activity($id)
    {
        $student = Student::findOrFail($id);

        $activities = StudentChangeLog::where(
            'student_id',
            $student->id
        )
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => $student->first_name.' '.$student->last_name,
            ],
            'activities' => $activities,
        ]);
    }

    public function adminLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function markNotificationRead($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    public function allActivity()
    {
        $activities = StudentChangeLog::query()
            ->join(
                'students',
                'student_change_logs.student_id',
                '=',
                'students.id'
            )
            ->select(
                'student_change_logs.*',
                'students.student_id as student_code',
                'students.first_name',
                'students.last_name'
            )
            ->latest('student_change_logs.changed_at')
            ->get();

        return response()->json(
            $activities
        );
    }

    public function changePassword(
        Request $request
    ) {

       

        $student =
            $request->user();

            dd([
        'request_user' => $student,
        'entered_password' =>
            $request->current_password,
        'db_password' =>
            $student->password ?? null,
    ]);

        if (
            ! Hash::check(
                $request->current_password,
                $student->password
            )
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);

        }

        $student->update([
            'password' => bcrypt(
                $request->new_password
            ),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);

    }
}
