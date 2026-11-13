<?php

namespace App\Enums;

enum Role: string
{
    case Administrator = 'administrator';

    case Receptionist = 'receptionist';

    case Physician = 'physician';

    case Patient = 'patient';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'System Administrator',
            self::Receptionist => 'Receptionist / Admin',
            self::Physician => 'Physician',
            self::Patient => 'Patient',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Administrator, self::Receptionist => 'admin.queue.index',
            self::Physician => 'physician.dashboard',
            self::Patient => 'patient.dashboard',
        };
    }
}
