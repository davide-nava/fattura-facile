<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validierung Sprachzeilen
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute muss akzeptiert werden.',
    'accepted_if' => ':attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => ':attribute ist keine gültige Internet-Adresse.',
    'after' => ':attribute muss ein Datum nach dem :date sein.',
    'after_or_equal' => ':attribute muss ein Datum nach dem :date oder gleich dem :date sein.',
    'alpha' => ':attribute darf nur aus Buchstaben bestehen.',
    'alpha_dash' => ':attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.',
    'alpha_num' => ':attribute darf nur aus Buchstaben und Zahlen bestehen.',
    'array' => ':attribute muss ein Array sein.',
    'confirmed' => ':attribute-Bestätigung stimmt nicht überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => ':attribute ist kein gültiges Datum.',
    'email' => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'integer' => ':attribute muss eine ganze Zahl sein.',
    'numeric' => ':attribute muss eine Zahl sein.',
    'password' => [
        'letters' => ':attribute muss mindestens einen Buchstaben beinhalten.',
        'mixed' => ':attribute muss mindestens einen Groß- und einen Kleinbuchstaben beinhalten.',
        'numbers' => ':attribute muss mindestens eine Zahl beinhalten.',
        'symbols' => ':attribute muss mindestens ein Sonderzeichen beinhalten.',
        'uncompromised' => ':attribute wurde in einem Datenleck gefunden. Bitte wählen Sie ein anderes :attribute.',
    ],
    'required' => ':attribute muss ausgefüllt werden.',
    'same' => ':attribute und :other müssen übereinstimmen.',
    'string' => ':attribute muss ein String sein.',
    'unique' => ':attribute ist bereits vergeben.',

    'attributes' => [
        'name' => 'Name',
        'email' => 'E-Mail-Adresse',
        'password' => 'Passwort',
        'password_confirmation' => 'Passwort-Bestätigung',
        'current_password' => 'Aktuelles Passwort',
    ],

];
