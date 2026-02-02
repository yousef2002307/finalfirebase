<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firebase Notification Receiver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Firebase Notifications</div>
                    <div class="card-body">
                        <div id="token-container" class="mb-4">
                            <!-- Token will be displayed here -->
                        </div>

                        <div id="user-token-container" class="mb-4">
                            <div class="input-group">
                                <input type="number" id="userId" class="form-control" placeholder="Enter User ID" min="1">
                                <button class="btn btn-primary" type="button" onclick="saveTokenForUser()">Save Token for User</button>
                            </div>
                            <small id="user-token-message" class="form-text"></small>
                        </div>
                        
                        <div id="notifications">
                            <h4>Received Notifications</h4>
                            <div id="notification-list" class="list-group">
                                <!-- Notifications will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Firebase SDKs -->

    <!-- Add Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.0/firebase-messaging-compat.js"></script>
    
    <script>
      // Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyBy40KPSHQH60jAyJDK8byV6vmh4fakTmY",
    authDomain: "finalfirebase-5dad6.firebaseapp.com",
    databaseURL: "https://finalfirebase-5dad6-default-rtdb.firebaseio.com",
    projectId: "finalfirebase-5dad6",
    storageBucket: "finalfirebase-5dad6.firebasestorage.app",
    messagingSenderId: "966362893191",
    appId: "1:966362893191:web:87805e6a7b0d8f4a082122",
    measurementId: "G-HSDTX05EM8"
  };

// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
let messaging;

// Register service worker and initialize messaging
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then((registration) => {
            console.log('Service Worker registered with scope:', registration.scope);
            return navigator.serviceWorker.ready;
        })
        .then((registration) => {
            console.log('Service Worker is ready');
            messaging = firebase.messaging();
            
            // Request notification permission
            requestPermission(messaging);
            
            // Handle token refresh
            messaging.onTokenRefresh(() => {
                messaging.getToken({vapidKey: 'BAW0oCAcfAoeuJESSWaODxFEZvIErE3emPT0Yx_L5qoOX9gV0Ix69136aVUQCdMfEmI1S53y-xHub-Br-3dR9Mg'})
                    .then((refreshedToken) => {
                        console.log('Token refreshed:', refreshedToken);
                        saveTokenToServer(refreshedToken);
                    })
                    .catch((err) => {
                        console.log('Unable to retrieve refreshed token', err);
                    });
            });
        })
        .catch((error) => {
            console.error('Service Worker registration failed:', error);
        });
}


function requestPermission(messaging) {
    console.log('Requesting permission...');
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log('Notification permission granted');
            getToken(messaging);
        } else {
            console.log('Unable to get permission to notify.');
        }
    });
}

function getToken(messaging) {
    messaging.getToken({vapidKey: 'BAW0oCAcfAoeuJESSWaODxFEZvIErE3emPT0Yx_L5qoOX9gV0Ix69136aVUQCdMfEmI1S53y-xHub-Br-3dR9Mg'})
    .then((currentToken) => {
        if (currentToken) {
            console.log('FCM Token:', currentToken);
            saveTokenToServer(currentToken);
            
            // Display token in UI
            const tokenContainer = document.getElementById('token-container');
            if (tokenContainer) {
                tokenContainer.innerHTML = `
                    <div class="alert alert-info">
                        <h5>Your FCM Token:</h5>
                        <div class="text-truncate">${currentToken}</div>
                        <button class="btn btn-sm btn-secondary mt-2" 
                                onclick="navigator.clipboard.writeText('${currentToken}')">
                            Copy Token
                        </button>
                    </div>
                `;
            }
        } else {
            console.log('No registration token available.');
        }
    }).catch((err) => {
        console.log('An error occurred while retrieving token:', err);
    });
}

function saveTokenToServer(token) {
    fetch('/save-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({token: token})
    })
    .then(response => response.json())
    .then(data => console.log('Token saved:', data))
    .catch(error => console.error('Error saving token:', error));
}

function saveTokenForUser() {
    const userId = document.getElementById('userId').value;
    const messageEl = document.getElementById('user-token-message');
    
    if (!userId) {
        messageEl.textContent = 'Please enter a User ID';
        messageEl.className = 'form-text text-danger';
        return;
    }

    if (!messaging) {
        messageEl.textContent = 'Messaging not initialized yet';
        messageEl.className = 'form-text text-danger';
        return;
    }

    messaging.getToken({vapidKey: 'BAW0oCAcfAoeuJESSWaODxFEZvIErE3emPT0Yx_L5qoOX9gV0Ix69136aVUQCdMfEmI1S53y-xHub-Br-3dR9Mg'})
        .then((currentToken) => {
            if (currentToken) {
                fetch(`/save-token/${userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({token: currentToken})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        messageEl.textContent = `✓ ${data.message}`;
                        messageEl.className = 'form-text text-success';
                    } else {
                        messageEl.textContent = `✗ ${data.error}`;
                        messageEl.className = 'form-text text-danger';
                    }
                })
                .catch(error => {
                    messageEl.textContent = `✗ Error: ${error.message}`;
                    messageEl.className = 'form-text text-danger';
                });
            } else {
                messageEl.textContent = 'No FCM token available';
                messageEl.className = 'form-text text-danger';
            }
        })
        .catch((err) => {
            messageEl.textContent = `✗ Error getting token: ${err.message}`;
            messageEl.className = 'form-text text-danger';
        });
}

// Handle incoming messages
messaging.onMessage((payload) => {
    console.log('Message received:', payload);
    const notification = payload.notification;
    const notificationList = document.getElementById('notification-list');
    
    if (notificationList) {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'list-group-item';
        notificationItem.innerHTML = `
            <h6 class="mb-1">${notification.title || 'No Title'}</h6>
            <p class="mb-1">${notification.body || 'No content'}</p>
            <small>${new Date().toLocaleTimeString()}</small>
        `;
        notificationList.prepend(notificationItem);
    }
});
    </script>
   
</body>
</html>


