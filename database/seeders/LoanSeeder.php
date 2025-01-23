<?php

namespace Database\Seeders;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Database\Factories\LoanFactory;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    protected $realUsers = [
        'داوود سليمان مسعود الحوسني',
        'حارب عبدالله القطيطي',
        'حسن صالح الصالحي',
        'موسى جمعه الحسني',
        'عزة راشد السعيدي',
        'خلود سالم المقبالي',
        'حسين الحسناوي',
        'رجب عبدالله النوبي',
        'سيف سالم سعيد الحامدي',
        'المهندس خميس ناصر',
        'حمد سعيد القاسمي',
        'فهد مبارك حمود الوهيبي',
        'عبدالله قاسم القاسمي',
        'احمد سالم سعيد الحضرمي',
        'سعيد راشد سعيد القطيطي',
        'علي سعيد هديب القطيطي',
        'فاطمة محمد سليمان البلوشي',
        'هلال جمعه سيف اليوسفي',
        'فهد طالب الغافري',
        'جاسم محمد المغيزوي',
        'سالم خميس العلوي',
        'احمد سعيد حمدان الضباري',
        'هاني ثاني خميس القطيطي',
        'عمار احمد محمد اللمكي',
        'وليد درويش العبيداني',
        'سالم سيف الغافري',
        'طالب سيف سعيد الضباري',
        'فيصل حميد محمد الحجري',
        'احمد علي عبيد الضباري',
        'منى صالح الحجري ',
        'ريه الحجري ام بدر',
        'عبدالله صالح عبيد الضباري',
        'عبدالله مبارك عبيد السناني (1 )',

        'ناصر بن سعيد المسعودي',
        'سيف مبارك عبدالله الضباري',
        'جمعه فقير البلوشي ',
        'حسن عبدالباقي منهال - سوري',
        'منذر حمدان الغافري',
        'عبدالله محمد عبدالله البلوشي',
        'داؤود المغيزوي',
        'خليل ابراهيم العجمي',
        'علياء البلوشي',
        'عادل الرديني',
        'ابتسام عبدالله درويش الصالحي',
        'حسن علي الشيادي',
        'عادل خميس سالم العجمي',
        'محمد كرم علي البلوشي',
        'عبدالله سعيد سالم العجمي',
        'الشيخ حميد بن محمد الحجري',
        'عبدالله صالح حارب الصالحي',
        'خميس صالح حارب الصالحي',
        'عمر بن محمد بن عبدالله الرواحي',
        'عبدالرحمن بن عبدالله الرواحي',
        'محمود الجابري',
        'حسن جمعه القطيطي',
        'نبيل عيسى محمد الحسني',
        'هادي عيسى مجمد الحسني',
        'عدنان عيسى محمد الحسني',
        'حمد علي الحجري ',
        'عبدالمطلب حمد نجف العجمي',
        'جمعة بن فقير البلوشي',
    ];

    protected $realAmounts = [
        29000,
        50750,
        7800,
        111000,
        13600,
        33650,
        11000,
        500,
        100,
        100000,
        200,
        15787,
        120500,
        20250,
        2450,
        93000,
        38000,
        63600,
        40140,
        5750,
        3000,
        40500,
        1500,
        14000,
        36303,
        25660,
        104700,
        120000,
        1200,
        11000,
        3000,
        155000,
        14000,
        7000,
        2200,
        36220,
        100900,
        500000,
        2500,
        1500,
        400,
        6900,
        1500,
        4500,
        4500,
        700,
        1200,
        3000,
        3000,
        15800,
        38200,
        18650,
        10000,
        33675,
        37450,
        84600,
        29000,
        15000,
        7000,
        1300,
        32620,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $loansCount = 61; // Match number of real users
        // Loan::factory()
        //     ->count($loansCount)
        //     ->create()
        //     ->each(function (Loan $loan) {
        //         $loan->generatePaymentSchedule();
        //     });
        // Access arrays from LoanFactory
        // $loanFactory = new LoanFactory;
        // $names = $loanFactory->getRealUsers();
        // $amounts = $loanFactory->getRealAmounts();

        // First create all users
        $users = collect($this->realUsers)->map(function ($name) {
            return User::factory()->create(['name' => $name]);
        });

        foreach ($users as $index => $user) {
            Loan::factory()->create([
                'user_id' => $user->id,
                'amount' => $this->realAmounts[$index] ?? 0,
                'approved_at' => '2024-01-01',
                'status' => LoanStatus::APPROVED,
            ])->generatePaymentSchedule();
        }

        // dd('Names count', count($names), $names);

        // Create a user & loan for each name-amount pair
        // for ($i = 0; $i < count($names); $i++) {
        //     logger("Seeding index=$i for user '{$names[$i]}'");
        //     $user = User::factory()->create([
        //         'name' => $names[$i],
        //     ]);

        //     $loan = Loan::factory()->create([
        //         'user_id' => $user->id,
        //         'amount' => $amounts[$i],
        //     ]);

        //     $loan->generatePaymentSchedule();
        // }
    }
}
