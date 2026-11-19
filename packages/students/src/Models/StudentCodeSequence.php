<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;

/**
 * students.student_code_sequences — correlativo por año para ALU-AAAA-NNNN.
 *
 * @property int $id
 * @property int $year
 * @property int $current
 */
final class StudentCodeSequence extends Model
{
    protected string $schema = 'students';

    protected $table = 'student_code_sequences';

    /**
     * @var list<string>
     */
    protected $fillable = ['year', 'current'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['year' => 'integer', 'current' => 'integer'];
    }
}
