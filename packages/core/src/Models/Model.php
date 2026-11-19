<?php

declare(strict_types=1);

namespace Cai\Core\Models;

use Cai\Core\Models\Concerns\ResolvesModuleSchema;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    use ResolvesModuleSchema;

    /**
     * Schemas are configured per connection and default to public.
     * Some modules (identity, students, care, etc.) will override this value.
     */
    protected string $schema = 'public';

    /**
     * Return the short table name used by Eloquent while the connection search_path
     * stays scoped to the current schema.
     */
    public function getTable(): string
    {
        if (! empty($this->table)) {
            return $this->table;
        }

        return parent::getTable();
    }
}
