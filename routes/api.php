<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ParticipantAudioController;
use App\Http\Controllers\StreamingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SpeechRequestController;
use App\Http\Controllers\WebRTCController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('/reset-password', [AuthController::class, 'resetPassword']);


/*
|--------------------------------------------------------------------------
| Protected Routes - Sanctum
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | Conferences
    |--------------------------------------------------------------------------
    */


    Route::get('/conferences/my', [
        ConferenceController::class,
        'myConferences'
    ]);

    Route::post('/conferences', [
        ConferenceController::class,
        'store'
    ]);

    Route::get('/conferences/{conference}', [
        ConferenceController::class,
        'show'
    ]);

    Route::post('/conferences/{conference}/invite', [
        ConferenceController::class,
        'invite'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Participant Audio
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/conferences/{conference}/participants/{user}/mute',
        [ParticipantAudioController::class, 'mute']
    );

    Route::post(
        '/conferences/{conference}/participants/{user}/unmute',
        [ParticipantAudioController::class, 'unmute']
    );


    /*
    |--------------------------------------------------------------------------
    | Streaming
    |--------------------------------------------------------------------------
    */

    Route::post('/conferences/{conference}/stream/start', [
        StreamingController::class,
        'start'
    ]);

    Route::post('/conferences/{conference}/stream/stop', [
        StreamingController::class,
        'stop'
    ]);


    Route::post('/conferences/{conference}/end', [
        StreamingController::class, 'end']);

    /*
    |--------------------------------------------------------------------------
    | Invitations
    |--------------------------------------------------------------------------
    */

    Route::get('/invitations', [
        InvitationController::class,
        'index'
    ]);

    Route::post('/invitations/{id}/accept', [
        InvitationController::class,
        'accept'
    ]);

    Route::post('/invitations/{id}/reject', [
        InvitationController::class,
        'reject'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Speech Requests
    |--------------------------------------------------------------------------
    */

    Route::post('/speech-requests', [
        SpeechRequestController::class,
        'requestToSpeak'
    ]);

    Route::get('/speech-requests/pending', [
        SpeechRequestController::class,
        'pendingRequests'
    ]);

    Route::post('/speech-requests/{id}/approve', [
        SpeechRequestController::class,
        'approveRequest'
    ]);

    Route::post('/speech-requests/{id}/reject', [
        SpeechRequestController::class,
        'rejectRequest'
    ]);

    Route::post('/speech-requests/end', [
        SpeechRequestController::class,
        'endSpeaking'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    */

    Route::post('/send-message', [
        MessageController::class,
        'sendMessage'
    ]);

    Route::post('/send-message', [
    MessageController::class,
    'sendMessage'
]);

Route::get('/conferences/{conference}/messages', [
    MessageController::class,
    'getMessages'
]);
    /*
    |--------------------------------------------------------------------------
    | WebRTC Signaling
    |--------------------------------------------------------------------------
    */

    Route::post('/webrtc/signal', [
        WebRTCController::class,
        'signal'
    ]);

});
