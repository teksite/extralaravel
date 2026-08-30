<?php

namespace Teksite\Extralaravel\Routing\Trash;

enum TrashAction: string
{
    case INDEX = 'index';
    case REINSTATE = 'reinstate';
    case PRUNE = 'prune';
    case RESTORE = 'restore';
    case FLUSH = 'flush';
}
