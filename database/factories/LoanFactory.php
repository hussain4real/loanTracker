<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Enums\Purpose;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Loan::class;

    // protected $realUsers = [
    //     'داوود سليمان مسعود الحوسني',
    //     'حارب عبدالله القطيطي',
    //     'حسن صالح الصالحي',
    //     'موسى جمعه الحسني',
    //     'عزة راشد السعيدي',
    //     'خلود سالم المقبالي',
    //     'حسين الحسناوي',
    //     'رجب عبدالله النوبي',
    //     'سيف سالم سعيد الحامدي',
    //     'المهندس خميس ناصر',
    //     'حمد سعيد القاسمي',
    //     'فهد مبارك حمود الوهيبي',
    //     'عبدالله قاسم القاسمي',
    //     'احمد سالم سعيد الحضرمي',
    //     'سعيد راشد سعيد القطيطي',
    //     'علي سعيد هديب القطيطي',
    //     'فاطمة محمد سليمان البلوشي',
    //     'هلال جمعه سيف اليوسفي',
    //     'فهد طالب الغافري',
    //     'جاسم محمد المغيزوي',
    //     'سالم خميس العلوي',
    //     'احمد سعيد حمدان الضباري',
    //     'هاني ثاني خميس القطيطي',
    //     'عمار احمد محمد اللمكي',
    //     'وليد درويش العبيداني',
    //     'سالم سيف الغافري',
    //     'طالب سيف سعيد الضباري',
    //     'فيصل حميد محمد الحجري',
    //     'احمد علي عبيد الضباري',
    //     'منى صالح الحجري ',
    //     'ريه الحجري ام بدر',
    //     'عبدالله صالح عبيد الضباري',
    //     'عبدالله مبارك عبيد السناني (1 )',

    //     'ناصر بن سعيد المسعودي',
    //     'سيف مبارك عبدالله الضباري',
    //     'جمعه فقير البلوشي ',
    //     'حسن عبدالباقي منهال - سوري',
    //     'منذر حمدان الغافري',
    //     'عبدالله محمد عبدالله البلوشي',
    //     'داؤود المغيزوي',
    //     'خليل ابراهيم العجمي',
    //     'علياء البلوشي',
    //     'عادل الرديني',
    //     'ابتسام عبدالله درويش الصالحي',
    //     'حسن علي الشيادي',
    //     'عادل خميس سالم العجمي',
    //     'محمد كرم علي البلوشي',
    //     'عبدالله سعيد سالم العجمي',
    //     'الشيخ حميد بن محمد الحجري',
    //     'عبدالله صالح حارب الصالحي',
    //     'خميس صالح حارب الصالحي',
    //     'عمر بن محمد بن عبدالله الرواحي',
    //     'عبدالرحمن بن عبدالله الرواحي',
    //     'محمود الجابري',
    //     'حسن جمعه القطيطي',
    //     'نبيل عيسى محمد الحسني',
    //     'هادي عيسى مجمد الحسني',
    //     'عدنان عيسى محمد الحسني',
    //     'حمد علي الحجري ',
    //     'عبدالمطلب حمد نجف العجمي',
    //     'جمعة بن فقير البلوشي',
    // ];

    // protected $realAmounts = [
    //     29000,
    //     50750,
    //     7800,
    //     111000,
    //     13600,
    //     33650,
    //     11000,
    //     500,
    //     100,
    //     100000,
    //     200,
    //     15787,
    //     120500,
    //     20250,
    //     2450,
    //     93000,
    //     38000,
    //     63600,
    //     40140,
    //     5750,
    //     3000,
    //     40500,
    //     1500,
    //     14000,
    //     36303,
    //     25660,
    //     104700,
    //     120000,
    //     1200,
    //     11000,
    //     3000,
    //     155000,
    //     14000,
    //     7000,
    //     2200,
    //     36220,
    //     100900,
    //     500000,
    //     2500,
    //     1500,
    //     400,
    //     6900,
    //     1500,
    //     4500,
    //     4500,
    //     700,
    //     1200,
    //     3000,
    //     3000,
    //     15800,
    //     38200,
    //     18650,
    //     10000,
    //     33675,
    //     37450,
    //     84600,
    //     29000,
    //     15000,
    //     7000,
    //     1300,
    //     32620,
    // ];

    // public function getRealUsers(): array
    // {
    //     return $this->realUsers;
    // }

    // public function getRealAmounts(): array
    // {
    //     return $this->realAmounts;
    // }

    public function definition(): array
    {

        /* copy realUsers array */
        // $users = $this->realUsers;
        // $amounts = $this->realAmounts;

        // echo 'Users count: '.count($users)."\n";
        // echo 'Amounts count: '.count($amounts)."\n";

        // // Print line numbers where they differ
        // foreach ($users as $key => $value) {
        //     if (! isset($amounts[$key])) {
        //         echo 'Missing amount for user at index: '.$key."\n";
        //     }
        // }

        // // Validate arrays at start
        // if (count($this->realAmounts) !== count($this->realUsers)) {
        //     throw new \RuntimeException(
        //         'Arrays mismatch: realUsers('.count($this->realUsers).
        //             ') vs realAmounts('.count($this->realAmounts).')'
        //     );
        // }
        // static $index = -1;
        // $index++;

        // // Reset index if we reach end of array
        // if ($index >= count($this->realUsers)) {
        //     $index = 0;
        // }
        // $approved_at = $this->faker->dateTimeBetween('-1 month', 'now');
        $purpose = $this->faker->randomElement(Purpose::cases());

        // // Get real user name and amount using current index
        // $userName = $this->realUsers[$index];
        // $amount = $this->realAmounts[$index];

        return [
            // 'user_id' => \App\Models\User::factory()->state(['name' => $userName]),
            // // 'amount' => $this->faker->randomFloat(3, 1000, 10000),
            // 'amount' => $amount,
            'purpose' => $purpose,
            'status' => LoanStatus::APPROVED,
            // 'approved_at' => $this->faker->dateTimeBetween('now', 'now'),
            'approved_at' => '2024-01-01',
            // 'duration' => $this->faker->numberBetween(24),
            'duration' => 24,
            // 'due_date' => $this->faker->dateTimeBetween($approved_at, '+1 year'),
            // due date should be 1 year from approved date
            'due_date' => '2026-01-01',
            // 'payment_schedule' => [
            //     'monthly_payment' => $this->faker->randomFloat(3, 100, 1000),
            //     'payment_start_date' => $approved_at,
            // ],
        ];
    }
}
