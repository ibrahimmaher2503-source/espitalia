<?php

namespace Database\Seeders\Auth;

use App\Events\Backend\UserCreated;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Address;

/**
 * Class UserTableSeeder.
 */
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        if (app()->environment('production') || ! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo and fixture users are disabled outside local and testing environments.');
            return;
        }

        Schema::disableForeignKeyConstraints();

        // Add the master administrator, user id of 1
        $avatarPath = config('app.avatar_base_path');


        $users = [
            [
                'first_name' => 'المدير',
                'last_name' => 'العام',
                'email' => 'admin@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 81859861',
                'date_of_birth' => '1990-08-17',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'profile_image' => public_path('/dummy-images/profile/admin/super_admin.png'),
                'avatar' => $avatarPath . 'male.webp',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'admin',
            ],
            [
                'first_name' => 'عمر',
                'last_name' => 'المالكي',
                'email' => 'demo@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 74858414',
                'date_of_birth' => '1989-02-08',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'profile_image' => public_path('/dummy-images/profile/admin/demo_admin.png'),
                'avatar' => $avatarPath . 'male.webp',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'demo_admin',
            ],

            //vendor 
            [
                'first_name' => 'خالد',
                'last_name' => 'الحربي',
                'email' => 'vendor@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 8574965162',
                'date_of_birth' => '1986-05-07',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/liam.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],
            [
                'first_name' => 'هند',
                'last_name' => 'السعيد',
                'email' => 'susan@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 2381547861',
                'date_of_birth' => '1984-10-05',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/susan.png'),
                'gender' => 'Female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],
            [
                'first_name' => 'فيصل',
                'last_name' => 'الدوسري',
                'email' => 'roberto@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 1241547857',
                'date_of_birth' => '1993-10-05',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/roberto.png'),
                'gender' => 'Male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],
            [
                'first_name' => 'عبدالله',
                'last_name' => 'النجار',
                'email' => 'richard@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 7481547856',
                'date_of_birth' => '1990-01-21',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/richard.png'),
                'gender' => 'Male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],
            [
                'first_name' => 'ماجد',
                'last_name' => 'الغامدي',
                'email' => 'ken@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 7485841458',
                'date_of_birth' => '1989-04-04',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/ken.png'),
                'gender' => 'Male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],
            [
                'first_name' => 'منى',
                'last_name' => 'الرشيدي',
                'email' => 'deborah@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 1475478605',
                'date_of_birth' => '1992-01-29',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/deborah.png'),
                'gender' => 'Female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'vendor',
            ],


            //user

            [
                'first_name' => 'محمد',
                'last_name' => 'الأحمدي',
                'email' => 'john@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 4578952512',
                'date_of_birth' => '1994-01-21',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/john.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'سعد',
                'last_name' => 'القرني',
                'email' => 'robert@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 7485961545',
                'date_of_birth' => '1993-10-05',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/robert.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'يوسف',
                'last_name' => 'الشهري',
                'email' => 'bentley@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 2563987448',
                'date_of_birth' => '1995-02-14',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/bentley.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'علي',
                'last_name' => 'الزهراني',
                'email' => 'brian@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 3565478912',
                'date_of_birth' => '1990-01-20',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/brian.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'حسن',
                'last_name' => 'المطيري',
                'email' => 'gilbert@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 5674587110',
                'date_of_birth' => '1982-08-03',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/gilbert.png'),
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'ريم',
                'last_name' => 'العنزي',
                'email' => 'pedra@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 6589741258',
                'date_of_birth' => '1988-09-25',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/pedra.png'),
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'نوف',
                'last_name' => 'الحارثي',
                'email' => 'diana@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 5687412589',
                'date_of_birth' => '1997-03-07',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/diana.png'),
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'لمى',
                'last_name' => 'البقمي',
                'email' => 'stella@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 6352897456',
                'date_of_birth' => '1992-07-09',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/stella.png'),
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],
            [
                'first_name' => 'هيا',
                'last_name' => 'السبيعي',
                'email' => 'lisa@gmail.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 3652417895',
                'date_of_birth' => '1991-04-30',

                'country' => 194,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/user/lisa.png'),
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'user',
            ],


        ];

        foreach ($users as $key => $user_data) {
            if (!env('IS_DUMMY_DATA') && $user_data['user_type'] !== 'admin') {
                continue;
            }
            $featureImage = $user_data['profile_image'] ?? null;
            $userData = Arr::except($user_data, ['profile_image', 'address']);
            $user = User::updateOrCreate(['email' => $userData['email']], $userData);

            if (isset($user_data['address'])) {
                $addresses = $user_data['address'];

                foreach ($addresses as $addressData) {
                    $address = new Address($addressData);
                    $user->address()->save($address);
                }
            }

            $user->assignRole($user_data['user_type']);


            event(new UserCreated($user));
            if (isset($featureImage)) {
                $this->attachFeatureImage($user, $featureImage);
            }
        }
        if (env('IS_DUMMY_DATA')) {
            if (env('IS_FAKE_DATA')) {
                User::factory()->count(30)->create()->each(function ($user) {
                    $user->assignRole('user');
                    $img = public_path('/dummy-images/user/customers/' . fake()->numberBetween(1, 13) . '.webp');
                    $this->attachFeatureImage($user, $img);
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function attachFeatureImage($model, $publicPath)
    {
        if (!env('IS_DUMMY_DATA_IMAGE')) return false;

        $file = new \Illuminate\Http\File($publicPath);

        $media = $model->addMedia($file)->preservingOriginal()->toMediaCollection('profile_image');

        return $media;
    }
}
