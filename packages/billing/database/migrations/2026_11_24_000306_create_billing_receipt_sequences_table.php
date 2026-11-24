<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** billing.receipt_sequences — 03.06 §7, numeración REC-AAAA-NNNNNN por año. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_sequences', function (Blueprint $table): void {
            $table->id();
            $table->smallInteger('year');
            $table->bigInteger('last_number')->default(0);
            $table->string('prefix', 20)->default('REC');
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['year', 'prefix']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE receipt_sequences ADD CONSTRAINT receipt_sequences_year_check CHECK (year BETWEEN 2000 AND 2100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_sequences');
    }
};
