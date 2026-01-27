<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase', function () {
            $serviceAccount = ServiceAccount::fromJsonFile(
               storage_path('app/firebase/firebase_credentials.json')
            );
            
            return (new Factory)
                ->withServiceAccount($serviceAccount)
                ->withDatabaseUri('https://finalfirebase-5dad6-default-rtdb.firebaseio.com')
                ->create();
        });
    }

    public function boot()
    {
        //
    }
}
