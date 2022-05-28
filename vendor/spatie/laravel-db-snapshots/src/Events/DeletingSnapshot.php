<?php

namespace Spatie\DbSnapshots\Events;

use Spatie\DbSnapshots\Snapshot;

class DeletingSnapshot
{
    public function __construct(
        public Snapshot $snapshot,
    ) {
        //
    }
}
