<?php

declare(strict_types=1);

namespace Cai\Care\Enums;

enum ClinicalNoteType: string
{
    case Session = 'session';
    case Evaluation = 'evaluation';
    case Progress = 'progress';
    case Incident = 'incident';
    case FamilyContact = 'family_contact';
    case Referral = 'referral';
    case Administrative = 'administrative';
}
