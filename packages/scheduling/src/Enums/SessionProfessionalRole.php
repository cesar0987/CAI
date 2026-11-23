<?php

declare(strict_types=1);

namespace Cai\Scheduling\Enums;

enum SessionProfessionalRole: string
{
    case Responsible = 'responsible';
    case Assistant = 'assistant';
    case Observer = 'observer';
}
