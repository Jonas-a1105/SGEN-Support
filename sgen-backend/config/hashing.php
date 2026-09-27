<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver de hash de contraseñas
    |--------------------------------------------------------------------------
    |
    | Argon2id (ganador PHC, resistente a GPU/ASIC) como algoritmo oficial.
    | Los hashes bcrypt legados siguen verificando vía password_verify y se
    | migran transparentemente al primer login exitoso (needsRehash).
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    /*
    | 'verify' => false: los hashes bcrypt heredados de la semilla no cumplen
    | el formato argon; la verificación estricta del cast 'hashed' rompería
    | su lectura. La migración real a Argon2id ocurre vía needsRehash al
    | iniciar sesión semilla (ver LoginRequest).
    */
    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => false,
    ],

    'argon' => [
        'memory' => env('ARGON2_MEMORY', 65536),
        'threads' => env('ARGON2_THREADS', 1),
        'time' => env('ARGON2_TIME', 4),
        'verify' => false,
    ],
];
