## Laravel Sanctum Introduction [![My Skills](https://skills.thijs.gg/icons?i=laravel)](https://laravel.com/)

Laravel Sanctum provides a featherweight authentication system for SPAs (single page applications), mobile applications, and simple, token based APIs. Sanctum allows each user of your application to generate multiple API tokens for their account. These tokens may be granted abilities / scopes which specify which actions the tokens are allowed to perform. <br />
First, Sanctum is a simple package you may use to issue API tokens to your users without the complication of OAuth.<br />
Second, Sanctum exists to offer a simple way to authenticate single page applications (SPAs) that need to communicate with a Laravel powered API.

## Sanctum Install Version Details

Reference the table below for the correct version to use in conjunction with the
version of Laravel you have installed and goldspecdigital/laravel-eloquent-uuid package

| Laravel | Sanctum | Uuid   |
| ------- | ------- | ------ |
| `v9.*`  | `v3.0`  | `v9.0` |


:warning: **READ ALL THE RELATED DOCUMENT CAREFULLY BEFORE IMPLEMENT SANCTUM ON LARAVEL 9.x**: 


## Note

WE ARE USING TWO PACKAGE HERE:
1. Implement the goldspecdigital/laravel-eloquent-uuid package for uuid.
2. Implement the laravel/sanctum package for authentication (By Default).


### Step-1 Install UUID Package 

```bash
composer require goldspecdigital/laravel-eloquent-uuid:^9.0
```

### Step 2: Using the Uuid trait In app/Models/User.php 

```php
<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class User extends Authenticatable
{
    use Uuid;
    protected $keyType = 'string'; //The "type" of the auto-incrementing ID.
    public $incrementing = false; // Indicates if the IDs are auto-incrementing.
} 

```

### Step 3: Update Users Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();  // Primary key.
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

```

### Step 4: Add Sanctum Package
```bash
composer require laravel/sanctum
```

### Step 5: publish the Sanctum configuration and Migration files
Type answer to yes
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Step 6: Update Sanctum Table
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('tokenable'); //Update column here 
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};


```

### Step 7: Migrate Database 
Hint: Create A Database Backup Before Use

```bash
php artisan migrate:refresh --force
```


### Step 8: Configuration Sanctum's Middleware: app/Http/Kernel.php file
```php

'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

```
### Step 9: Update User Model app/Models/User.php
```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; //Here
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuid;


    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

    
```


### Step 10: Modify database/seeders/DatabaseSeeder.php

```php
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::truncate();
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => Hash::make("12345"),
        ]);
    }
}

```

### Step 11: Create Controller
```bash
php artisan make:controller LoginController
```
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Feel Free To Visit https://navjotsinghprince.com
class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = 'test@user.com';
        $password = '12345';

        if (Auth::attempt(['email' =>  $email, 'password' =>  $password])) {
            $user = Auth::user();
            $success['access_token'] =  $user->createToken('PrinceFerozepuria')->plainTextToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();
        $response = [
            "user" =>  $user,
            "message" => "success"
        ];
        return response()->json($response, 200);
    }
}

```

### Step 12: Create Routes routes/api.php
```php
<?php

Route::post('login', [LoginController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('getUser', [LoginController::class, 'getUser']);
});

```

### Step 13: Run
```bash
php artisan key:generate
php artisan db:seed
php artisan optimize:clear
composer dump-autoload
php artisan serve
```


## Authors
:point_right: [Navjot Singh Prince](https://github.com/navjotsinghprince)

See also the site of [contributor](https://navjotsinghprince.com)
who participated in this project.

## Contact
If you discover any question within sanctum, please send an e-mail to Prince Ferozepuria via [fzr@navjotsinghprince.com](mailto:fzr@navjotsinghprince.com). Your all questions will be answered.


## Buy Me A Coffee! :coffee: 
Feel free to buy me a coffee at [__Buy me a coffee! :coffee:__]( https://ko-fi.com/princeferozepuria), I would be really grateful for anything, be it coffee or just a kind comment towards my work, that helps me a lot.

## Donation
The Sanctum with user's uuid project is completely free to use, however, it has taken a lot of time to build. If you would like to show your appreciation by leaving a small donation, you can do so by clicking here [here](https://www.paypal.com/paypalme/navjotsinghprince). Thanks!

## License
This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md)
file for details.

