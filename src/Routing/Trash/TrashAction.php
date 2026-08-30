<?php

namespace Teksite\Extralaravel\Routing\Trash;

enum TrashAction: string
{
    /**
     * Show all soft-deleted records.
     */
    case INDEX = 'index';

    /**
     * Restore one soft-deleted record.
     */
    case REINSTATE = 'reinstate';

    /**
     * Permanently delete one soft-deleted record.
     */
    case PRUNE = 'prune';

    /**
     * Restore all soft-deleted records.
     */
    case RESTORE = 'restore';

    /**
     * Permanently delete all soft-deleted records.
     */
    case FLUSH = 'flush';
}
