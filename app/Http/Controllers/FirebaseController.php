<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Kreait\Firebase\Messaging\CloudMessage;
class FirebaseController extends Controller
{
    use ValidatesRequests;
    protected $firebase;
  protected $messaging;
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase->getDatabase();
        $this->messaging = $firebase->getMessaging();
    }

    public function index()
    {
       $data =  $this->firebase
        ->getReference("users") //Pass the table name
        ->getValue(); // get value willl give us all the values of that table

        return view('laravel-form', [
            "data" => $data // pass the data into our page so we will load in our table
        ]);
    }

    function addFirebaseData(Request $request)
    {
        //Now add the data into firebase
        $username = $request->get("username");
        $email = $request->get("email");
        $age = $request->get("age");

        $data = [
            "username" => $username,
            "email" => $email,
            "age" => $age,
        ];

        $this->firebase
            ->getReference("users") //table name
            ->push() // pass an key it will automatic generate an id
            ->set($data); //Values we have to set

        return redirect('/'); //Redirect to main page directly once we have added the data
    }

    public function edit($id)
    {
        $user = $this->firebase
            ->getReference('users/' . $id)
            ->getValue();

        if (!$user) {
            return redirect('/')->with('error', 'User not found');
        }

        return view('edit-form', [
            'user' => $user,
            'id' => $id
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'username' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric'
        ]);

        $data = [
            'username' => $request->username,
            'email' => $request->email,
            'age' => $request->age
        ];

        $this->firebase
            ->getReference('users/' . $id)
            ->update($data);

        return redirect('/')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        try {
            $this->firebase
                ->getReference('users/' . $id)
                ->remove();

            return redirect('/')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

 public function notificationPage()
{
    return view('notification-demo');
}

public function saveToken(Request $request)
{
    // In a real app, you would associate this token with the authenticated user
    // For this example, we'll just store it in the session
    $request->session()->put('fcm_token', $request->token);
    return response()->json(['message' => 'Token saved successfully.']);
}

public function saveTokenByUserId(Request $request, $userId)
{
    $request->validate([
        'token' => 'required|string'
    ]);

    try {
        $user = \App\Models\User::findOrFail($userId);
        $user->fcm_token = $request->token;
        $user->save();
        return response()->json(['message' => 'Token saved successfully.', 'user_id' => $userId]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'User not found or token save failed.'], 404);
    }
}

  public function showSendForm()
    {
        return view('send-notification');
    }

    public function sendNotification(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'token' => 'required|string'
    ]);

    try {
        $message = CloudMessage::fromArray([
            'token' => $request->token,
            'notification' => [
                'title' => $request->title,
                'body' => $request->body
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ]);

        $this->messaging->send($message);

        return back()->with('success', 'Notification sent successfully!');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
    }
}
}
