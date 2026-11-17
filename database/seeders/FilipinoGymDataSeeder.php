<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Models\CheckIn;
use App\Models\ClassBooking;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FilipinoGymDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Identify or Create the Primary Gym for the Owner
        $ownerEmail = '09262874496adrian@gmail.com';
        $owner = User::where('email', $ownerEmail)->first();

        $gymsData = [
            [
                'name' => 'Bakal & Pawis Fitness Hub - Diliman',
                'slug' => 'bakal-pawis-diliman',
                'address' => '88 Maginhawa Street, Diliman',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'phone' => '0289201122',
                'email' => 'diliman@bakalpawis.ph',
                'owner_name' => $owner?->name ?? 'Adrian Viernes',
                'owner_email' => $ownerEmail,
                'owner_phone' => '09262874496',
                'subscription_status' => 'active',
                'subscription_plan' => 'professional',
            ],
            [
                'name' => 'Sigsig Athletic Club - BGC',
                'slug' => 'sigla-athletic-bgc',
                'address' => '26th Street corner 5th Avenue, Bonifacio Global City',
                'city' => 'Taguig',
                'province' => 'Metro Manila',
                'phone' => '0288889900',
                'email' => 'bgc@siglafitness.ph',
                'owner_name' => 'Coach Bato Dela Cruz',
                'owner_email' => 'bato.delacruz@siglafitness.ph',
                'owner_phone' => '09175551234',
                'subscription_status' => 'active',
                'subscription_plan' => 'enterprise',
            ],
            [
                'name' => 'Matatag Boxing & Functional Gym - Cebu',
                'slug' => 'matatag-gym-cebu',
                'address' => '45 Gorordo Avenue, Lahug',
                'city' => 'Cebu City',
                'province' => 'Cebu',
                'phone' => '0322314567',
                'email' => 'cebu@matataggym.ph',
                'owner_name' => 'Manny Pacquiao Jr.',
                'owner_email' => 'manny@matataggym.ph',
                'owner_phone' => '09189998877',
                'subscription_status' => 'trialing',
                'subscription_plan' => 'starter',
            ],
        ];

        foreach ($gymsData as $gymInfo) {
            $gym = Gym::firstOrCreate(
                ['slug' => $gymInfo['slug']],
                array_merge($gymInfo, [
                    'scanner_key' => 'FLSCAN-' . Str::upper(Str::random(32)),
                    'is_active' => true,
                    'trial_ends_at' => now()->addDays(30),
                    'subscription_ends_at' => now()->addMonths(6),
                ])
            );

            // Assign primary gym to Owner user if not yet assigned
            if ($owner && $gym->slug === 'bakal-pawis-diliman' && blank($owner->gym_id)) {
                $owner->update(['gym_id' => $gym->id]);
            }

            // 2. Local Membership Plans
            $plans = [
                [
                    'name' => 'Daily Walk-in (Pang-Arawan)',
                    'description' => 'Isang araw na access sa gym floor, weights, at locker room.',
                    'price' => 120.00,
                    'duration_days' => 1,
                ],
                [
                    'name' => 'Monthly Unlimited Barkada Pass',
                    'description' => '30-araw na unlimited weights, cardio, at functional area access.',
                    'price' => 1200.00,
                    'duration_days' => 30,
                ],
                [
                    'name' => 'Student / Senior Citizen Discounted',
                    'description' => 'Para sa mga mag-aaral at senior citizens na may valid ID.',
                    'price' => 950.00,
                    'duration_days' => 30,
                ],
                [
                    'name' => '3 Months Bayanihan VIP',
                    'description' => 'Quarterly access na may kasamang libreng personal trainer assessment.',
                    'price' => 3200.00,
                    'duration_days' => 90,
                ],
                [
                    'name' => 'Annual Buwis-Buhay Elite',
                    'description' => '1 taong VIP access sa lahat ng branches at libreng gym jersey.',
                    'price' => 11500.00,
                    'duration_days' => 365,
                ],
            ];

            $createdPlans = [];
            foreach ($plans as $p) {
                $createdPlans[] = Plan::firstOrCreate(
                    ['gym_id' => $gym->id, 'name' => $p['name']],
                    array_merge($p, ['is_active' => true])
                );
            }

            // 3. Filipino Gym Classes
            $classes = [
                ['name' => 'Pawis & HIIT Circuit', 'instructor' => 'Coach Dindo Marasigan', 'capacity' => 20, 'duration_minutes' => 60],
                ['name' => 'Pinoy Boxing & Sparring', 'instructor' => 'Coach Nonito "The Flash" Rivera', 'capacity' => 15, 'duration_minutes' => 75],
                ['name' => 'Zumba Kalye Fiesta', 'instructor' => 'Teacher Marites Reyes', 'capacity' => 30, 'duration_minutes' => 60],
                ['name' => 'Kettlebell & Barbell Strongman', 'instructor' => 'Coach Jomar Batungbakal', 'capacity' => 12, 'duration_minutes' => 90],
                ['name' => 'Matatag Core & Yoga Flow', 'instructor' => 'Coach Lianne De Jesus', 'capacity' => 18, 'duration_minutes' => 50],
            ];

            $createdClasses = [];
            foreach ($classes as $index => $c) {
                $createdClasses[] = GymClass::firstOrCreate(
                    ['gym_id' => $gym->id, 'name' => $c['name']],
                    [
                        'instructor' => $c['instructor'],
                        'capacity' => $c['capacity'],
                        'duration_minutes' => $c['duration_minutes'],
                        'schedule_time' => now()->addDays($index + 1)->setTime(rand(7, 18), 0),
                    ]
                );
            }

            // 4. Filipino Gym Members (Authentic Pinoy Names & Places)
            $membersData = [
                [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'phone' => '0917' . rand(1000000, 9999999),
                    'email' => 'juan.delacruz@gmail.com',
                    'address' => 'Barangay Central, Quezon City',
                    'emergency_contact_name' => 'Maria Dela Cruz (Nanay)',
                    'emergency_contact_phone' => '09181234567',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Clarisse',
                    'last_name' => 'Santos-Agoncillo',
                    'phone' => '0922' . rand(1000000, 9999999),
                    'email' => 'clarisse.agoncillo@yahoo.com',
                    'address' => 'Salcedo Village, Makati City',
                    'emergency_contact_name' => 'Renato Agoncillo (Asawa)',
                    'emergency_contact_phone' => '09179876543',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Mark Joseph',
                    'last_name' => 'Bautista',
                    'phone' => '0995' . rand(1000000, 9999999),
                    'email' => 'mj.bautista@gmail.com',
                    'address' => 'Barangay Malate, Manila',
                    'emergency_contact_name' => 'Tita Baby Bautista',
                    'emergency_contact_phone' => '09265554433',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Danielle',
                    'last_name' => 'Villanueva',
                    'phone' => '0919' . rand(1000000, 9999999),
                    'email' => 'dani.villanueva@outlook.com',
                    'address' => 'Poblacion, Mandaluyong City',
                    'emergency_contact_name' => 'Kuya Ronald Villanueva',
                    'emergency_contact_phone' => '09153332211',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Reynold',
                    'last_name' => 'Dimagiba',
                    'phone' => '0927' . rand(1000000, 9999999),
                    'email' => 'reynold.dimagiba@gmail.com',
                    'address' => 'Kapitolyo, Pasig City',
                    'emergency_contact_name' => 'Aling Nena Dimagiba',
                    'emergency_contact_phone' => '09204445566',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Arianne',
                    'last_name' => 'Tolentino',
                    'phone' => '0939' . rand(1000000, 9999999),
                    'email' => 'arianne.tolentino@gmail.com',
                    'address' => 'BF Homes, Parañaque City',
                    'emergency_contact_name' => 'Grace Tolentino (Ate)',
                    'emergency_contact_phone' => '09167778899',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Dingdong',
                    'last_name' => 'Macaraeg',
                    'phone' => '0908' . rand(1000000, 9999999),
                    'email' => 'dingdong.macaraeg@gmail.com',
                    'address' => 'San Antonio Village, Pasig City',
                    'emergency_contact_name' => 'Lito Macaraeg',
                    'emergency_contact_phone' => '09281112233',
                    'status' => MemberStatus::Inactive,
                ],
                [
                    'first_name' => 'Princess',
                    'last_name' => 'Catacutan',
                    'phone' => '0945' . rand(1000000, 9999999),
                    'email' => 'cess.catacutan@gmail.com',
                    'address' => 'Barangay Commonwealth, Quezon City',
                    'emergency_contact_name' => 'Tatay Noel Catacutan',
                    'emergency_contact_phone' => '09176665544',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Christian',
                    'last_name' => 'Magtanggol',
                    'phone' => '0917' . rand(1000000, 9999999),
                    'email' => 'christian.magtanggol@gmail.com',
                    'address' => 'Alabang, Muntinlupa City',
                    'emergency_contact_name' => 'Atty. Magtanggol (Tatay)',
                    'emergency_contact_phone' => '09182223344',
                    'status' => MemberStatus::Active,
                ],
                [
                    'first_name' => 'Bea Bianca',
                    'last_name' => 'Resurreccion',
                    'phone' => '0926' . rand(1000000, 9999999),
                    'email' => 'bea.resurreccion@gmail.com',
                    'address' => 'Heroes Hill, Quezon City',
                    'emergency_contact_name' => 'Doctora Resurreccion',
                    'emergency_contact_phone' => '09951112233',
                    'status' => MemberStatus::Active,
                ],
            ];

            foreach ($membersData as $mIndex => $mData) {
                $fullName = $mData['first_name'] . ' ' . $mData['last_name'];
                $member = Member::firstOrCreate(
                    ['gym_id' => $gym->id, 'email' => $mData['email']],
                    [
                        'name' => $fullName,
                        'phone' => $mData['phone'],
                        'address' => $mData['address'],
                        'status' => $mData['status'],
                        'qr_code' => 'MEM-PH-' . Str::upper(Str::random(12)),
                        'emergency_contact_name' => $mData['emergency_contact_name'],
                        'emergency_contact_phone' => $mData['emergency_contact_phone'],
                        'joined_at' => now()->subDays(rand(10, 200)),
                        'notes' => 'Taga ' . $mData['address'] . '. Fitness enthusiast.',
                    ]
                );

                // Assign Subscriptions
                $chosenPlan = $createdPlans[$mIndex % count($createdPlans)];
                $isExpired = $mData['status'] === MemberStatus::Inactive;
                $startsAt = $isExpired ? now()->subDays(60) : now()->subDays(rand(2, 20));
                $endsAt = $isExpired ? now()->subDays(30) : now()->addDays(rand(10, 45));

                $sub = Subscription::firstOrCreate(
                    [
                        'gym_id' => $gym->id,
                        'member_id' => $member->id,
                        'plan_id' => $chosenPlan->id,
                    ],
                    [
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'amount' => $chosenPlan->price,
                        'amount_paid' => $chosenPlan->price,
                        'payment_status' => PaymentStatus::Paid,
                        'payment_method' => $mIndex % 2 === 0 ? 'gcash' : 'cash',
                        'reference' => 'GCASH-' . rand(10000000, 99999999),
                        'paid_at' => $startsAt,
                    ]
                );

                // Record Payment transaction
                Payment::firstOrCreate(
                    [
                        'gym_id' => $gym->id,
                        'member_id' => $member->id,
                        'subscription_id' => $sub->id,
                    ],
                    [
                        'provider' => 'manual',
                        'reference_number' => 'OR-' . rand(100000, 999999),
                        'amount' => $chosenPlan->price,
                        'currency' => 'PHP',
                        'status' => 'paid',
                        'payment_method' => $mIndex % 2 === 0 ? 'gcash' : 'cash',
                        'paid_at' => $startsAt,
                    ]
                );

                // Add Check-In history for Active members
                if ($mData['status'] === MemberStatus::Active) {
                    CheckIn::firstOrCreate(
                        [
                            'gym_id' => $gym->id,
                            'member_id' => $member->id,
                            'scanned_at' => now()->subHours(rand(1, 8)),
                        ],
                        [
                            'subscription_id' => $sub->id,
                            'status' => 'active',
                            'message' => 'Valid entry via QR Scanner Station (Entrance Gate)',
                        ]
                    );

                    // Book into a class
                    if (! empty($createdClasses)) {
                        $class = $createdClasses[$mIndex % count($createdClasses)];
                        ClassBooking::firstOrCreate(
                            [
                                'gym_class_id' => $class->id,
                                'member_id' => $member->id,
                            ],
                            [
                                'status' => 'confirmed',
                            ]
                        );
                    }
                }
            }
        }
    }
}
