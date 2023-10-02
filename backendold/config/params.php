<?php
return [
    'adminEmail' => 'admin@example.com',
    'project_time_file' => 'project_time.txt',
    // Доллары
    'default_currency' => 'USD',
    // Тариф стоимости работы (в валюте - default_currency)
    'rate' => '5',
    // Время отдыха по умолчанию
    'time_relax'=>[
        'min'=>'5',
        'sec'=>'300',
    ],
    'time_work'=>[
        'min'=>'20',
        'sec'=>'1200',
    ],
    'active_colors_old'=>[
        '0'=>'red',
        '1'=>'#272BFF',
        '2'=>'#E600FF',
    ],
    'active_colors'=>[
        '0'=>'font-disabled',
        '1'=>'font-active',
        '2'=>'font-pause',
    ],
    'active_status'=>[
        '0'=>'Отключена',
        '1'=>'Активна',
        '2'=>'Отложена',
    ],
    'periods' => [
        [
            'name' => 'Выберите период',
            'value' => '',
        ],
        [
            'name' => 'За текущий год',
            'value' => '00',
        ],
        [
            'name' => 'За текущий месяц',
            'value' => date('m', time()),
        ],
        [
            'name' => 'За январь',
            'value' => '01',
        ],
        [
            'name' => 'За февраль',
            'value' => '02',
        ],
        [
            'name' => 'За март',
            'value' => '03',
        ],
        [
            'name' => 'За апрель',
            'value' => '04',
        ],
        [
            'name' => 'За май',
            'value' => '05',
        ],
        [
            'name' => 'За июнь',
            'value' => '06',
        ],
        [
            'name' => 'За июль',
            'value' => '07',
        ],
        [
            'name' => 'За август',
            'value' => '08',
        ],
        [
            'name' => 'За сентябрь',
            'value' => '09',
        ],
        [
            'name' => 'За октябрь',
            'value' => '10',
        ],
        [
            'name' => 'За ноябрь',
            'value' => '11',
        ],
        [
            'name' => 'За декабрь',
            'value' => '12',
        ],
    ],
];
