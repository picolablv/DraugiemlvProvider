# Draugiem.lv Pases funkcionalitātes pievienošana Laravel Socialite

Pagaidām laravel/socialite  atbalsta autentifikāciju ar Facebook, Twitter, Google un Github. Tā kā saviem projektiem 
Latvijā populāri būtu izmantot arī draugiem.lv pases funkcionalitāti, tad par pamatu pases ieviešanai varat ņemt 
DraugiemlvProvider.php kodu.

## Instalēšana

* Uzinstalējiet laravel/socialite
* Iekopējiet DraugiemlvProvider.php projekta direktorijā. Es to, piemēram, lieku app/Helpers/DraugiemlvProvider.php
* Izveidojiet atbilstošu konfigurācijas ierakstu config/services.php ar savu konfigurāciju

    
         'draugiemlv' => [
                'client_id' => '666555665',                                  // Aplikācijas_id
                'client_secret' => '7c437d28be62b492151788f6c827afd6 ',      // Api atslēga
                'redirect' => 'http://example.com/auth/draugiemlv/callback', // Adrese uz kuru jānonāk pēc pieprasījuma veikšanas
            ] 


* Pievieno draugiem provaideri Socialite menedžerī. To var, piemēram, izdarīt app\Providers\AppServiceProvider.php 
    
    Izveidojam funkciju, kas paveic darbu un norādām, lai tā ielādējas
    
        private function bootDraugiemlvSocialite()
        {
            $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
            $socialite->extend(
                'draugiemlv',
                function ($app) use ($socialite) {
                    $config = $app['config']['services.draugiemlv'];
                    return $socialite->buildProvider(\App\Helpers\DraugiemlvProvider::class, $config);
                }
            );
        }
       
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            //
            $this->bootDraugiemlvSocialite();
        }
        
 
* Izveidojiet vai papildiniet kontroliera metodes

        /**
         * @return mixed
         */
        public function redirectToDraugiemlv()
        {
            return Socialite::driver('draugiemlv')->redirect();
        }
    
        /**
         * @return \Illuminate\Http\RedirectResponse
         */
        public function returnFromDraugiemlv()
        {
            $user = Socialite::driver('draugiemlv')->user();            
        }

* Norādiet routse.php ceļu uz kontroliera metodēm


    Route::get('auth/draugiemlv', 'Auth\AuthController@redirectToDraugiemlv');
    Route::get('auth/draugiemlv/callback', 'Auth\AuthController@returnFromDraugiemlv');


### Todo

* Vienkāršotāk veikt pieprasījumus, jo lietotāja informāciju var iegūt jau pie _authorize_ pieprasījuma