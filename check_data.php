<?php
$user = \App\Models\User::where('email', 'aaa@gmail.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

$patient = \App\Models\Patient::where('user_id', $user->id)->first();
if (!$patient) {
    echo "Patient not found\n";
    exit;
}

echo "Consultations:\n";
print_r($patient->consultations->toArray());

echo "\nSessions:\n";
print_r($patient->patientSessions->toArray());
