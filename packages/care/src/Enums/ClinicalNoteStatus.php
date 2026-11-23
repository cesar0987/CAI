<?php

declare(strict_types=1);

namespace Cai\Care\Enums;

/**
 * 03.05 §5.3: una nota finalizada es inmutable; se corrige con una nueva.
 */
enum ClinicalNoteStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';
    case Superseded = 'superseded';
}
