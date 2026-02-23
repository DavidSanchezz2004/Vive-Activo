<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'aaa@gmail.com')->first();
$patient = \App\Models\Patient::where('user_id', $user->id)->first();

$data = [
    'consultations' => $patient->consultations->toArray(),
    'sessions' => $patient->patientSessions->toArray()
];

file_put_contents('patient_data.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Data written to patient_data.json\n";
